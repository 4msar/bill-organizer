# API Documentation - Bill Organizer

**Version:** 1.0  
**Base URL:** `/api/v1`  
**Authentication:** Bearer Token (Laravel Sanctum)

## Table of Contents

- [Authentication](#authentication)
- [Bills](#bills)
- [Categories](#categories)
- [Transactions](#transactions)
- [Teams](#teams)
- [Notes](#notes)
- [Users](#users)
- [Webhooks](#webhooks)
- [Error Handling](#error-handling)

---

## Authentication

All API endpoints (except login and register) require authentication using Bearer tokens via Laravel Sanctum.

### Authentication Levels

- **Public Routes:** `/auth/login`, `/auth/register`
- **Authenticated Routes:** Require valid Bearer token (user must be logged in)
- **Team-Scoped Routes:** Require valid Bearer token + active team context (set via `/auth/user` or `/teams/{team}/switch`)

### Headers

Include the following header in all authenticated requests:

```
Authorization: Bearer {your_token_here}
Content-Type: application/json
Accept: application/json
```

### POST /auth/login

Authenticate user and receive access token.

**Request:**
```json
{
  "email": "user@example.com",
  "password": "password123",
  "device_name": "mobile-app" // optional
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "active_team_id": 1
    },
    "token": "1|abcdefgh...",
    "token_type": "Bearer"
  }
}
```

### POST /auth/register

Register a new user account.

**Request:**
```json
{
  "name": "John Doe",
  "email": "user@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "device_name": "mobile-app" // optional
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com"
    },
    "token": "1|abcdefgh...",
    "token_type": "Bearer"
  }
}
```

### POST /auth/logout

Revoke the current access token.

**Response (200):**
```json
{
  "success": true,
  "message": "Logout successful"
}
```

### GET /auth/user

Get authenticated user's profile.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "active_team_id": 1,
    "active_team": {
      "id": 1,
      "name": "My Team"
    },
    "created_at": "2026-01-01T00:00:00.000Z"
  }
}
```

### PUT /auth/user

Update authenticated user's profile.

**Request:**
```json
{
  "name": "Updated Name", // optional
  "email": "newemail@example.com", // optional
  "password": "newpassword123", // optional
  "password_confirmation": "newpassword123" // required if password provided
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "data": {
    "id": 1,
    "name": "Updated Name",
    "email": "newemail@example.com"
  }
}
```

---

## Bills

Manage subscription bills and recurring payments.

### GET /bills

List all bills with optional filtering, sorting, and pagination.

**Query Parameters:**
- `search` - Search in title/description (or `column:value` format)
- `status` - Filter by status: `paid`, `unpaid`, `pending`, `cancelled`, `upcoming`
- `category_id` - Filter by category ID
- `is_recurring` - Filter by recurring status: `true` or `false`
- `tags` - Filter by tags (array or single value)
- `sort_by` - Column to sort by (default: `due_date`)
- `sort_direction` - `asc` or `desc` (default: `asc`)
- `per_page` - Results per page (max: 100, default: 15)

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Netflix Subscription",
      "slug": "netflix-subscription",
      "description": "Monthly streaming service",
      "amount": 15.99,
      "due_date": "2026-03-01T00:00:00.000Z",
      "status": "unpaid",
      "is_recurring": true,
      "recurrence_period": "monthly",
      "payment_url": "https://netflix.com/billing",
      "tags": ["entertainment", "subscription"],
      "category": {
        "id": 1,
        "name": "Entertainment"
      }
    }
  ],
  "links": {
    "first": "http://localhost/api/v1/bills?page=1",
    "last": "http://localhost/api/v1/bills?page=5",
    "prev": null,
    "next": "http://localhost/api/v1/bills?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 15,
    "to": 15,
    "total": 67
  }
}
```

### POST /bills

Create a new bill.

**Request:**
```json
{
  "title": "Netflix Subscription",
  "amount": 15.99,
  "due_date": "2026-03-01",
  "description": "Monthly streaming service", // optional
  "category_id": 1, // optional
  "is_recurring": true, // optional
  "recurrence_period": "monthly", // required if is_recurring: daily, weekly, monthly, yearly
  "payment_url": "https://netflix.com/billing", // optional
  "tags": ["entertainment", "subscription"], // optional
  "has_trial": false, // optional
  "trial_start_date": "2026-02-01", // optional
  "trial_end_date": "2026-02-28" // optional, must be after trial_start_date
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Bill created successfully",
  "data": {
    "id": 1,
    "title": "Netflix Subscription",
    "amount": 15.99,
    "due_date": "2026-03-01T00:00:00.000Z"
  }
}
```

### GET /bills/{bill}

Get a single bill by ID or slug.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Netflix Subscription",
    "amount": 15.99,
    "transactions": [],
    "notes": []
  }
}
```

### PUT /bills/{bill}

Update an existing bill.

**Request:**
```json
{
  "title": "Netflix Premium", // optional
  "amount": 19.99, // optional
  "status": "paid" // optional: paid, unpaid, pending, cancelled
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Bill updated successfully",
  "data": {
    "id": 1,
    "title": "Netflix Premium",
    "amount": 19.99
  }
}
```

### DELETE /bills/{bill}

Delete a bill.

**Response (200):**
```json
{
  "success": true,
  "message": "Bill deleted successfully"
}
```

### PATCH /bills/{bill}/pay

Mark a bill as paid.

**Response (200):**
```json
{
  "success": true,
  "message": "Bill marked as paid",
  "data": {
    "id": 1,
    "status": "paid"
  }
}
```

### GET /bills/{bill}/upcoming

Get upcoming bills (next 7 days by default).

**Query Parameters:**
- `days` - Number of days to look ahead (default: 7)

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Netflix Subscription",
      "due_date": "2026-02-10T00:00:00.000Z"
    }
  ]
}
```

---

## Categories

Organize bills into categories.

### GET /categories

List all categories.

**Query Parameters:**
- `search` - Search in name/description
- `user_id` - Filter by user ID
- `team_id` - Filter by team ID
- `with_bills_count` - Include bills count
- `sort_by` - Column to sort by
- `sort_direction` - `asc` or `desc`
- `per_page` - Results per page (max: 100)

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Entertainment",
      "description": "Streaming and entertainment services",
      "icon": "🎬",
      "color": "#FF5733"
    }
  ]
}
```

### POST /categories

Create a new category.

**Request:**
```json
{
  "name": "Entertainment",
  "description": "Streaming services", // optional
  "icon": "🎬", // optional
  "color": "#FF5733" // optional
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Category created successfully",
  "data": {
    "id": 1,
    "name": "Entertainment"
  }
}
```

### GET /categories/{category}

Get a single category by ID.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Entertainment",
    "description": "Streaming and entertainment services",
    "icon": "🎬",
    "color": "#FF5733"
  }
}
```

### PUT /categories/{category}

Update a category.

**Request:**
```json
{
  "name": "Updated Name", // optional
  "description": "Updated description", // optional
  "icon": "🎬", // optional
  "color": "#FF5733" // optional
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Category updated successfully",
  "data": {
    "id": 1,
    "name": "Updated Name"
  }
}
```

### DELETE /categories/{category}

Delete a category (only if no bills are associated).

**Response (200):**
```json
{
  "success": true,
  "message": "Category deleted successfully"
}
```

---

## Transactions

Track payments for bills.

### GET /transactions

List all transactions.

**Query Parameters:**
- `search` - Search in notes/payment_method
- `bill_id` - Filter by bill ID
- `payment_method` - Filter by payment method
- `date_from` - Filter from date (YYYY-MM-DD)
- `date_to` - Filter to date (YYYY-MM-DD)
- `min_amount` - Filter minimum amount
- `max_amount` - Filter maximum amount
- `sort_by` - Column to sort by
- `sort_direction` - `asc` or `desc`
- `per_page` - Results per page (max: 100)

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "tnx_id": "TXN-001",
      "amount": 15.99,
      "payment_date": "2026-02-01T00:00:00.000Z",
      "payment_method": "credit_card",
      "payment_method_name": "Credit Card",
      "attachment_link": "https://...",
      "notes": "Paid via auto-billing",
      "bill": {
        "id": 1,
        "title": "Netflix Subscription"
      }
    }
  ]
}
```

### POST /transactions

Create a new transaction.

**Request:**
```json
{
  "bill_id": 1,
  "amount": 15.99,
  "payment_date": "2026-02-01",
  "payment_method": "credit_card", // optional
  "notes": "Paid via auto-billing", // optional
  "attachment": "base64_encoded_file" // optional
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Transaction created successfully",
  "data": {
    "id": 1,
    "tnx_id": "TXN-001",
    "amount": 15.99,
    "payment_date": "2026-02-01T00:00:00.000Z",
    "bill_id": 1
  }
}
```

### GET /transactions/{transaction}

Get a single transaction by ID.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "tnx_id": "TXN-001",
    "amount": 15.99,
    "payment_date": "2026-02-01T00:00:00.000Z",
    "payment_method": "credit_card",
    "payment_method_name": "Credit Card",
    "attachment_link": "https://...",
    "notes": "Paid via auto-billing",
    "bill": {
      "id": 1,
      "title": "Netflix Subscription"
    }
  }
}
```

