# NextGen - Food Waste Prediction System

A comprehensive system for predicting and managing food waste using machine learning and web-based data management.

## Project Structure

```
NextGen/
├── _AZURE/          # Azure Functions ML Backend
├── _WEBSITE/        # PHP Web Application Frontend
├── _DOCS/           # Documentation (excluded from git)
├── _SQL/            # Database scripts (excluded from git)
└── README.md        # This file
```

## Components

### 🤖 Azure ML Backend (`_AZURE/`)
Azure Functions application providing machine learning capabilities for food waste prediction.

**Features:**
- Model training on historical data
- Waste prediction API endpoints
- Automated daily model retraining
- Dynamic model versioning

**Tech Stack:** Python, Azure Functions, scikit-learn, pandas, Azure SQL

[📖 View Azure Documentation](./_AZURE/README.MD)

### 🌐 Web Frontend (`_WEBSITE/`)
PHP web application for data management and visualization.

**Features:**
- User authentication and management
- Data upload and processing
- Prediction visualization
- Historical data analysis

**Tech Stack:** PHP, MySQL, Bootstrap, Chart.js, jQuery

[📖 View Website Documentation](./_WEBSITE/README.md)

## Quick Start

### Prerequisites
- **For Azure Backend:**
  - Python 3.9+
  - Azure Functions Core Tools
  - Azure SQL Database
  
- **For Web Frontend:**
  - PHP 8.2+
  - MySQL/MariaDB
  - Composer
  - Node.js & npm

### Setup Instructions

1. **Clone the repository:**
   ```bash
   git clone <your-repo-url>
   cd NextGen
   ```

2. **Set up the Azure Backend:**
   ```bash
   cd _AZURE
   pip install -r requirements.txt
   # Configure environment variables (see _AZURE/README.MD)
   func start
   ```

3. **Set up the Web Frontend:**
   ```bash
   cd _WEBSITE
   composer install
   npm install
   # Configure database (see _WEBSITE/README.md)
   ```

## Configuration

Both components require environment-specific configuration:

- **Azure Backend:** Uses Azure Function App Settings or `local.settings.json`
- **Web Frontend:** Uses `config/_config.php` (template provided)

⚠️ **Never commit sensitive configuration files to git!**

## Development Workflow

1. **Backend Development:** Work in `_AZURE/` directory
2. **Frontend Development:** Work in `_WEBSITE/` directory
3. **Database Changes:** Document in `_SQL/` (local only)
4. **Documentation Updates:** Update in `_DOCS/` (local only)

## Deployment

### Azure Backend
Deploy to Azure Functions using:
- Azure CLI
- Visual Studio Code Azure Functions extension
- GitHub Actions (CI/CD)

### Web Frontend
Deploy to any PHP-compatible hosting:
- Shared hosting (cPanel/DirectAdmin)
- VPS with Apache/Nginx
- Container deployment

## Security Notes

- All sensitive data uses environment variables
- Database credentials are templated
- Upload directories excluded from version control
- Configuration directories protected via `.htaccess`

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

[Add your license information here]

## Support

[Add support/contact information here]