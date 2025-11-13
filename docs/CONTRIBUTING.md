# Contributing Guide - Balance Flow Backend

## Welcome Contributors!

Thank you for considering contributing to Balance Flow Backend! This guide will help you understand our development process, coding standards, and how to submit your contributions.

---

## Table of Contents

1. [Code of Conduct](#code-of-conduct)
2. [Getting Started](#getting-started)
3. [Development Workflow](#development-workflow)
4. [Coding Standards](#coding-standards)
5. [Commit Guidelines](#commit-guidelines)
6. [Pull Request Process](#pull-request-process)
7. [Testing Requirements](#testing-requirements)
8. [Documentation](#documentation)
9. [Issue Reporting](#issue-reporting)
10. [Communication Channels](#communication-channels)

---

## Code of Conduct

### Our Pledge

We are committed to providing a welcoming and inspiring community for all. Please be respectful and constructive in all interactions.

### Expected Behavior

- ✅ Use welcoming and inclusive language
- ✅ Be respectful of differing viewpoints
- ✅ Accept constructive criticism gracefully
- ✅ Focus on what's best for the community
- ✅ Show empathy towards others

### Unacceptable Behavior

- ❌ Harassment or discriminatory language
- ❌ Trolling or inflammatory comments
- ❌ Public or private harassment
- ❌ Publishing others' private information
- ❌ Other unprofessional conduct

---

## Getting Started

### Prerequisites

Before contributing, ensure you have:

- **Docker**: 24.0+
- **Docker Compose**: 2.20+
- **Git**: 2.30+
- **Code Editor**: VS Code, PHPStorm, or similar
- **Basic Laravel Knowledge**: Familiarity with Laravel 12

### Fork & Clone

```bash
# 1. Fork the repository on GitHub

# 2. Clone your fork
git clone https://github.com/YOUR_USERNAME/balance-flow-be.git
cd balance-flow-be

# 3. Add upstream remote
git remote add upstream https://github.com/tuanldas/balance-flow-be.git

# 4. Verify remotes
git remote -v
```

### Local Setup

```bash
# 1. Copy environment file
cp .env.example .env

# 2. Start Docker containers
docker compose up -d

# 3. Install dependencies
docker compose exec app composer install
docker compose exec app npm install

# 4. Generate application key
docker compose exec app php artisan key:generate

# 5. Run migrations
docker compose exec app php artisan migrate --seed

# 6. Install Passport
docker compose exec app php artisan passport:install

# 7. Run tests to verify setup
docker compose exec app php artisan test
```

---

## Development Workflow

### Branch Strategy

We follow **Git Flow** branching model:

```
main (production-ready)
  ↓
develop (integration branch)
  ↓
feature/* (new features)
bugfix/* (bug fixes)
hotfix/* (urgent production fixes)
release/* (release preparation)
```

### Creating a Feature Branch

```bash
# 1. Update develop branch
git checkout develop
git pull upstream develop

# 2. Create feature branch
git checkout -b feature/your-feature-name

# 3. Work on your feature
# ... make changes ...

# 4. Commit changes (see commit guidelines)
git add .
git commit -m "feat: add user authentication"

# 5. Push to your fork
git push origin feature/your-feature-name

# 6. Create Pull Request on GitHub
```

### Branch Naming Convention

| Type | Prefix | Example |
|------|--------|---------|
| New feature | `feature/` | `feature/user-profile` |
| Bug fix | `bugfix/` | `bugfix/login-error` |
| Hotfix | `hotfix/` | `hotfix/security-patch` |
| Release | `release/` | `release/v1.2.0` |
| Documentation | `docs/` | `docs/api-guide` |
| Refactoring | `refactor/` | `refactor/user-service` |

---

## Coding Standards

### Laravel Conventions

Follow **Laravel Best Practices** and the project's `CLAUDE.md` guidelines:

1. **Use Eloquent ORM** instead of raw queries
2. **Create Form Requests** for validation
3. **Use API Resources** for transformations
4. **Follow naming conventions** (see below)
5. **Write tests** for all features

### PHP Standards

We follow **PSR-12** coding standard enforced by **Laravel Pint**.

#### Run Code Formatter

```bash
# Format all files
docker compose exec app vendor/bin/pint

# Check formatting without fixing
docker compose exec app vendor/bin/pint --test

# Format specific file
docker compose exec app vendor/bin/pint app/Models/User.php
```

### Naming Conventions

#### Controllers

```php
// ✅ Good: Plural, suffixed with Controller
class UsersController extends Controller {}
class TransactionsController extends Controller {}

// ❌ Bad
class User extends Controller {}
class ManageUsers extends Controller {}
```

#### Models

```php
// ✅ Good: Singular, PascalCase
class User extends Model {}
class Transaction extends Model {}

// ❌ Bad
class Users extends Model {}
class user extends Model {}
```

#### Methods

```php
// ✅ Good: Descriptive, camelCase
public function getUserBalance(User $user): float {}
public function isEligibleForDiscount(): bool {}

// ❌ Bad
public function balance() {}  // Too vague
public function get_balance() {}  // snake_case
```

#### Variables

```php
// ✅ Good: Descriptive, camelCase
$userBalance = 100.00;
$isActive = true;
$totalTransactions = 42;

// ❌ Bad
$ub = 100.00;  // Too short
$user_balance = 100.00;  // snake_case
```

#### Database

```php
// Tables: plural, snake_case
'users', 'transactions', 'account_balances'

// Columns: snake_case
'user_id', 'created_at', 'is_active'

// Foreign keys: {table}_id
'user_id', 'account_id', 'transaction_id'
```

### Type Declarations

Always use type hints and return types:

```php
// ✅ Good
public function calculateBalance(User $user, ?float $adjustment = null): float
{
    // ...
}

// ❌ Bad
public function calculateBalance($user, $adjustment = null)
{
    // ...
}
```

### PHPDoc Blocks

Add PHPDoc for complex methods:

```php
/**
 * Calculate user's total balance including pending transactions.
 *
 * @param User $user The user to calculate balance for
 * @param bool $includePending Whether to include pending transactions
 * @return float The calculated balance
 * @throws InsufficientBalanceException If balance is negative
 */
public function calculateTotalBalance(User $user, bool $includePending = false): float
{
    // ...
}
```

### Code Organization

```php
// Order of class members:
class UserController extends Controller
{
    // 1. Constants
    private const MAX_ATTEMPTS = 3;

    // 2. Properties
    protected UserService $userService;

    // 3. Constructor
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    // 4. Public methods
    public function index() {}
    public function store() {}

    // 5. Protected methods
    protected function validateUser() {}

    // 6. Private methods
    private function calculateMetrics() {}
}
```

---

## Commit Guidelines

### Commit Message Format

We follow **Conventional Commits** specification:

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types

| Type | Description | Example |
|------|-------------|---------|
| `feat` | New feature | `feat(auth): add OAuth login` |
| `fix` | Bug fix | `fix(api): resolve null pointer error` |
| `docs` | Documentation | `docs(readme): update installation steps` |
| `style` | Code style (formatting) | `style: run Laravel Pint` |
| `refactor` | Code refactoring | `refactor(user): simplify balance calculation` |
| `test` | Add/update tests | `test(auth): add login integration tests` |
| `chore` | Maintenance tasks | `chore: update dependencies` |
| `perf` | Performance improvement | `perf(db): add index on users.email` |

### Examples

#### Good Commit Messages

```bash
# Feature
git commit -m "feat(transactions): add CSV export functionality"

# Bug fix
git commit -m "fix(auth): resolve token refresh issue"

# Documentation
git commit -m "docs(api): add pagination examples"

# With body
git commit -m "feat(notifications): add email notifications

- Implement notification service
- Create email templates
- Add queue job for sending
- Update user settings

Closes #123"
```

#### Bad Commit Messages

```bash
❌ "fixed stuff"
❌ "WIP"
❌ "asdf"
❌ "Updated files"
❌ "changes"
```

### Commit Best Practices

1. **One logical change per commit**
2. **Write in present tense**: "add feature" not "added feature"
3. **Keep subject under 50 characters**
4. **Use body to explain why, not what**
5. **Reference issues**: `Closes #123`, `Fixes #456`

---

## Pull Request Process

### Before Submitting

Ensure your PR meets these requirements:

- [ ] Code follows Laravel and PSR-12 standards
- [ ] Tests pass: `php artisan test`
- [ ] Code formatted: `vendor/bin/pint`
- [ ] No merge conflicts with `develop` branch
- [ ] Documentation updated (if applicable)
- [ ] Commit messages follow conventions
- [ ] PR description is clear and complete

### PR Title Format

Follow the same format as commit messages:

```
feat(auth): implement two-factor authentication
fix(api): resolve pagination issue in transactions endpoint
docs(contributing): add commit message guidelines
```

### PR Description Template

```markdown
## Description
Brief description of changes made.

## Type of Change
- [ ] Bug fix (non-breaking change)
- [ ] New feature (non-breaking change)
- [ ] Breaking change (fix or feature causing existing functionality to break)
- [ ] Documentation update

## Changes Made
- Change 1
- Change 2
- Change 3

## Testing
Describe how you tested these changes.

## Screenshots (if applicable)
Add screenshots for UI changes.

## Related Issues
Closes #123
Relates to #456

## Checklist
- [ ] My code follows the project's coding standards
- [ ] I have run Laravel Pint
- [ ] I have added tests covering my changes
- [ ] All tests pass
- [ ] I have updated documentation
- [ ] My changes generate no new warnings
```

### Review Process

1. **Automated checks run** (tests, linting)
2. **Code review** by maintainers
3. **Request changes** if needed
4. **Approval** when ready
5. **Merge** to develop branch

### Getting Your PR Merged Faster

- Write clear, descriptive PR descriptions
- Keep PRs focused and small
- Respond promptly to feedback
- Rebase on latest develop if needed
- Ensure all CI checks pass

---

## Testing Requirements

### Test Coverage Goals

- **Models**: 80%+ coverage
- **Controllers**: 70%+ coverage
- **Services**: 90%+ coverage
- **API Endpoints**: 100% of critical paths

### Writing Tests

#### Feature Tests

```php
// tests/Feature/UserApiTest.php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_their_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/user');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'email' => $user->email,
                ]
            ]);
    }
}
```

#### Unit Tests

```php
// tests/Unit/UserServiceTest.php
namespace Tests\Unit;

use Tests\TestCase;
use App\Services\UserService;
use App\Models\User;

class UserServiceTest extends TestCase
{
    public function test_calculates_user_balance_correctly(): void
    {
        $user = User::factory()->create();
        $service = new UserService();

        $balance = $service->calculateBalance($user);

        $this->assertIsFloat($balance);
        $this->assertGreaterThanOrEqual(0, $balance);
    }
}
```

### Running Tests

```bash
# Run all tests
docker compose exec app php artisan test

# Run specific test file
docker compose exec app php artisan test tests/Feature/UserApiTest.php

# Run with coverage
docker compose exec app php artisan test --coverage

# Run specific test method
docker compose exec app php artisan test --filter=test_user_can_login
```

### Test Factories

Use factories for test data:

```php
// Create one user
$user = User::factory()->create();

// Create multiple users
$users = User::factory()->count(10)->create();

// Custom attributes
$user = User::factory()->create([
    'email' => 'test@example.com',
    'email_verified_at' => now(),
]);

// Factory states
$unverifiedUser = User::factory()->unverified()->create();
```

---

## Documentation

### Code Documentation

```php
// ✅ Good: Clear PHPDoc
/**
 * Process a payment transaction.
 *
 * @param User $user The user making the payment
 * @param float $amount The payment amount
 * @param string $currency The currency code (USD, EUR, etc.)
 * @return Transaction The created transaction
 * @throws InsufficientBalanceException When user balance is insufficient
 * @throws InvalidCurrencyException When currency is not supported
 */
public function processPayment(User $user, float $amount, string $currency): Transaction
{
    // Implementation
}
```

### API Documentation

Update `docs/API.md` when adding/modifying endpoints:

```markdown
### Create Transaction

**Endpoint**: `POST /api/v1/transactions`

**Request**:
\`\`\`json
{
  "amount": 100.00,
  "type": "income",
  "description": "Salary"
}
\`\`\`

**Response (201)**:
\`\`\`json
{
  "data": {
    "id": "uuid",
    "amount": 100.00,
    "type": "income"
  }
}
\`\`\`
```

### Architecture Documentation

Update `docs/ARCHITECTURE.md` for:
- New design patterns
- Architectural decisions
- System integrations
- Major refactorings

---

## Issue Reporting

### Before Creating an Issue

1. **Search existing issues** to avoid duplicates
2. **Check documentation** for solutions
3. **Verify it's not a local setup issue**
4. **Reproduce in a clean environment**

### Bug Report Template

```markdown
**Bug Description**
Clear description of the bug.

**Steps to Reproduce**
1. Go to '...'
2. Click on '...'
3. See error

**Expected Behavior**
What should happen.

**Actual Behavior**
What actually happens.

**Environment**
- OS: Ubuntu 22.04
- Docker: 24.0.5
- Laravel: 12.0
- PHP: 8.4.14

**Screenshots**
If applicable.

**Additional Context**
Any other relevant information.
```

### Feature Request Template

```markdown
**Feature Description**
Clear description of the proposed feature.

**Use Case**
Why this feature is needed.

**Proposed Solution**
How it could be implemented.

**Alternatives Considered**
Other approaches you've thought about.

**Additional Context**
Mockups, examples, etc.
```

---

## Communication Channels

### GitHub

- **Issues**: Bug reports, feature requests
- **Discussions**: Questions, ideas, general discussion
- **Pull Requests**: Code contributions

### Response Times

- **Bug reports**: Within 48 hours
- **Feature requests**: Within 1 week
- **Pull requests**: Within 3-5 business days

---

## Development Tips

### Useful Commands

```bash
# Code formatting
vendor/bin/pint

# Run tests
php artisan test

# Database refresh
php artisan migrate:fresh --seed

# Clear all caches
php artisan optimize:clear

# Generate IDE helper (if installed)
php artisan ide-helper:generate

# Run tinker (REPL)
php artisan tinker

# Check routes
php artisan route:list

# Check migrations
php artisan migrate:status
```

### Debug Tools

1. **Laravel Debugbar** (development)
2. **Laravel Telescope** (development)
3. **Clockwork** (API debugging)
4. **Ray** (debugging tool)

### IDE Configuration

#### VS Code Extensions

- PHP Intelephense
- Laravel Extension Pack
- PHP Debug (Xdebug)
- EditorConfig

#### PHPStorm Plugins

- Laravel Idea
- PHP Toolbox
- .env files support

---

## Security

### Reporting Security Issues

**DO NOT** create public issues for security vulnerabilities.

Email: security@balanceflow.com (if applicable)

Include:
- Description of vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (optional)

### Security Best Practices

- Never commit `.env` files
- Don't expose sensitive data in logs
- Use parameterized queries (Eloquent does this)
- Validate all user input
- Use HTTPS in production
- Keep dependencies updated

---

## License

By contributing, you agree that your contributions will be licensed under the same license as the project (typically MIT or similar).

---

## Recognition

Contributors will be recognized in:
- GitHub contributors page
- CONTRIBUTORS.md file (if created)
- Release notes for significant contributions

---

## Questions?

If you have questions not covered in this guide:

1. Check the [documentation](../README.md)
2. Search [existing issues](https://github.com/tuanldas/balance-flow-be/issues)
3. Ask in [GitHub Discussions](https://github.com/tuanldas/balance-flow-be/discussions)
4. Contact maintainers

---

## Thank You!

Your contributions make Balance Flow better for everyone. We appreciate your time and effort! 🙏

---

**Contributing Guide Version**: 1.0
**Last Updated**: 2025-11-13
**Maintainer**: Development Team
