# TaskHub Cost Calculator Module

[![Version](https://img.shields.io/badge/version-0.1.0--beta-orange.svg)](https://github.com/tronderdata/td-cost-calcultaror)
[![Laravel](https://img.shields.io/badge/laravel-10.x-red.svg)](https://laravel.com)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![Status](https://img.shields.io/badge/status-beta-yellow.svg)](https://github.com/tronderdata/td-cost-calcultaror)

> **⚠️ BETA VERSION**: This module is currently in beta. Features may change and some functionality might be incomplete.

A comprehensive cost calculation system module for [TaskHub](https://github.com/SveinT83/TaskHub/tree/main) that enables businesses to accurately calculate product and service costs by allocating various cost items with flexible pricing models.

**📌 This is a module extension for TaskHub** - you need a working TaskHub installation to use this module. Visit the [TaskHub repository](https://github.com/SveinT83/TaskHub/tree/main) for core system installation and documentation.

## 🚀 Features

- **💰 Cost Item Management**: Create and manage individual cost elements (salary, rent, equipment, etc.)
- **📦 Product Cost Calculation**: Build products by allocating cost items with different allocation types
- **⏱️ Flexible Time Periods**: Support for yearly, monthly, hourly, and minute-based pricing
- **🎯 Multiple Allocation Types**: Fixed amounts, per-user costs, and per-resource-unit pricing
- **📊 Visual Cost Breakdown**: Interactive charts and detailed cost analysis
- **🏷️ Category Integration**: Seamless integration with TaskHub Categories module
- **🌍 Multi-language Support**: Full Norwegian and English localization
- **📈 Forecasting Tools**: Predict future costs and analyze trends

## 📋 Table of Contents

- [Installation](#installation)
- [Quick Start Guide](#quick-start-guide)
- [Real-World Example](#real-world-example)
- [Feature Overview](#feature-overview)
- [User Guide](#user-guide)
- [Technical Documentation](#technical-documentation)
- [Contributing](#contributing)
- [License](#license)

## 🔧 Installation

### Prerequisites
- **TaskHub Core** >= 11.0 ([Get TaskHub](https://github.com/SveinT83/TaskHub/tree/main))
- PHP >= 8.1
- Laravel >= 10.0
- MySQL/PostgreSQL

> **Important**: This module requires a working TaskHub installation. Please install and configure TaskHub first by following the [TaskHub installation guide](https://github.com/SveinT83/TaskHub/tree/main).

### Manual Installation (Recommended)

1. **Clone the repository** into your TaskHub modules directory:
```bash
cd /path/to/your/taskhub/modules
git clone https://github.com/tronderdata/td-cost-calcultaror.git
```

2. **Install dependencies** (if any):
```bash
cd td-cost-calcultaror
composer install --no-dev
```

3. **Run migrations**:
```bash
php artisan migrate --path=modules/td-cost-calcultaror/database/migrations
```

4. **Clear cache**:
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Alternative: Download ZIP
1. Download the [latest release](https://github.com/tronderdata/td-cost-calcultaror/releases) as ZIP
2. Extract to your TaskHub `modules/` directory
3. Follow steps 2-4 from manual installation above

### Composer Installation (Future)
> **Note**: This module is not yet published to Packagist. Composer installation will be available in a future release.

```bash
# This will work once published to Packagist
composer require tronderdata/td-cost-calcultaror
```

## 🚀 Quick Start Guide

### 1. Access the Module
Navigate to **Admin Panel** → **Cost Calculator** in your TaskHub installation.

### 2. Create Your First Cost Item
1. Go to **Cost Items** → **Create New**
2. Fill in basic information:
   - **Name**: "Office Rent"
   - **Price**: 15000
   - **Period**: Monthly
   - **Category**: Office Expenses

### 3. Create a Product
1. Go to **Products** → **Create New**
2. Configure your product:
   - **Name**: "Consulting Service"
   - **Calculation Model**: Per User
   - **Expected Users**: 5

### 4. Allocate Costs
1. Edit your product
2. Click **Add Cost Item**
3. Select cost items and set allocation amounts
4. View real-time cost calculations

## 🏢 Real-World Example: "Rent a Nerd" Service

Let's walk through setting up a technical consulting service with realistic Norwegian business costs.

### Step 1: Create Cost Items

#### Salary Cost
- **Name**: "Hourly Salary"
- **Price**: 531.59 (kr/hour including taxes and benefits)
- **Period**: Hour
- **Category**: Personnel

#### Office Rent
- **Name**: "Office Rent"
- **Price**: 13,385.78 (kr/month)
- **Period**: Month
- **Category**: Facilities

*Note: We manually calculated this to 63.74 kr/hour based on 21 working days × 8 hours = 168 hours/month*

### Step 2: Create the Service Product
- **Name**: "Lei en nerd" (Rent a Nerd)
- **Calculation Model**: Per Resource Unit
- **Expected Resource Units**: 1 (consultant)

### Step 3: Allocate Costs
1. **Salary**: Fixed allocation of 531.59 kr
2. **Office Rent**: Fixed allocation of 63.74 kr

### Step 4: View Results
**Total hourly cost**: 595.33 kr/hour
- Salary: 531.59 kr (89.3%)
- Office overhead: 63.74 kr (10.7%)

This gives you a data-driven foundation for pricing your consulting services with proper cost coverage.

## 📖 Feature Overview

### Cost Items Management
- ✅ Create, edit, delete cost items
- ✅ Bulk operations for efficiency
- ✅ Category organization
- ✅ Lifetime and capacity tracking
- ✅ Notes and documentation

### Product Configuration
- ✅ Multiple calculation models (per user, per resource, fixed price)
- ✅ Expected usage parameters
- ✅ Real-time cost updates
- ✅ Professional allocation management

### Allocation Types
| Type | Description | Use Case |
|------|-------------|----------|
| **Fixed** | Same amount regardless of scale | Base costs, licenses |
| **Per User** | Cost multiplied by user count | User licenses, accounts |
| **Per Resource Unit** | Cost per resource/hour/unit | Equipment, consulting time |

### Reporting & Analytics
- 📊 Cost breakdown visualization
- 📈 Monthly and yearly projections
- 🎯 Allocation distribution charts
- 📋 Detailed cost reports

## 👥 User Guide

### For Administrators
1. **Setup Categories**: Organize cost items logically
2. **Define Cost Items**: Input all business costs with accurate periods
3. **Create Product Templates**: Build reusable product configurations
4. **Monitor Usage**: Review cost allocations regularly

### For Project Managers
1. **Calculate Project Costs**: Use existing cost items for project budgeting
2. **Compare Scenarios**: Create multiple product variants
3. **Track Resource Usage**: Monitor per-user and per-resource costs
4. **Generate Reports**: Export cost breakdowns for stakeholders

### For Financial Controllers
1. **Audit Cost Accuracy**: Verify all cost items reflect reality
2. **Analyze Profitability**: Review cost vs. revenue ratios
3. **Forecast Budgets**: Use yearly projections for planning
4. **Monitor Trends**: Track cost changes over time

## 🛠️ Technical Documentation

### Database Schema
The module creates the following tables:
- `cost_items`: Core cost elements
- `products`: Product definitions
- `cost_allocations`: Cost-to-product relationships
- `cost_item_logs`: Audit trail

### API Endpoints
```bash
# Cost Items
GET /api/cost-calculator/cost-items
POST /api/cost-calculator/cost-items
PUT /api/cost-calculator/cost-items/{id}
DELETE /api/cost-calculator/cost-items/{id}

# Products
GET /api/cost-calculator/products
POST /api/cost-calculator/products
PUT /api/cost-calculator/products/{id}

# Calculations
POST /api/cost-calculator/products/{id}/calculate
GET /api/cost-calculator/products/{id}/breakdown
```

### Permissions
- `view_cost_calculator`: View cost items and products
- `edit_cost_calculator`: Create and edit cost data
- `delete_cost_calculator`: Delete cost items and products
- `export_cost_calculator`: Export cost reports

### Integration Points
- **Categories Module**: For cost item organization
- **Export System**: For data extraction
- **Permission System**: For access control
- **Cache System**: For performance optimization

## 🔄 Workflow Examples

### Scenario 1: Software Licensing Cost
1. Create cost item: "Microsoft 365 Business" (150 kr/user/month)
2. Create product: "Office Suite Access"
3. Allocate: Per-user allocation
4. Result: Automatic scaling based on user count

### Scenario 2: Equipment Depreciation
1. Create cost item: "Laptop Fleet" (25,000 kr, 36-month lifetime)
2. Calculate: 694.44 kr/month depreciation
3. Allocate: Fixed allocation to all products
4. Result: Accurate equipment cost distribution

### Scenario 3: Consulting Service Pricing
1. Combine multiple cost items (salary, rent, equipment, insurance)
2. Create different service tiers (Junior, Senior, Expert)
3. Allocate proportionally based on role
4. Result: Competitive yet profitable pricing structure

## 🐛 Known Issues & Limitations (Beta)

- [ ] **Excel Export**: Limited formatting options
- [ ] **Bulk Import**: Not yet implemented
- [ ] **Currency Support**: Only NOK currently supported
- [ ] **Historical Data**: Limited trending capabilities
- [ ] **Mobile UI**: Some responsive design improvements needed

## 🗺️ Roadmap

### Version 0.2.0
- [ ] Multi-currency support
- [ ] Advanced reporting dashboard
- [ ] Excel import/export improvements
- [ ] Mobile-optimized interface

### Version 0.3.0
- [ ] Cost templates and presets
- [ ] Integration with accounting systems
- [ ] Advanced forecasting algorithms
- [ ] Automated cost adjustments

### Version 1.0.0
- [ ] Full API documentation
- [ ] Comprehensive test coverage
- [ ] Production-ready performance
- [ ] Complete user documentation

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

### Development Setup
```bash
# Clone the repository
git clone https://github.com/tronderdata/td-cost-calcultaror.git
cd td-cost-calcultaror

# Install dependencies
composer install

# Run migrations (from your TaskHub root directory)
php artisan migrate --path=modules/td-cost-calcultaror/database/migrations

# Run tests
php artisan test modules/td-cost-calcultaror/tests/
```

### Running Tests
```bash
# Unit tests
php artisan test tests/Unit/

# Feature tests
php artisan test tests/Feature/

# All tests
php artisan test
```

## 📞 Support

- **Documentation**: [Module Wiki](https://github.com/tronderdata/td-cost-calcultaror/wiki)
- **Issues**: [GitHub Issues](https://github.com/tronderdata/td-cost-calcultaror/issues)
- **Discussions**: [GitHub Discussions](https://github.com/tronderdata/td-cost-calcultaror/discussions)
- **Email**: support@tronderdata.no

## 📜 License

This module is open-sourced software licensed under the [MIT license](LICENSE).

## 🙏 Acknowledgments

- Built as a module extension for [TaskHub](https://github.com/SveinT83/TaskHub/tree/main)
- Developed by TronderData AS
- Community feedback and contributions
- Inspired by real Norwegian business needs

---

**Made with ❤️ in Norway**

*For the core TaskHub system, visit [TaskHub Repository](https://github.com/SveinT83/TaskHub/tree/main)*  
*For more TaskHub modules, visit [TronderData Modules](https://github.com/tronderdata)*
