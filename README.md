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

### 2. Create environment file

Create a `.env.local` file in the project root:

```bash
cp .env .env.local
```

Update the following variables in `.env.local`:

```env
###> symfony/framework-bundle ###
APP_ENV=dev
APP_SECRET=your_app_secret_here  # Generate a new secret key
###< symfony/framework-bundle ###

###> doctrine/doctrine-bundle ###
DATABASE_URL="mysql://username:password@127.0.0.1:3306/database_name?serverVersion=8.0.32&charset=utf8mb4"
###< doctrine/doctrine-bundle ###
```

### 3. Install Dependencies

```bash
composer install
```

### 4. Create Database and Run Migrations

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5. Start the Development Server

```bash
symfony server:start
```

## Note

- Never commit `.env.local` to Git
- Each installation should have its own unique APP_SECRET
- Update database credentials in `.env.local` according to your local setup