### PUT /transactions/{transaction}

Update a transaction.

**Request:**
```json
{
  "amount": 19.99, // optional
  "payment_date": "2026-02-01", // optional
  "payment_method": "credit_card", // optional
  "notes": "Updated notes" // optional
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Transaction updated successfully",
  "data": {
    "id": 1,
    "tnx_id": "TXN-001",
    "amount": 19.99
  }
}
```

### DELETE /transactions/{transaction}

Delete a transaction.

**Response (200):**
```json
{
  "success": true,
  "message": "Transaction deleted successfully"
}
```

### GET /transactions/{transaction}/receipt

Get formatted receipt details for a transaction.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "tnx_id": "TXN-001",
    "amount": 15.99,
    "payment_date": "2026-02-01T00:00:00.000Z",
    "bill_title": "Netflix Subscription",
    "category": "Entertainment"
  }
}
```

---

## Teams

Manage team workspaces for collaborative bill management.

### GET /teams

List all teams the user belongs to.

**Query Parameters:**
- `search` - Search in name/description/slug
- `status` - Filter by status
- `user_id` - Filter by user ID
- `sort_by` - Column to sort by
- `per_page` - Results per page

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Family Bills",
      "slug": "family-bills",
      "description": "Shared family expenses",
      "currency": "USD",
      "currency_symbol": "$",
      "icon_url": "https://...",
      "owner": {
        "id": 1,
        "name": "John Doe"
      },
      "members": []
    }
  ]
}
```

