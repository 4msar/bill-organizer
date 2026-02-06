# API Setup Guide

This guide will help you set up and use the Bill Organizer API.

## Quick Start

### 1. Environment Configuration

Add the following to your `.env` file:

```env
# Sanctum Configuration
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1
SANCTUM_TOKEN_PREFIX=

# CORS Configuration  
CORS_ALLOWED_ORIGINS=http://localhost:3000,http://localhost:8000

# API Rate Limiting (requests per minute)
API_RATE_LIMIT=60
```

### 2. Run Migrations

Ensure your database is up to date:

```bash
php artisan migrate
```

### 3. Test the API

Start the development server:

```bash
php artisan serve
```

Test the API health endpoint:

```bash
curl http://localhost:8000/api/v1/auth/login
```

## Authentication Flow

### 1. Register a New User

```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### 2. Login

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123",
    "device_name": "api-client"
  }'
```

Save the returned token for subsequent requests.

### 3. Make Authenticated Requests

Use the token in the Authorization header:

```bash
curl http://localhost:8000/api/v1/bills \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

## API Endpoints

See [docs/api.md](api.md) for complete API documentation.

### Available Resources

- **Authentication** - `/api/v1/auth/*`
- **Bills** - `/api/v1/bills`
- **Categories** - `/api/v1/categories`
- **Transactions** - `/api/v1/transactions`
- **Teams** - `/api/v1/teams`
- **Notes** - `/api/v1/notes`
- **Users** - `/api/v1/users`

## Testing

Run the API tests:

```bash
# Run all API tests
php artisan test tests/Feature/Api

# Run specific test file
php artisan test tests/Feature/Api/V1/AuthControllerTest.php

# Run with coverage
php artisan test --coverage
```

## Postman Collection

Import the Postman collection from `docs/postman_collection.json` to quickly test all endpoints.

### Using Postman Collection

1. Open Postman
2. Click "Import" button
3. Select `docs/postman_collection.json`
4. Set the `{{base_url}}` variable to `http://localhost:8000`
5. Register/Login to get a token
6. Set the `{{token}}` variable with your auth token
7. Start making requests!

## Common Use Cases

### Example 1: Create a Bill with Category

```bash
# 1. Create a category
curl -X POST http://localhost:8000/api/v1/categories \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Entertainment",
    "icon": "🎬",
    "color": "#FF5733"
  }'

# 2. Create a bill
curl -X POST http://localhost:8000/api/v1/bills \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Netflix Subscription",
    "amount": 15.99,
    "due_date": "2026-03-01",
    "category_id": 1,
    "is_recurring": true,
    "recurrence_period": "monthly",
    "tags": ["entertainment", "subscription"]
  }'
```

### Example 2: Record a Payment

```bash
curl -X POST http://localhost:8000/api/v1/transactions \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "bill_id": 1,
    "amount": 15.99,
    "payment_date": "2026-02-15",
    "payment_method": "credit_card",
    "notes": "Automatic payment"
  }'
```

### Example 3: Get Upcoming Bills

```bash
curl "http://localhost:8000/api/v1/bills?status=upcoming&days=7" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Example 4: Search and Filter

```bash
# Search bills by title
curl "http://localhost:8000/api/v1/bills?search=Netflix" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Filter by category and status
curl "http://localhost:8000/api/v1/bills?category_id=1&status=unpaid" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Advanced search with column:value
curl "http://localhost:8000/api/v1/bills?search=title:Netflix" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Security Best Practices

### 1. Token Management

- **Never expose tokens** in client-side code or version control
- **Rotate tokens regularly** for security
- **Use different tokens** for different devices/apps
- **Revoke tokens** immediately when compromised

### 2. CORS Configuration

In production, set specific allowed origins in `.env`:

```env
CORS_ALLOWED_ORIGINS=https://yourdomain.com,https://app.yourdomain.com
```

### 3. Rate Limiting

Default limits apply per user:
- 60 requests/minute for authenticated users
- 10 requests/minute for guests

Adjust in `.env`:

```env
API_RATE_LIMIT=100
```

### 4. HTTPS Only

Always use HTTPS in production. Update `.env`:

```env
FORCE_HTTPS=true
SESSION_SECURE_COOKIE=true
```

### 5. Token Expiration

Tokens don't expire by default. Set expiration in `config/sanctum.php`:

```php
'expiration' => 60 * 24 * 7, // 7 days in minutes
```

## Troubleshooting

### Issue: 401 Unauthorized

**Solution:**
- Verify token is included in `Authorization: Bearer YOUR_TOKEN` header
- Check token hasn't been revoked
- Ensure user hasn't been deleted

### Issue: 403 Forbidden - Team Required

**Solution:**
- User must belong to at least one team
- Switch to an active team using `/api/v1/teams/{team}/switch`

### Issue: 422 Validation Error

**Solution:**
- Check request payload matches required fields
- Refer to API documentation for field requirements

### Issue: 429 Too Many Requests

**Solution:**
- You've hit the rate limit
- Wait for the reset time indicated in `X-RateLimit-Reset` header
- Implement exponential backoff in your client

### Issue: CORS Errors

**Solution:**
- Add your frontend URL to `CORS_ALLOWED_ORIGINS` in `.env`
- Ensure `Accept: application/json` header is included
- Check browser console for specific CORS error details

## Production Deployment

### Pre-deployment Checklist

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure specific `CORS_ALLOWED_ORIGINS`
- [ ] Enable HTTPS and secure cookies
- [ ] Set up database backups
- [ ] Configure proper rate limiting
- [ ] Set up monitoring and logging
- [ ] Review and test all API endpoints
- [ ] Generate API keys/secrets for third-party services
- [ ] Document API for external developers

### Deployment Commands

```bash
# Clear and optimize configuration
php artisan config:clear
php artisan config:cache

# Clear and optimize routes
php artisan route:clear
php artisan route:cache

# Run migrations
php artisan migrate --force

# Restart queue workers
php artisan queue:restart
```

## API Versioning

Current API version is `v1`. When making breaking changes, create a new version:

1. Create new route file: `routes/api/v2.php`
2. Create new controllers: `app/Http/Controllers/Api/V2/`
3. Update `routes/api.php` to include v2 routes
4. Document changes in `docs/api-v2.md`
5. Maintain v1 for backward compatibility

## Support

For API issues or questions:
- Email: api-support@bill-organizer.com
- Documentation: [docs/api.md](api.md)
- GitHub Issues: https://github.com/yourusername/bill-organizer/issues

## License

This API is part of the Bill Organizer project. See LICENSE file for details.
