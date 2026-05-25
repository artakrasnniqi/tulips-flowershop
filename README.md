# TULIPS FlowerShop

TULIPS FlowerShop is a PHP and MySQL e-commerce project for an online flower shop. The site includes a product catalog, product details, shopping cart, checkout flow, delivery information, contact page, and an admin panel for managing products.

## Main Features

- Responsive Bootstrap layout with a floral shop design
- Home page with featured products and a storefront image
- Product catalog with search and category filtering
- Product details page with quantity selection
- Session-based shopping cart
- Cart update and remove actions
- Checkout form with customer and delivery details
- Card payment simulation and cash on delivery option
- Product stock management
- Stock validation before adding to cart and before checkout
- Automatic stock reduction after successful order creation
- Admin panel for adding, editing, deleting, importing, and managing products
- Delivery page with delivery steps and customer guidance
- Contact page
- MySQL database schema with sample flower products

## Security Improvements

- CSRF protection on important POST forms
- Checkout idempotency token to reduce duplicate order submissions
- Server-side validation for checkout fields
- Prepared SQL statements for database queries
- Stock checks inside database transactions during checkout
- Basic HTTPS requirement support for card payments, with local testing allowed in config

## Project Structure

```text
tulips_flowershop/
|-- admin/
|   |-- add_product.php
|   |-- edit_product.php
|   |-- delete_product.php
|   |-- import_products.php
|   `-- index.php
|-- assets/
|   |-- css/style.css
|   |-- img/
|   |   `-- highquality-flower-shop-with-large-sign-modern-storefront_976564-6835.avif
|-- config/
|   |-- db.php
|   `-- payment_config.php
|-- includes/
|   |-- header.php
|   |-- footer.php
|   `-- functions.php
|-- sql/database.sql
|-- index.php
|-- products.php
|-- product.php
|-- cart.php
|-- checkout.php
|-- process_checkout.php
|-- delivery.php
`-- contact.php
```

## Setup

1. Place the project folder in your XAMPP `htdocs` directory.

   Example:

   ```text
   C:\xampp1\htdocs\tulips_flowershop
   ```

2. Start Apache and MySQL from XAMPP.

3. Create/import the database using:

   ```text
   sql/database.sql
   ```

4. Check the database credentials in:

   ```text
   config/db.php
   ```

5. Open the project in your browser:

   ```text
   http://localhost/tulips_flowershop/index.php
   ```

## Important Pages

- Home: `index.php`
- Catalog: `products.php`
- Product details: `product.php?id=1`
- Cart: `cart.php`
- Checkout: `checkout.php`
- Delivery: `delivery.php`
- Contact: `contact.php`
- Admin panel: `admin/index.php`

## Database Notes

The `products` table includes a `stock` column. Existing products receive a default stock value if the column is added automatically by the application.

The checkout flow creates records in:

- `orders`
- `order_items`

The application also includes schema checks in `includes/functions.php` to help older local databases receive the needed columns.

## Payment Notes

Payments are simulated for local testing. Configuration is stored in:

```text
config/payment_config.php
```

For a real production store, connect a real payment provider, enable HTTPS, and replace the test gateway configuration.

## Current Status

The project is ready as a complete educational e-commerce website. It has the main online shop flow: browse products, add to cart, manage cart, checkout, create orders, and manage product stock from the admin panel.
