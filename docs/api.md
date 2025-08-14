# API Documentation

This document describes all API endpoints available in the TD Cost Calculator module, including request/response formats and examples.

## Base URL

All API endpoints are prefixed with:
```
/api/cost-calculator
```

## Authentication

All API endpoints require authentication via Laravel Sanctum or session-based authentication.

### Headers Required
```http
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
X-Requested-With: XMLHttpRequest
```

## Response Format

All API responses follow a consistent format:

### Success Response
```json
{
    "success": true,
    "data": {
        // Response data here
    },
    "message": "Operation completed successfully",
    "meta": {
        "timestamp": "2025-08-14T10:30:00Z",
        "version": "0.1.0"
    }
}
```

### Error Response
```json
{
    "success": false,
    "error": {
        "type": "ValidationException",
        "message": "The given data was invalid.",
        "details": {
            "name": ["The name field is required."],
            "price": ["The price must be a number."]
        }
    },
    "meta": {
        "timestamp": "2025-08-14T10:30:00Z",
        "version": "0.1.0"
    }
}
```

## Cost Items API

### List Cost Items

**GET** `/api/cost-calculator/cost-items`

Retrieve a paginated list of cost items.

#### Query Parameters
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `page` | integer | 1 | Page number |
| `per_page` | integer | 15 | Items per page (max 100) |
| `search` | string | - | Search in name and description |
| `category_id` | integer | - | Filter by category ID |
| `period` | string | - | Filter by period (hour, month, etc.) |
| `is_active` | boolean | true | Filter by active status |
| `sort_by` | string | name | Sort field (name, price, created_at) |
| `sort_order` | string | asc | Sort order (asc, desc) |

#### Example Request
```http
GET /api/cost-calculator/cost-items?search=salary&period=hour&page=1&per_page=10
```

#### Example Response
```json
{
    "success": true,
    "data": {
        "items": [
            {
                "id": 1,
                "name": "Hourly Salary",
                "description": "Consultant hourly rate including taxes",
                "price": 531.59,
                "period": "hour",
                "category_id": null,
                "category": null,
                "is_active": true,
                "lifetime_months": null,
                "capacity": null,
                "notes": "Includes employer taxes and benefits",
                "created_at": "2025-08-14T10:00:00Z",
                "updated_at": "2025-08-14T10:00:00Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 10,
            "total": 1,
            "total_pages": 1,
            "has_more": false
        }
    }
}
```

### Get Cost Item

**GET** `/api/cost-calculator/cost-items/{id}`

Retrieve a specific cost item by ID.

