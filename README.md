# Ecom-Shop — Full-Stack E-Commerce Platform

A full-stack e-commerce web application built with **Laravel, Vue.js, and MySQL**. The project includes an online storefront, product browsing, shopping cart, checkout, order management, wishlist functionality, and an administrative dashboard.

## Overview

Ecom-Shop is designed to provide a complete online shopping experience for customers and a management interface for administrators. It combines Laravel's backend capabilities with a modern Vue.js frontend to manage products, orders, customers, and essential e-commerce operations.

The project is under active development, with planned improvements to payment processing and other store features.

## Features

### Customer Storefront

- Browse the homepage and product catalog.
- View product details.
- Add products to the shopping cart.
- Manage cart items and quantities.
- Proceed through the checkout process.
- Add products to a wishlist.
- View order-related information and checkout results.

### Admin Dashboard

- View store statistics and key business metrics.
- Manage products and product information.
- Manage orders and monitor their status.
- View customer and store-related data.
- Manage product categories and other store resources.
- Use dashboard tables, forms, and navigation to manage store operations.

### Additional Functionality

- Coupon management and discount application.
- Form validation and request handling.
- Database-backed application data.
- Responsive storefront and administrative interfaces.
- Payment integration support through the existing checkout architecture, subject to the configured gateway.

*Feature availability may depend on the current implementation and configuration.*

## Technology Stack

| Technology | Purpose |
|---|---|
| PHP | Server-side programming |
| Laravel | Backend framework |
| Vue.js | Frontend interface |
| Inertia.js | Communication between Laravel and Vue, where configured |
| MySQL | Relational database |
| Vite | Frontend development and asset bundling |
| Composer | PHP dependency management |
| npm | JavaScript dependency management |
| Bootstrap | Responsive UI styling, where used |

## Project Architecture

The application uses Laravel for backend operations and Vue.js for interactive user interfaces.

- **Backend:** Application logic, routing, controllers, database access, validation, and order processing.
- **Frontend:** Storefront pages, product interfaces, cart, checkout, and admin dashboard.
- **Database:** Persistent storage for products, users, orders, and other application data.
- **Build tools:** Composer and npm manage the project's dependencies and development workflow.

## Getting Started

Follow these instructions to run the project locally.

### Prerequisites

Install the following software:

- PHP version compatible with the project's Laravel requirements
- Composer
- Node.js and npm
- MySQL
- Git

### 1. Clone the Repository

```bash
git clone https://github.com/jabedhosenn/ecom-shop.git
cd ecom-shop
```

### 2. Install Dependencies

Install the PHP dependencies:

```bash
composer install
```

Install the frontend dependencies:

```bash
npm install
```

### 3. Configure Environment Variables

Copy the example environment file:

**Windows Command Prompt:**

```cmd
copy .env.example .env
```

**macOS / Linux:**

```bash
cp .env.example .env
```

Generate the Laravel application key:

```bash
php artisan key:generate
```

### 4. Configure the Database

Create a MySQL database for the project, then update the database settings in `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
```

Replace the example values with your local MySQL credentials.

### 5. Run Database Migrations

```bash
php artisan migrate
```

If the project includes seeders for sample products, categories, or other records, run them when appropriate:

```bash
php artisan db:seed
```

### 6. Build and Run the Application

Start the frontend development server:

```bash
npm run dev
```

In a separate terminal, start Laravel:

```bash
php artisan serve
```

Open the local URL shown by Laravel, usually:

http://127.0.0.1:8000

Keep both development processes running while working locally.

**Note:** Use the scripts defined in the project's `package.json` and `composer.json`. If the project has a combined development command, you may use that instead.

## Environment Configuration

Application configuration is managed through Laravel's `.env` file.

Common settings include:

| Variable | Description |
|---|---|
| `APP_NAME` | Application name |
| `APP_ENV` | Application environment |
| `APP_DEBUG` | Enable or disable debug mode |
| `APP_URL` | Local or deployed application URL |
| `DB_CONNECTION` | Database driver |
| `DB_HOST` | Database server |
| `DB_PORT` | Database port |
| `DB_DATABASE` | Database name |
| `DB_USERNAME` | Database username |
| `DB_PASSWORD` | Database password |

Additional variables may be required for email, sessions, queues, file storage, and the currently configured payment gateway.

Never commit `.env` files, database credentials, API keys, or other secrets to version control

## Future Improvements

Planned areas for further development include:

- Stripe payment integration for eligible international transactions.
- Improved order and payment status handling.
- Enhanced storefront and admin dashboard usability.
- Additional coupon and discount validation.
- Expanded automated testing.
- Production deployment and performance improvements.

## Author

**Jabed Hosen**

Software Engineer

- GitHub: [@jabedhosenn](https://github.com/jabedhosenn)
- Project Repository: [ecom-shop](https://github.com/jabedhosenn/ecom-shop)

## License

Unless a license file specifies otherwise, all rights and permissions are governed by the repository's existing licensing status. Check the project's `LICENSE` file before redistributing or reusing the code.

---

*Ecom-Shop is a full-stack development project focused on building and improving an online shopping platform with Laravel and Vue.js.*
