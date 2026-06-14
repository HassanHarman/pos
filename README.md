# POS System

A PHP-based Point of Sale (POS) system with inventory management, sales tracking, and multi-role user access.

## Features

- **Multi-Role Access**: Admin, Manager, Cashier, and Stock Manager interfaces
- **Sales Management**: Process sales, view sales history, handle returns
- **Inventory Management**: Track products, low stock alerts, stock adjustments
- **Cash Float Management**: Open and close cash registers with float tracking
- **PWA Support**: Progressive Web App capabilities for mobile access
- **Reporting**: Sales reports and analytics

## Project Structure

```
pos/
├── cashier/          # Cashier interface for processing sales
├── main/             # Admin dashboard and management
│   ├── products.php  # Product management
│   ├── sales.php     # Sales reports
│   ├── customers.php # Customer management
│   └── vendors/      # Third-party libraries (Bootstrap, CKEditor, DataTables, etc.)
├── manager/          # Manager-specific tools
├── stock/            # Stock management interface
├── index.php         # Main entry point
├── login.php         # Authentication
├── connect.php       # Database configuration
├── sales.sql         # Database schema
└── sw.js             # Service Worker for PWA
```

## Requirements

- PHP 7.0+
- MySQL/MariaDB
- Web server (Apache/Nginx)

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/HassanHarman/pos.git
   ```

2. Import the database schema:
   ```bash
   mysql -u username -p database_name < sales.sql
   ```

3. Configure database connection in `connect.php`

4. Access the application through your web browser

## Default Login

Access the login page at `/login.php`

## License

This project is open source.
