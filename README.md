# PHP Spike with .NET Aspire

This project demonstrates a PHP application orchestrated using .NET Aspire with MySQL database and LocalStack for AWS service emulation.

## Prerequisites

### Required Software

1. **.NET 10 Preview** - This project requires .NET 10.0 (currently in preview)
   - Download from: https://dotnet.microsoft.com/en-us/download/dotnet/10.0
   - Verify installation: `dotnet --version` (should show 10.x.x)

2. **Docker Desktop**
   - Download from: https://www.docker.com/products/docker-desktop/
   - Required for running MySQL, LocalStack, and PHP containers
   - Ensure Docker is running before starting the application

3. **Composer** (for PHP dependency management)
   - Download from: https://getcomposer.org/download/
   - Required for managing PHP dependencies in the containerized environment

### Optional but Recommended

- **Visual Studio 2025** or **Visual Studio Code** with C# extension
- **PowerShell** (for Windows users)

## Project Structure

```
php-spike/
├── .aspire/                    # Aspire configuration
│   └── settings.json          # Single-file AppHost settings
├── src/
│   ├── apphost.cs             # Single-file Aspire AppHost (experimental)
│   └── phpapp/                # PHP application
│       ├── composer.json      # PHP dependencies
│       ├── dockerfile         # PHP container definition
│       └── public/
│           ├── index.php      # Main PHP application
│           └── styles.css     # Styling
└── README.md
```

## Architecture Overview

This application uses .NET Aspire 9.5.1 to orchestrate:

- **PHP Application** - Running on Apache with PHP 8.3
- **MySQL Database** - With phpMyAdmin for database management
- **LocalStack** - AWS service emulation (S3, Secrets Manager)

## Getting Started

### 1. Clone the Repository

```powershell
git clone <repository-url>
cd php-spike
```

### 2. Verify Prerequisites

```powershell
# Check .NET version
dotnet --version

# Check Docker status
docker --version
docker ps

# Aspire workload is no longer required with latest versions
```

### 3. Run the Application

From the project root directory:

```powershell
aspire run
```

### 4. Access Services

Once running, the Aspire dashboard will show you the endpoints for:

- **Aspire Dashboard** - Management interface for all services
- **PHP Application** - Main web application (typically http://localhost:5xxx)
- **phpMyAdmin** - Database management interface
- **LocalStack** - AWS service emulation (http://localhost:4566)

## Development

### PHP Dependencies

The PHP application uses Composer for dependency management. Dependencies are installed during the Docker build process, but you can also install them locally:

```powershell
cd src/phpapp
composer install
```

Current PHP dependencies:
- `aws/aws-sdk-php` - AWS SDK for LocalStack integration

### Database Configuration

The application automatically configures the PHP container with:
- MySQL host, port, database name
- Auto-generated MySQL root password
- LocalStack endpoint for AWS services

### Container Persistence

- MySQL data is persisted using Docker volumes
- Containers use `ContainerLifetime.Persistent` for faster restarts during development

## Troubleshooting

### Common Issues

1. **Docker not running**
   ```
   Error: Cannot connect to the Docker daemon
   ```
   **Solution**: Ensure Docker Desktop is running

2. **Port conflicts**
   ```
   Error: Port already in use
   ```
   **Solution**: Stop conflicting services or modify port assignments in `apphost.cs`

3. **.NET 10 not found**
   ```
   Error: The specified framework 'Microsoft.NETCore.App', version '10.x.x' was not found
   ```
   **Solution**: Install .NET 10 preview from the official download page



### Logs and Debugging

- Use the Aspire dashboard to view logs for all services
- LocalStack logs are set to "error" level to reduce noise
- PHP application logs are available through the Docker container logs

## Configuration

### Environment Variables

The application uses these key environment variables (automatically configured):

- `DB_HOST`, `DB_PORT`, `DB_NAME` - MySQL connection details
- `DB_USER`, `DB_PASSWORD` - MySQL credentials
- `AWS_ENDPOINT` - LocalStack endpoint for AWS services
- `ASPIRE_ALLOW_UNSECURED_TRANSPORT=true` - Allows HTTP in development

### Customization

To modify service configuration, edit `src/apphost.cs`:

- Change container images or versions
- Modify environment variables
- Add new services or dependencies
- Adjust health check settings

## Production Considerations

This setup is designed for development and includes:

- Unsecured HTTP transport (set via environment variable)
- Debug logging for LocalStack
- Development-optimized container settings

For production deployment:
1. Remove `ASPIRE_ALLOW_UNSECURED_TRANSPORT` environment variable
2. Configure proper SSL certificates
3. Use production-grade MySQL configuration
4. Replace LocalStack with actual AWS services
5. Implement proper secret management

## Contributing

When making changes:

1. Test locally with `dotnet run`
2. Verify all containers start successfully
3. Check the Aspire dashboard for service health
4. Test PHP application functionality
5. Ensure proper error handling and logging

## Resources

- [.NET Aspire Documentation](https://learn.microsoft.com/en-us/dotnet/aspire/)
- [Docker Desktop](https://www.docker.com/products/docker-desktop/)
- [LocalStack Documentation](https://docs.localstack.cloud/)
- [PHP 8.3 Documentation](https://www.php.net/releases/8_3_0.php)