### POST /teams

Create a new team.

**Request:**
```json
{
  "name": "Family Bills",
  "description": "Shared family expenses", // optional
  "currency": "USD", // optional
  "currency_symbol": "$", // optional
  "icon": "base64_encoded_image" // optional
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Team created successfully",
  "data": {
    "id": 1,
    "name": "Family Bills",
    "slug": "family-bills",
    "currency": "USD"
  }
}
```

### GET /teams/{team}

Get a single team by ID or slug.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Family Bills",
    "slug": "family-bills",
    "description": "Shared family expenses",
    "currency": "USD",
    "currency_symbol": "$",
    "icon_url": "https://...",
    "owner": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "members": [
      {
        "id": 2,
        "name": "Jane Doe",
        "email": "jane@example.com"
      }
    ]
  }
}
```

### PUT /teams/{team}

Update a team.

**Request:**
```json
{
  "name": "Updated Team Name", // optional
  "description": "Updated description", // optional
  "currency": "EUR", // optional
  "currency_symbol": "€" // optional
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Team updated successfully",
  "data": {
    "id": 1,
    "name": "Updated Team Name",
    "currency": "EUR"
  }
}
```

### DELETE /teams/{team}

Delete a team (only owner can delete).

**Response (200):**
```json
{
  "success": true,
  "message": "Team deleted successfully"
}
```

### POST /teams/{team}/members

Add a member to the team.

**Request:**
```json
{
  "user_id": 2
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Member added successfully",
  "data": {
    "id": 2,
    "name": "Jane Doe",
    "email": "jane@example.com"
  }
}
```

### DELETE /teams/{team}/members/{user}

Remove a member from the team.

**Response (200):**
```json
{
  "success": true,
  "message": "Member removed successfully"
}
```

### POST /teams/{team}/switch

Switch the authenticated user's active team.

**Response (200):**
```json
{
  "success": true,
  "message": "Active team switched successfully"
}
```

---

## Notes

Add notes to bills and transactions.

### GET /notes

List all notes.

**Query Parameters:**
- `search` - Search in title/content
- `is_pinned` - Filter by pinned status
- `team_notes_only` - Show only team notes
- `personal_notes_only` - Show only personal notes
- `sort_by` - Column to sort by
- `per_page` - Results per page

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Payment Reminder",
      "content": "Remember to update payment method",
      "is_pinned": true,
      "was_recently_created": false
    }
  ]
}
```

