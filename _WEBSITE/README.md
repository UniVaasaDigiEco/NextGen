# NextGen Website

A PHP web application for managing food waste predictions and data analysis.

## Prerequisites

- PHP 8.2+
- MySQL/MariaDB
- Composer
- Node.js & npm

## Setup Instructions

### 1. Clone the Repository
```bash
git clone <your-repo-url>
cd NextGen/_WEBSITE
```

### 2. Install Dependencies

#### PHP Dependencies (Composer)
```bash
composer install
```

#### JavaScript Dependencies (npm)
```bash
npm install
```

### 3. Database Configuration

1. Copy the configuration template:
   ```bash
   cp config/_config.template.php config/_config.php
   ```

2. Edit `config/_config.php` with your database credentials:
   ```php
   <?php
   CONST DB_HOST = "your_database_host";
   CONST DB_USER = "your_database_username";
   CONST DB_PASS = "your_database_password";
   CONST DB_NAME = "your_database_name";
   ```

### 4. Web Server Setup

Configure your web server to serve the application from this directory.

#### Apache
The included `.htaccess` files should handle the configuration automatically.

#### Nginx
Configure your virtual host to point to this directory.

### 5. File Permissions

Ensure the web server has write permissions to:
- `files/` directory (for uploads)
- Any cache directories

## Security Notes

- Never commit `config/_config.php` to version control
- The `config/` directory is protected by `.htaccess`
- Remove or secure any temporary files before production deployment

## Development

### File Structure
- `config/` - Configuration files (protected from web access)
- `pages/` - Application pages
- `actions/` - Form handlers and API endpoints  
- `classes/` - PHP classes
- `css/` - Stylesheets
- `js/` - JavaScript files
- `images/` - Image assets
- `files/` - User uploads (excluded from git)

### Dependencies
- **OpenSpout**: Excel file processing
- **Bootstrap 5**: UI framework
- **jQuery**: JavaScript utilities
- **Chart.js**: Data visualization