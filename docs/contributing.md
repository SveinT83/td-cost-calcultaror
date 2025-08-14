# Contributing Guide

Thank you for your interest in contributing to the TD Cost Calculator module! This guide will help you get started with contributing code, documentation, and ideas.

## 🤝 How to Contribute

### Types of Contributions
- **Bug Reports**: Found an issue? Let us know!
- **Feature Requests**: Have an idea for improvement?
- **Code Contributions**: Fix bugs or implement new features
- **Documentation**: Improve or add documentation
- **Testing**: Help us improve test coverage
- **Translations**: Add support for new languages

## 🚀 Getting Started

### 1. Fork and Clone
```bash
# Fork the repository on GitHub
# Then clone your fork
git clone https://github.com/YOUR_USERNAME/td-cost-calcultaror.git
cd td-cost-calcultaror

# Add the upstream repository
git remote add upstream https://github.com/tronderdata/td-cost-calcultaror.git
```

### 2. Set Up Development Environment
Follow the [Development Setup Guide](development-setup.md) to get your local environment ready.

### 3. Create a Branch
```bash
# Create a new branch for your feature or fix
git checkout -b feature/your-feature-name

# Or for bug fixes
git checkout -b fix/issue-description
```

## 📝 Development Workflow

### Branch Naming Convention
- **Features**: `feature/feature-name`
- **Bug Fixes**: `fix/bug-description`
- **Documentation**: `docs/what-you-are-documenting`
- **Refactoring**: `refactor/what-you-are-refactoring`
- **Tests**: `test/what-you-are-testing`

