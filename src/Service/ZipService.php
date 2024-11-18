<?php

namespace App\Service;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive; // applied built-in package so that we don not need to include external packages

class ZipService
{
    public function createZip(array $files, string $zipNameWithPath): bool
    {
        $zip = new ZipArchive();
        try {
            if($zip -> open($zipNameWithPath, ZipArchive::CREATE ) === TRUE) { 
                foreach ($files as $file) {
                    if (!$zip->addFile($file, basename($file))) {
                        throw new \RuntimeException(sprintf('Cannot add file %s to zip', basename($file)));
                    }
                }
            } else {
                throw new \RuntimeException('Cannot create zip file');
            }
            return $zip->close();
        } catch (\Exception $e) {
            $zip->close();
            throw $e;
        }
    }

    public function getZipResponse(string $zipPath, string $downloadName): StreamedResponse
    {
        if (!file_exists($zipPath)) {
            throw new \RuntimeException('Zip file not found');
        }

        $response = new StreamedResponse(function() use ($zipPath) {
            readfile($zipPath);
        });

        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $downloadName));

        return $response;
    }

    
}