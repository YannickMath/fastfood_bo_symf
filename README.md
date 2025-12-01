# FastFood API - Symfony Backend

A professional RESTful API built with Symfony 7.3 for a fast-food e-commerce application. This backend provides comprehensive product management, user authentication, and shopping cart functionality.

## Overview

This project demonstrates modern PHP/Symfony development practices including:

-   Clean architecture with service layer pattern
-   JWT-based authentication and role-based access control
-   DTO (Data Transfer Objects) for input validation and output serialization
-   Custom exception handling with meaningful error responses
-   CSV data import commands for easy product seeding
-   Comprehensive REST API endpoints

## Tech Stack

-   **Framework:** Symfony 7.3
-   **Language:** PHP 8.2+
-   **Database:** PostgreSQL (configurable for MySQL/MariaDB)
-   **Authentication:** JWT (LexikJWTAuthenticationBundle)
-   **ORM:** Doctrine ORM 3.4
-   **API:** RESTful architecture with JSON responses
-   **Security:** CORS configuration, password hashing, role-based authorization

## Key Features

### User Management

-   User registration and login with JWT token generation
-   Password hashing with Symfony's security component
-   Role-based access control (ROLE_USER, ROLE_ADMIN)
-   User profile management (view, update, delete)
-   Authentication check endpoint

### Product Management

-   CRUD operations for products (admin-restricted)
-   Product categorization
-   Public product browsing
-   Search products by name or category
-   Input validation with Symfony Validator

### Shopping Cart

-   User-specific cart management
-   Add/remove items with quantity control
-   Cart persistence in database
-   Merge cart functionality (for anonymous to authenticated user transition)
-   Clear cart functionality

### Data Management

-   CSV import commands for categories and products
-   Console commands for easy data seeding
-   Automated timestamps (created_at, updated_at)

## Architecture

### Project Structure

```
src/
├── Command/          # Console commands for data import
├── Controller/       # API endpoints (User, Product, Cart, Category)
├── Dto/              # Input/Output data transfer objects
├── Entity/           # Doctrine entities (User, Product, Cart, CartItem, Category)
├── EventListener/    # Global exception listener
├── Exception/        # Custom exceptions
├── Repository/       # Doctrine repositories
└── Service/          # Business logic layer
```

### Design Patterns

-   **Service Layer Pattern:** Business logic separated from controllers
-   **DTO Pattern:** Input validation and output formatting
-   **Repository Pattern:** Data access abstraction via Doctrine
-   **Exception Handling:** Custom exceptions with global listener for consistent error responses

## API Endpoints

### Public Endpoints

-   `GET /api/products` - List all products
-   `GET /api/product/{id}` - Get product details
-   `GET /api/products/category/{category}` - Products by category
-   `GET /api/categories` - List all categories
-   `POST /api/user/register` - Register new user
-   `POST /api/user/login_check` - User login (returns JWT token)

### Authenticated Endpoints (ROLE_USER)

-   `GET /api/user/checkAuthentication` - Verify authentication status
-   `GET /api/cart` - Get user's cart
-   `POST /api/cart` - Save/update cart
-   `DELETE /api/cart` - Clear cart
-   `PUT /api/cart/item` - Update cart item quantity
-   `POST /api/cart/items` - Add item to cart
-   `POST /api/cart/merge` - Merge session cart with user cart
-   `POST /api/user/logout` - Logout user

### Admin Endpoints (ROLE_ADMIN)

-   `POST /api/product/create` - Create product
-   `PUT /api/product/update/{id}` - Update product
-   `DELETE /api/product/delete/{id}` - Delete product
-   `GET /api/product/find/{name}` - Find product by name
-   `GET /api/user` - List all users
-   `GET /api/user/{id}` - Get user details
-   `PUT /api/user/{id}/update` - Update user
-   `DELETE /api/user/{id}` - Delete user

## Installation & Setup

### Prerequisites

-   PHP 8.2 or higher
-   Composer
-   PostgreSQL (or MySQL/MariaDB)
-   Symfony CLI (optional but recommended)

### Installation Steps

1. **Clone the repository**

    ```bash
    git clone <repository-url>
    cd symfony_bo
    ```

2. **Install dependencies**

    ```bash
    composer install
    ```

