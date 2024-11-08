<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use League\Csv\Writer;
use League\Csv\Reader;

#[Route('/')]
final class ProductController extends AbstractController
{
    #[Route('/import', name: 'app_product_import', priority: 1000, methods: ['POST'])]
    public function import(Request $request, EntityManagerInterface $entityManager): Response
    {   
        try {
            $file = $request->files->get('csv_file');
            
            if (!$file) {
                $this->addFlash('error', 'Please upload a file');
                return $this->redirectToRoute('app_product_index');
            }

            // Validate file type
            $mimeType = $file->getMimeType();
            $extension = $file->getClientOriginalExtension();
            
            if ($extension !== 'csv') {
                $this->addFlash('error', 'Please upload a CSV file. Received file type: .' . $extension);
                return $this->redirectToRoute('app_product_index');
            }

            // Read and process the CSV file
            $csv = Reader::createFromPath($file->getPathname());
            $csv->setHeaderOffset(0);
            
            $records = $csv->getRecords();
            $importCount = 0;
            $errors = [];
            
            foreach ($records as $index => $record) {
                try {
                    $product = new Product();
                    $product->setName($record['Name'] ?? '');
                    $product->setDescription($record['Description'] ?? '');
                    $product->setPrice(floatval($record['Price'] ?? 0));
                    $product->setStockQuantity(intval($record['Stock Quantity'] ?? 0));
                    $product->setCreatedAt(new \DateTimeImmutable());
                    
                    $entityManager->persist($product);
                    $importCount++;
                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            if ($importCount > 0) {
                $entityManager->flush();
                $this->addFlash('success', sprintf('Successfully imported %d products', $importCount));
            } else {
                $this->addFlash('warning', 'No products were imported');
            }

            // Add any errors as warnings
            foreach ($errors as $error) {
                $this->addFlash('warning', $error);
            }
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error importing file: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_product_index');
    }

    #[Route('/export', name: 'app_product_export', methods: ['GET'])]
    public function export(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();

        $response = new StreamedResponse(function() use ($products) {
            $csv = Writer::createFromStream(fopen('php://output', 'w'));
            
            // Add headers
            $csv->insertOne(['ID', 'Name', 'Description', 'Price', 'Stock Quantity', 'Created At']);
            
            // Add products
            foreach ($products as $product) {
                $csv->insertOne([
                    $product->getId(),
                    $product->getName(),
                    $product->getDescription(),
                    $product->getPrice(),
                    $product->getStockQuantity(),
                    $product->getCreatedAt()->format('Y-m-d H:i:s')
                ]);
            }
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'products.csv'
        );
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
    
    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(Request $request, ProductRepository $productRepository, PaginatorInterface $paginator)
    {
        // Fetch search and sorting parameters from the request
        $search = $request->query->get('search');
        $sortField = $request->query->get('sort', 'name');
        $sortDirection = $request->query->get('direction', 'asc');
        // Define allowed sorting fields to prevent errors
        $allowedSortFields = ['name', 'price', 'stock_quantity', 'created_at'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'name'; // Fallback to a default field
        }
        // Create a query with search and sorting
        $queryBuilder = $productRepository->createQueryBuilder('p')->select('p'); // explicit select
       // echo $queryBuilder."<br>";
        if ($search) {
            $queryBuilder->andWhere('p.name LIKE :search OR p.description LIKE :search')
                         ->setParameter('search', '%' . $search . '%');
        }
       // Handle sorting
        switch ($sortField) {
            case 'name':
                $queryBuilder->orderBy('p.name', $sortDirection);
                break;
            case 'price':
                $queryBuilder->orderBy('p.price', $sortDirection);
                break;
            case 'stock_quantity':
                $queryBuilder->orderBy('p.stock_quantity', $sortDirection);
                break;
            case 'created_at':
                    $queryBuilder->orderBy('p.created_at', $sortDirection);
                    break;
            default:
                $queryBuilder->orderBy('p.name', 'asc');
        }
        // Get the query to avoid the issues with sorting
        $query = $queryBuilder->getQuery();
       // echo $queryBuilder."<br>";//exit;
        
        try {
            $pagination = $paginator->paginate(
                //$queryBuilder->getQuery(), 
                $query->getResult(), // Get the Query object instead of QueryBuilder
                $request->query->getInt('page', 1),
                5
            );
        } catch (\Exception $e) { 
            var_dump($e->getMessage());
            throw $e;
        }
        
        //print_r($pagination);exit;
        return $this->render('product/index.html.twig', [
           // 'products' => $productRepository->findAll(),
            'pagination' => $pagination,
            'search' => $search,
            'sortField' => $sortField,
            'sortDirection' => $sortDirection, 
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $product->setCreatedAt(new \DateTimeImmutable());
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();
            // Preserve search parameter if it exists
            $params = [
                'sort' => 'created_at',
                'direction' => 'desc'
            ];
            return $this->redirectToRoute('app_product_index', $params, Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $product->setUpdatedAt(new \DateTimeImmutable());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
    
}
