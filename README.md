# Product CRUD Application

This is a Symfony 7-based web application that allows managing product data with features like CRUD (Create, Read, Update, Delete) operations and validation. The project uses Doctrine ORM for database interactions, Twig templates for the frontend, and Symfony Forms for handling user input and validation.

## Technologies Used

- **Symfony 7.0**: PHP framework for building web applications.
- **Doctrine ORM**: Database abstraction layer for managing entity relationships and CRUD operations.
- **Twig**: Template engine for rendering views in the application.
- **Symfony Forms**: To handle form submissions and validation.

## Features

- **CRUD functionality** for products:
  - **Create**: Add a new product to the database.
  - **Read**: View product details.
  - **Update**: Edit product information.
  - **Delete**: Remove a product from the database.
- **Validation**: The product form includes validation for fields such as `name`, `price`, `stock_quantity`, etc.
- **Twig Templates**: Used for rendering views, including product list, product form, and individual product details.

## Installation

To set up the project locally, follow these steps:

### 1. Clone the repository

```bash
git clone https://github.com/mkwmkn/sf7-crud-app.git
cd sf7-crud-app
```

### 2. Install Dependencies

```
composer install
```