#### Example Response
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Hourly Salary",
        "description": "Consultant hourly rate including taxes",
        "price": 531.59,
        "period": "hour",
        "category_id": null,
        "category": null,
        "is_active": true,
        "lifetime_months": null,
        "capacity": null,
        "notes": "Includes employer taxes and benefits",
        "products": [
            {
                "id": 1,
                "name": "Lei en nerd",
                "pivot": {
                    "allocation_type": "fixed",
                    "allocation_value": 531.59
                }
            }
        ],
        "created_at": "2025-08-14T10:00:00Z",
        "updated_at": "2025-08-14T10:00:00Z"
    }
}
```

### Create Cost Item

**POST** `/api/cost-calculator/cost-items`

Create a new cost item.

#### Request Body
```json
{
    "name": "Office Rent",
    "description": "Monthly office rental cost",
    "price": 13385.78,
    "period": "month",
    "category_id": 1,
    "is_active": true,
    "lifetime_months": null,
    "capacity": null,
    "notes": "Prime location downtown office space"
}
```

#### Validation Rules
| Field | Rules |
|-------|-------|
| `name` | required, string, max:255, unique:cost_items |
| `description` | nullable, string |
| `price` | required, numeric, min:0, max:999999.99 |
| `period` | required, in:minute,hour,day,week,month,year |
| `category_id` | nullable, exists:categories,id |
| `is_active` | boolean |
| `lifetime_months` | nullable, integer, min:1, max:600 |
| `capacity` | nullable, numeric, min:0 |
| `notes` | nullable, string |

#### Example Response
```json
{
    "success": true,
    "data": {
        "id": 2,
        "name": "Office Rent",
        "description": "Monthly office rental cost",
        "price": 13385.78,
        "period": "month",
        "category_id": 1,
        "is_active": true,
        "created_at": "2025-08-14T10:30:00Z",
        "updated_at": "2025-08-14T10:30:00Z"
    },
    "message": "Cost item created successfully"
}
```

### Update Cost Item

**PUT** `/api/cost-calculator/cost-items/{id}`

Update an existing cost item.

#### Request Body
Same as create, all fields optional except validation constraints.

### Delete Cost Item

**DELETE** `/api/cost-calculator/cost-items/{id}`

Delete a cost item. Only allowed if not allocated to any products.

#### Example Response
```json
{
    "success": true,
    "message": "Cost item deleted successfully"
}
```

## Products API

### List Products

**GET** `/api/cost-calculator/products`

Retrieve a paginated list of products.

#### Query Parameters
Similar to cost items, plus:
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `calculation_model` | string | - | Filter by calculation model |

#### Example Response
```json
{
    "success": true,
    "data": {
        "items": [
            {
                "id": 1,
                "name": "Lei en nerd",
                "description": "Technical consulting service",
                "calculation_model": "per_resource",
                "expected_users": null,
                "expected_resource_units": 1,
                "is_active": true,
                "total_cost": 595.33,
                "cost_breakdown": {
                    "hourly": 595.33,
                    "monthly": 100176.00,
                    "yearly": 1202112.00
                },
                "allocations_count": 2,
                "created_at": "2025-08-14T10:00:00Z",
                "updated_at": "2025-08-14T10:00:00Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 15,
            "total": 1,
            "total_pages": 1,
            "has_more": false
        }
    }
}
```

### Get Product

**GET** `/api/cost-calculator/products/{id}`

Retrieve a specific product with cost allocations.

#### Query Parameters
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `include_allocations` | boolean | true | Include cost allocations |
| `calculate_costs` | boolean | true | Include calculated costs |

#### Example Response
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Lei en nerd",
        "description": "Technical consulting service with hourly billing",
        "calculation_model": "per_resource",
        "expected_users": null,
        "expected_resource_units": 1,
        "is_active": true,
        "allocations": [
            {
                "id": 1,
                "allocation_type": "fixed",
                "allocation_value": 531.59,
                "cost_item": {
                    "id": 1,
                    "name": "Hourly Salary",
                    "price": 531.59,
                    "period": "hour"
                }
            },
            {
                "id": 2,
                "allocation_type": "fixed",
                "allocation_value": 63.74,
                "cost_item": {
                    "id": 2,
                    "name": "Office Rent",
                    "price": 13385.78,
                    "period": "month"
                }
            }
        ],
        "calculated_costs": {
            "total_hourly": 595.33,
            "total_monthly": 100176.00,
            "total_yearly": 1202112.00,
            "breakdown": [
                {
                    "cost_item": "Hourly Salary",
                    "amount": 531.59,
                    "percentage": 89.3
                },
                {
                    "cost_item": "Office Rent",
                    "amount": 63.74,
                    "percentage": 10.7
                }
            ]
        },
        "created_at": "2025-08-14T10:00:00Z",
        "updated_at": "2025-08-14T10:00:00Z"
    }
}
```

### Create Product

**POST** `/api/cost-calculator/products`

Create a new product.

#### Request Body
```json
{
    "name": "Team Office Suite",
    "description": "Complete office solution for development teams",
    "calculation_model": "per_user",
    "expected_users": 10,
    "expected_resource_units": null,
    "is_active": true,
    "notes": "Includes workspace, equipment, and utilities per team member"
}
```

#### Validation Rules
| Field | Rules |
|-------|-------|
| `name` | required, string, max:255, unique:products |
| `description` | nullable, string |
| `calculation_model` | required, in:per_user,per_resource,fixed_price |
| `expected_users` | required_if:calculation_model,per_user, integer, min:1 |
| `expected_resource_units` | required_if:calculation_model,per_resource, integer, min:1 |
| `is_active` | boolean |
| `notes` | nullable, string |

### Update Product

**PUT** `/api/cost-calculator/products/{id}`

Update an existing product.

### Delete Product

**DELETE** `/api/cost-calculator/products/{id}`

Delete a product and all its cost allocations.

## Cost Allocations API

### Add Cost Item to Product

**POST** `/api/cost-calculator/products/{productId}/allocations`

Allocate a cost item to a product.

#### Request Body
```json
{
    "cost_item_id": 1,
    "allocation_type": "fixed",
    "allocation_value": 531.59,
    "notes": "Direct consultant hourly rate"
}
```

#### Validation Rules
| Field | Rules |
|-------|-------|
| `cost_item_id` | required, exists:cost_items,id |
| `allocation_type` | required, in:fixed,per_user,per_resource_unit,percentage |
| `allocation_value` | required, numeric, min:0 |
| `notes` | nullable, string |

### Update Allocation

**PUT** `/api/cost-calculator/products/{productId}/allocations/{allocationId}`

Update an existing cost allocation.

#### Request Body
```json
{
    "allocation_type": "per_user",
    "allocation_value": 100.00,
    "notes": "Updated to per-user allocation"
}
```

### Remove Allocation

**DELETE** `/api/cost-calculator/products/{productId}/allocations/{allocationId}`

Remove a cost allocation from a product.

## Calculation API

### Calculate Product Costs

**POST** `/api/cost-calculator/products/{id}/calculate`

Calculate real-time costs for a product with custom parameters.

#### Request Body
```json
{
    "user_count": 5,
    "resource_units": 2,
    "period": "month",
    "include_breakdown": true
}
```

#### Example Response
```json
{
    "success": true,
    "data": {
        "product_id": 1,
        "parameters": {
            "user_count": 5,
            "resource_units": 2,
            "period": "month"
        },
        "total_cost": 200352.00,
        "period": "month",
        "breakdown": [
            {
                "cost_item": "Hourly Salary",
                "base_amount": 531.59,
                "calculated_amount": 178612.80,
                "allocation_type": "fixed",
                "multiplier": 2,
                "percentage": 89.3
            },
            {
                "cost_item": "Office Rent",
                "base_amount": 63.74,
                "calculated_amount": 21739.20,
                "allocation_type": "fixed",
                "multiplier": 2,
                "percentage": 10.7
            }
        ],
        "summary": {
            "base_hourly_cost": 595.33,
            "total_hourly_cost": 1190.66,
            "working_hours_per_month": 168,
            "total_monthly_cost": 200352.00
        }
    }
}
```

### Get Cost Breakdown

**GET** `/api/cost-calculator/products/{id}/breakdown`

Get detailed cost breakdown for a product.

#### Query Parameters
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `period` | string | hour | Target period for breakdown |
| `user_count` | integer | - | Override expected users |
| `resource_units` | integer | - | Override expected resource units |

## Export API

### Export Cost Items

**GET** `/api/cost-calculator/export/cost-items`

Export cost items to various formats.

#### Query Parameters
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `format` | string | json | Export format (json, csv, excel) |
| `filters` | object | {} | Same filters as list endpoint |

#### Example Response (JSON)
```json
{
    "success": true,
    "data": {
        "export_url": "/storage/exports/cost-items-20250814-103000.json",
        "filename": "cost-items-20250814-103000.json",
        "format": "json",
        "record_count": 25,
        "generated_at": "2025-08-14T10:30:00Z",
        "expires_at": "2025-08-15T10:30:00Z"
    }
}
```

### Export Products

**GET** `/api/cost-calculator/export/products`

Export products with calculated costs.

### Export Cost Report

**GET** `/api/cost-calculator/export/report`

Generate comprehensive cost report.

#### Query Parameters
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `format` | string | excel | Export format |
| `include_breakdown` | boolean | true | Include detailed breakdown |
| `product_ids` | array | [] | Specific products to include |

## Forecasting API

### Generate Forecast

**POST** `/api/cost-calculator/forecast`

Generate cost forecasts based on historical data and projections.

#### Request Body
```json
{
    "product_ids": [1, 2],
    "projection_months": 12,
    "growth_rate": 0.05,
    "include_trends": true
}
```

#### Example Response
```json
{
    "success": true,
    "data": {
        "forecast_period": "2025-08-14 to 2026-08-14",
        "products": [
            {
                "product_id": 1,
                "product_name": "Lei en nerd",
                "current_monthly_cost": 100176.00,
                "projected_costs": [
                    {
                        "month": "2025-09",
                        "cost": 105184.80,
                        "growth": 5.0
                    }
                ],
                "total_projection": 1265184.00,
                "trends": {
                    "average_monthly_growth": 5.0,
                    "seasonal_factors": []
                }
            }
        ],
        "summary": {
            "total_current_monthly": 100176.00,
            "total_projected_yearly": 1265184.00,
            "estimated_growth": 26.2
        }
    }
}
```

## Error Codes

| Code | Description |
|------|-------------|
| `CALC_001` | Invalid calculation parameters |
| `CALC_002` | Missing required allocation data |
| `CALC_003` | Division by zero in calculation |
| `VAL_001` | Validation failed |
| `AUTH_001` | Authentication required |
| `AUTH_002` | Insufficient permissions |
| `NOT_FOUND` | Resource not found |
| `CONFLICT` | Resource conflict (e.g., trying to delete allocated cost item) |

## Rate Limiting

API endpoints are rate limited:
- **Authenticated users**: 1000 requests per hour
- **Calculation endpoints**: 100 requests per hour
- **Export endpoints**: 10 requests per hour

Rate limit headers are included in responses:
```http
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1692009600
```

## Webhooks (Future)

Future versions will support webhooks for:
- Cost item changes
- Product cost updates
- Allocation modifications
- Export completion

---

*This API documentation is generated automatically and reflects the current state of the TD Cost Calculator module.*