3. **Configure environment**

    Copy `.env` to `.env.local` and configure your database:

    ```env
    DATABASE_URL="postgresql://username:password@127.0.0.1:5432/database_name?serverVersion=16&charset=utf8"
    APP_SECRET=your-random-secret-key-here
    JWT_PASSPHRASE=your-secure-passphrase-here
    ```

4. **Generate JWT keys**

    ```bash
    php bin/console lexik:jwt:generate-keypair
    ```

5. **Create database and run migrations**

    ```bash
    php bin/console doctrine:database:create
    php bin/console doctrine:migrations:migrate
    ```

6. **Import initial data (optional)**

    ```bash
    php bin/console app:import-categories
    php bin/console app:import-products
    ```

7. **Create an admin user (optional)**

    First, register a user via the API or create one manually, then promote them to admin:

    ```bash
    php bin/console app:promote-admin user@example.com
    ```

8. **Start the development server**
    ```bash
    symfony server:start
    # Or
    php -S localhost:8000 -t public/
    ```

## Configuration

### CORS Setup

CORS is configured via `nelmio_cors.yaml` for cross-origin requests. Adjust the `CORS_ALLOW_ORIGIN` in `.env.local`:

```env
CORS_ALLOW_ORIGIN='^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$'
```

### Security Configuration

-   JWT tokens expire after 1 hour (configurable in `config/packages/lexik_jwt_authentication.yaml`)
-   Passwords are hashed using Symfony's auto algorithm
-   Access control rules defined in `config/packages/security.yaml`

## Usage Examples

### Register a User

```bash
curl -X POST http://localhost:8000/api/user/register \
  -H "Content-Type: application/json" \
  -d '{"username":"john","email":"john@example.com","password":"securePassword123"}'
```

### Login

```bash
curl -X POST http://localhost:8000/api/user/login_check \
  -H "Content-Type: application/json" \
  -d '{"email":"john@example.com","password":"securePassword123"}'
```

### Get Products (with JWT)

```bash
curl -X GET http://localhost:8000/api/products \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

### Create Product (Admin)

```bash
curl -X POST http://localhost:8000/api/product/create \
  -H "Authorization: Bearer ADMIN_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Burger","price":850,"description":"Delicious burger","category":1}'
```

## Data Import

Place CSV files in the `data/` directory:

-   `categories.csv` - Categories data
-   `products.csv` - Products data

CSV Format for products:

```csv
name,description,price,category
Burger Supreme,Delicious burger with cheese,850,1
French Fries,Crispy golden fries,350,2
```

## Security Features

-   **JWT Authentication:** Stateless authentication for API security
-   **Password Hashing:** Automatic secure password hashing
-   **Role-Based Access:** Granular permission control (PUBLIC_ACCESS, ROLE_USER, ROLE_ADMIN)
-   **Input Validation:** DTOs with Symfony Validator constraints
-   **SQL Injection Protection:** Doctrine ORM parameterized queries
-   **CORS Protection:** Configurable cross-origin resource sharing

## Error Handling

Custom exceptions are caught by the `ExceptionListener` and return consistent JSON responses:

```json
{
    "error": "Product not found",
    "code": 404
}
```

Custom exceptions include:

-   `UserNotFoundException`
-   `UserAlreadyExistsException`
-   `UserLoginException`
-   `ProductNotFoundException`
-   `ProductNotPossibleToCreateException`
-   `ProductCannotBeUpdatedException`
-   And more...

## Development Commands

```bash
# Create new entity
php bin/console make:entity

# Generate migration
php bin/console make:migration

# Run migrations
php bin/console doctrine:migrations:migrate

# Create new controller
php bin/console make:controller

# Clear cache
php bin/console cache:clear

# Promote user to admin
php bin/console app:promote-admin user@example.com

# Import data from CSV
php bin/console app:import-categories
php bin/console app:import-products
```

## Code Quality

This project follows:

-   PSR-4 autoloading
-   Symfony best practices
-   Type declarations (strict types)
-   Separation of concerns (Controller → Service → Repository)
-   DTO pattern for API boundaries
-   RESTful naming conventions

## Project Status

This project is actively under development. New features and improvements are being added regularly as part of an ongoing full-stack portfolio project.

---

**Note:** This is the backend API. It's designed to work with a React frontend application for a complete e-commerce solution.