### POST /notes

Create a new note.

**Request:**
```json
{
  "title": "Payment Reminder",
  "content": "Remember to update payment method",
  "is_pinned": false, // optional
  "team_id": 1 // optional, null for personal note
}
```

### GET /notes/{note}

Get a single note.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Payment Reminder",
    "content": "Remember to update payment method",
    "is_pinned": true,
    "team_id": null,
    "created_at": "2026-02-01T00:00:00.000Z"
  }
}
```

### PUT /notes/{note}

Update a note.

**Request:**
```json
{
  "title": "Updated Title", // optional
  "content": "Updated content", // optional
  "is_pinned": true // optional
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Note updated successfully",
  "data": {
    "id": 1,
    "title": "Updated Title",
    "content": "Updated content"
  }
}
```

### DELETE /notes/{note}

Delete a note.

**Response (200):**
```json
{
  "success": true,
  "message": "Note deleted successfully"
}
```

---

## Webhooks

Manage webhooks for real-time event notifications. All webhook operations require an active team context.

### GET /webhooks/events

Get available webhook events that can be subscribed to.

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "name": "bill.created",
      "description": "Triggered when a new bill is created"
    },
    {
      "name": "bill.updated",
      "description": "Triggered when a bill is updated"
    },
    {
      "name": "bill.paid",
      "description": "Triggered when a bill is marked as paid"
    },
    {
      "name": "bill.deleted",
      "description": "Triggered when a bill is deleted"
    },
    {
      "name": "transaction.created",
      "description": "Triggered when a new transaction is created"
    },
    {
      "name": "transaction.updated",
      "description": "Triggered when a transaction is updated"
    },
    {
      "name": "transaction.deleted",
      "description": "Triggered when a transaction is deleted"
    }
  ]
}
```

### GET /webhooks

List all webhooks for the active team.

**Query Parameters:**
- `search` - Search in name/url
- `status` - Filter by status: `active`, `inactive`, `failed`
- `event` - Filter by specific event
- `sort_by` - Column to sort by
- `sort_direction` - `asc` or `desc`
- `per_page` - Results per page (max: 100)

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Bill Notifications",
      "url": "https://example.com/webhooks/bills",
      "events": ["bill.created", "bill.paid"],
      "status": "active",
      "retry_count": 0,
      "created_at": "2026-02-01T00:00:00.000Z"
    }
  ],
  "links": { /* ... */ },
  "meta": { /* ... */ }
}
```

### POST /webhooks

Create a new webhook.

**Request:**
```json
{
  "name": "Bill Notifications",
  "url": "https://example.com/webhooks/bills",
  "events": ["bill.created", "bill.paid"],
  "active": true // optional, default: true
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Webhook created successfully",
  "data": {
    "id": 1,
    "name": "Bill Notifications",
    "url": "https://example.com/webhooks/bills",
    "events": ["bill.created", "bill.paid"],
    "status": "active"
  }
}
```

### GET /webhooks/{webhook}

Get a single webhook by ID.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Bill Notifications",
    "url": "https://example.com/webhooks/bills",
    "events": ["bill.created", "bill.paid"],
    "status": "active",
    "retry_count": 0,
    "last_triggered_at": "2026-02-05T10:30:00.000Z",
    "created_at": "2026-02-01T00:00:00.000Z",
    "updated_at": "2026-02-01T00:00:00.000Z"
  }
}
```

