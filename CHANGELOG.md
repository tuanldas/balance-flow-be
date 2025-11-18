# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed
- **BREAKING**: Category icon system refactored from SVG strings to file uploads
  - Database: `icon_svg` (text) → `icon_path` (string, nullable)
  - API: Changed from JSON body with `icon_svg` to multipart/form-data with `icon` file upload
  - Validation: File upload validation (image, max 1MB, formats: png, jpg, jpeg, svg)
  - Storage: Icons stored in `storage/app/public/category-icons/`
  - Response: API now returns `icon_url` instead of `icon_svg`
  - Service: Automatic file cleanup when updating or deleting categories
  - Seeder: System categories created without icons (icon_path = null)

### Added
- Category Management System
  - Dual category system (System + User categories)
  - 17 default system categories (6 income + 11 expense)
  - File upload support for category icons (PNG, JPG, SVG, max 1MB)
  - Safe deletion with transaction transfer
  - Type-based filtering (income/expense)
  - Multi-language support for system categories

- API Endpoints (9 new endpoints)
  - `GET /api/categories` - List accessible categories
  - `GET /api/categories?type={type}` - Filter by type
  - `POST /api/categories` - Create user category
  - `GET /api/categories/{id}` - Get category details
  - `PUT /api/categories/{id}` - Update category
  - `DELETE /api/categories/{id}` - Delete category
  - `GET /api/categories/{id}/transactions-count` - Count transactions

- Database Schema
  - `categories` table with UUID v7 primary key
  - `transactions` table with UUID v7 primary key
  - Foreign key relationships with cascade/restrict
  - Composite indexes for performance

- Models
  - `Category` model with relationships and scopes
  - `Transaction` model with relationships
  - UUID v7 support for all models
  - Translated name accessor for system categories
  - Query scopes for filtering

- Service Layer
  - `CategoryService` with full business logic
  - `CategoryRepository` for data access
  - `TransactionRepository` for transaction operations
  - Interface-based dependency injection

- Validation
  - `StoreCategoryRequest` for creation
  - `UpdateCategoryRequest` for updates
  - `DeleteCategoryRequest` for deletion with transfer

- API Resources
  - `CategoryResource` for consistent API responses
  - Translated names for multi-language support

- Testing
  - 22 comprehensive feature tests for category endpoints
  - `CategoryFactory` with states (system, income, expense)
  - `TransactionFactory` with states (income, expense)
  - 100% endpoint coverage
  - Authorization and validation test coverage

- Documentation
  - Postman collection with 18 requests
  - Postman environment configuration
  - README.md for API documentation
  - QUICKSTART.md for 5-minute setup
  - CURL_EXAMPLES.md for command-line testing
  - Updated CLAUDE.md with comprehensive guidelines

- Seeders
  - `CategorySeeder` for default system categories

### Changed
- Updated CLAUDE.md with:
  - Category Management section
  - Expanded Code Style guidelines
  - Enhanced Testing Guidelines
  - Database & Model patterns
  - UUID v7 documentation
  - Development workflow
  - Troubleshooting guide
  - API documentation section

- Updated User model with category/transaction relationships
- Enhanced translation files (vi/en) with category messages

### Security
- Authorization checks for category modification
- System category protection from user modification
- User-level category access control
- Transaction transfer validation (same type only)

## [0.1.0] - 2025-11-18

### Added
- Initial Laravel 12 setup
- Docker configuration (nginx, pgsql, redis)
- Laravel Passport OAuth2 authentication
- Multi-language support (Vietnamese/English)
- Service-Repository pattern implementation
- User authentication flow
  - Registration with email verification
  - Login with OAuth2 Password Grant
  - Token refresh mechanism
  - Password reset functionality
  - Email verification

- API Endpoints (9 authentication endpoints)
  - `POST /api/register`
  - `POST /api/login`
  - `GET /api/me`
  - `POST /api/refresh`
  - `POST /api/logout`
  - `POST /api/change-password`
  - `POST /api/forgot-password`
  - `POST /api/reset-password`
  - `GET /api/verify-email/{id}/{hash}`

- Infrastructure
  - PostgreSQL 17 database
  - Redis caching
  - Laravel Passport setup
  - UUID v7 for User model
  - SetLocale middleware
  - CORS configuration

- Documentation
  - CLAUDE.md for development guidelines
  - Basic API documentation

[Unreleased]: https://github.com/yourusername/balance-flow-be/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/yourusername/balance-flow-be/releases/tag/v0.1.0