### Commit Message Format
We follow the [Conventional Commits](https://www.conventionalcommits.org/) specification:

```
type(scope): description

[optional body]

[optional footer(s)]
```

#### Types
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

#### Examples
```bash
feat(cost-calculation): add support for percentage-based allocations

fix(ui): resolve modal display issue on mobile devices

docs(api): update endpoint documentation for cost calculations

test(models): add unit tests for CostItem model validation
```

### Code Quality Standards

#### PHP Standards
- Follow **PSR-12** coding standard
- Use **meaningful variable and method names**
- Add **PHPDoc** comments for all public methods
- Keep methods **focused and small** (prefer < 20 lines)
- Use **type hints** for all parameters and return values

```php
/**
 * Calculate the total cost for a product based on its allocations.
 *
 * @param Product $product The product to calculate costs for
 * @param array $parameters Calculation parameters (user_count, etc.)
 * @return array Calculated costs with breakdown
 * @throws InvalidCalculationException When parameters are invalid
 */
public function calculateTotalCost(Product $product, array $parameters): array
{
    // Implementation here
}
```

#### Frontend Standards
- Use **consistent indentation** (4 spaces)
- Add **JSDoc** comments for complex functions
- Follow **ES6+ standards**
- Use **meaningful variable names**

```javascript
/**
 * Calculate cost breakdown for display in charts
 * @param {Array} allocations - Cost allocations data
 * @param {Object} parameters - Calculation parameters
 * @returns {Object} Formatted data for chart display
 */
function formatCostBreakdown(allocations, parameters) {
    // Implementation here
}
```

### Testing Requirements

#### Unit Tests
All new code must include unit tests:

```php
class CostCalculationTest extends TestCase
{
    /** @test */
    public function it_calculates_fixed_allocation_correctly()
    {
        $costItem = CostItem::factory()->create([
            'price' => 100.00,
            'period' => 'hour'
        ]);
        
        $allocation = CostAllocation::factory()->create([
            'cost_item_id' => $costItem->id,
            'allocation_type' => 'fixed',
            'allocation_value' => 50.00
        ]);
        
        $result = $this->calculator->calculateAllocation($allocation, []);
        
        $this->assertEquals(50.00, $result);
    }
}
```

#### Feature Tests
Test complete user workflows:

```php
class ProductManagementTest extends TestCase
{
    /** @test */
    public function user_can_create_product_with_cost_allocations()
    {
        $user = User::factory()->create();
        $costItem = CostItem::factory()->create();
        
        $response = $this->actingAs($user)
            ->post('/admin/cost-calculator/products', [
                'name' => 'Test Product',
                'calculation_model' => 'per_user',
                'expected_users' => 5
            ]);
            
        $response->assertStatus(201);
        $this->assertDatabaseHas('products', [
            'name' => 'Test Product'
        ]);
    }
}
```

#### Running Tests
```bash
# Run all tests
php artisan test modules/td-cost-calcultaror/tests/

# Run specific test file
php artisan test modules/td-cost-calcultaror/tests/Unit/CostCalculationTest.php

# Run with coverage
php artisan test --coverage --min=80
```

### Code Review Process

#### Before Submitting
1. **Run all tests** and ensure they pass
2. **Check code style** with PHP CS Fixer
3. **Run static analysis** with PHPStan
4. **Update documentation** if needed
5. **Test manually** in browser

```bash
# Code quality checks
./vendor/bin/phpcs src/ --standard=PSR12
./vendor/bin/phpstan analyse src/ --level=8
php artisan test modules/td-cost-calcultaror/tests/
```

#### Pull Request Template
Use this template for your pull request description:

```markdown
## Description
Brief description of changes made.

## Type of Change
- [ ] Bug fix (non-breaking change which fixes an issue)
- [ ] New feature (non-breaking change which adds functionality)
- [ ] Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] Documentation update

## Testing
- [ ] Unit tests added/updated
- [ ] Feature tests added/updated
- [ ] Manual testing completed
- [ ] All tests pass

## Checklist
- [ ] Code follows project style guidelines
- [ ] Self-review completed
- [ ] Documentation updated (if applicable)
- [ ] No merge conflicts

## Screenshots (if applicable)
Add screenshots to help explain your changes.

## Related Issues
Fixes #123
```

## 🐛 Bug Reports

### Before Submitting
1. **Check existing issues** to avoid duplicates
2. **Search documentation** for known solutions
3. **Test with latest version** of the module
4. **Reproduce the issue** consistently

### Bug Report Template
```markdown
**Bug Description**
A clear description of what the bug is.

**To Reproduce**
Steps to reproduce the behavior:
1. Go to '...'
2. Click on '...'
3. Scroll down to '...'
4. See error

**Expected Behavior**
What you expected to happen.

**Screenshots**
If applicable, add screenshots.

**Environment:**
- TaskHub Version: [e.g., 11.2]
- TD Cost Calculator Version: [e.g., 0.1.0-beta]
- PHP Version: [e.g., 8.2]
- Browser: [e.g., Chrome 115]
- OS: [e.g., Ubuntu 22.04]

**Additional Context**
Any other context about the problem.

**Error Logs**
```
Paste relevant error logs here
```
```

## 💡 Feature Requests

### Feature Request Template
```markdown
**Is your feature request related to a problem?**
A clear description of what the problem is.

**Describe the solution you'd like**
A clear description of what you want to happen.

**Describe alternatives you've considered**
Other solutions you've considered.

**Additional context**
Any other context or screenshots.

**Impact**
- Who would benefit from this feature?
- How often would it be used?
- Is this a blocking issue?
```

## 🌍 Translations

### Adding New Language Support

1. **Create language directory**:
```bash
mkdir src/resources/lang/es  # For Spanish
```

2. **Copy messages file**:
```bash
cp src/resources/lang/en/messages.php src/resources/lang/es/messages.php
```

3. **Translate all strings**:
```php
<?php

return [
    'cost_items' => 'Elementos de Costo',
    'products' => 'Productos',
    'dashboard' => 'Panel de Control',
    // ... translate all strings
];
```

4. **Test the translation**:
```bash
# Test in browser with ?lang=es
http://localhost:8000/admin/cost-calculator?lang=es
```

### Translation Guidelines
- **Use gender-neutral terms** when possible
- **Maintain consistent terminology** throughout
- **Consider cultural context** for financial terms
- **Test with real data** to ensure proper formatting

## 📊 Performance Contributions

### Identifying Performance Issues
- Use **Laravel Debugbar** for query analysis
- Monitor **memory usage** during calculations
- Test with **large datasets** (1000+ cost items)
- Profile **calculation algorithms** for optimization

### Performance Testing
```php
class PerformanceTest extends TestCase
{
    /** @test */
    public function large_cost_calculation_completes_within_time_limit()
    {
        $product = Product::factory()
            ->has(CostAllocation::factory()->count(100))
            ->create();
            
        $start = microtime(true);
        
        $result = app(CalculationService::class)
            ->calculateTotalCost($product, ['user_count' => 1000]);
            
        $executionTime = microtime(true) - $start;
        
        $this->assertLessThan(2.0, $executionTime); // Max 2 seconds
        $this->assertNotNull($result);
    }
}
```

## 🔒 Security Contributions

### Security Guidelines
- **Validate all inputs** thoroughly
- **Use parameterized queries** (Eloquent handles this)
- **Sanitize outputs** when displaying user data
- **Follow OWASP guidelines** for web security
- **Test authorization** for all endpoints

### Security Testing
```php
class SecurityTest extends TestCase
{
    /** @test */
    public function unauthorized_users_cannot_access_cost_data()
    {
        $response = $this->get('/admin/cost-calculator');
        
        $response->assertRedirect('/login');
    }
    
    /** @test */
    public function users_cannot_access_other_users_cost_items()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $costItem = CostItem::factory()->create(['created_by' => $user1->id]);
        
        $response = $this->actingAs($user2)
            ->get("/admin/cost-calculator/cost-items/{$costItem->id}");
            
        $response->assertStatus(403);
    }
}
```

## 📖 Documentation Contributions

### Documentation Standards
- **Write in clear, simple English**
- **Include code examples** for all concepts
- **Use consistent formatting** and structure
- **Update both English and Norwegian** versions when applicable
- **Include screenshots** for UI changes

### Documentation Structure
```
docs/
├── README.md              # Overview and index
├── development-setup.md   # Getting started for developers
├── architecture.md        # System design and patterns
├── database-schema.md     # Database structure
├── api.md                # API documentation
├── testing.md            # Testing strategies
├── contributing.md       # This file
└── troubleshooting.md    # Common issues and solutions
```

## 🎯 Areas Needing Contribution

### High Priority
- [ ] **Multi-currency support** - EUR, USD, SEK support
- [ ] **Bulk import/export** - Excel/CSV data management
- [ ] **Advanced forecasting** - Machine learning predictions
- [ ] **Mobile UI improvements** - Responsive design fixes

### Medium Priority
- [ ] **Cost templates** - Predefined cost structures
- [ ] **API rate limiting** - Improve API performance
- [ ] **Audit improvements** - Enhanced logging
- [ ] **Integration tests** - More comprehensive testing

### Low Priority
- [ ] **Dark mode support** - UI theme improvements
- [ ] **Keyboard shortcuts** - Power user features
- [ ] **Advanced charts** - More visualization options
- [ ] **Cost optimization suggestions** - AI-powered recommendations

## 🏆 Recognition

Contributors will be recognized in:
- **README.md** contributors section
- **CHANGELOG.md** for each release
- **GitHub Contributors** page
- **Release notes** for significant contributions

## 📞 Getting Help

### Communication Channels
- **GitHub Issues**: Bug reports and feature requests
- **GitHub Discussions**: General questions and ideas
- **Email**: technical-support@tronderdata.no
- **Documentation**: Check existing docs first

### Response Times
- **Bug reports**: 2-3 business days
- **Feature requests**: 1 week
- **Pull requests**: 3-5 business days
- **Questions**: 1-2 business days

## 📜 License

By contributing to TD Cost Calculator, you agree that your contributions will be licensed under the same [MIT License](../LICENSE) that covers the project.

---

**Thank you for contributing to make TD Cost Calculator better! 🚀**

*Every contribution, no matter how small, makes a difference. We appreciate your time and effort in helping improve this project.*