### PUT /webhooks/{webhook}

Update a webhook.

**Request:**
```json
{
  "name": "Updated Webhook Name", // optional
  "url": "https://example.com/webhooks/updated", // optional
  "events": ["bill.created", "bill.updated", "bill.paid"], // optional
  "active": true // optional
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Webhook updated successfully",
  "data": {
    "id": 1,
    "name": "Updated Webhook Name",
    "url": "https://example.com/webhooks/updated"
  }
}
```

### DELETE /webhooks/{webhook}

Delete a webhook.

**Response (200):**
```json
{
  "success": true,
  "message": "Webhook deleted successfully"
}
```

### GET /webhooks/{webhook}/deliveries

Get delivery history for a webhook.

**Query Parameters:**
- `status` - Filter by delivery status: `pending`, `delivered`, `failed`
- `event` - Filter by specific event
- `date_from` - Filter from date (YYYY-MM-DD)
- `date_to` - Filter to date (YYYY-MM-DD)
- `sort_direction` - `asc` or `desc` (default: `desc`)
- `per_page` - Results per page (max: 100)

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "webhook_id": 1,
      "event": "bill.created",
      "status": "delivered",
      "payload": { /* event data */ },
      "response_status_code": 200,
      "response_body": "OK",
      "attempts": 1,
      "next_retry_at": null,
      "delivered_at": "2026-02-05T10:30:00.000Z",
      "created_at": "2026-02-05T10:30:00.000Z"
    }
  ],
  "links": { /* ... */ },
  "meta": { /* ... */ }
}
```

### POST /webhooks/{webhook}/deliveries/{delivery}/retry

Retry a failed webhook delivery.

**Response (200):**
```json
{
  "success": true,
  "message": "Delivery retry queued successfully",
  "data": {
    "id": 1,
    "status": "pending",
    "next_retry_at": "2026-02-05T10:35:00.000Z"
  }
}
```

---

## Users

User management endpoints.

### GET /users

List all users (with appropriate permissions).

**Query Parameters:**
- `search` - Search in name/email
- `active_team_id` - Filter by active team
- `sort_by` - Column to sort by
- `per_page` - Results per page

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "active_team_id": 1
    }
  ]
}
```

### GET /users/{user}

Get a single user.

---

## Error Handling

### Standard Error Response

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

### HTTP Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized (missing or invalid token)
- `403` - Forbidden (insufficient permissions)
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

### Common Error Responses

**401 Unauthorized:**
```json
{
  "message": "Unauthenticated."
}
```

**403 Forbidden:**
```json
{
  "message": "This action is unauthorized."
}
```

**422 Validation Error:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "amount": ["The amount must be at least 0."]
  }
}
```

---

## Rate Limiting

API requests are rate-limited to prevent abuse. Default limits:
- **Authenticated requests:** 60 requests per minute
- **Guest requests:** 10 requests per minute

Rate limit headers are included in responses:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1609459200
```

---

## Pagination

All list endpoints support pagination using Laravel's standard pagination format:

```json
{
  "data": [...],
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 15,
    "to": 15,
    "total": 67
  }
}
```

---

## Best Practices

1. **Always include the `Accept: application/json` header** to ensure JSON responses
2. **Store tokens securely** - never expose them in client-side code
3. **Use HTTPS** in production for secure communication
4. **Handle rate limiting** - implement exponential backoff when rate limited
5. **Validate data** before sending to reduce round trips
6. **Use filtering and pagination** to minimize response sizes
7. **Cache responses** where appropriate to reduce API calls

---

## Support

For API support, please contact: support@bills.msar.me

**Last Updated:** April 22, 2026  
**API Version:** 1.0
