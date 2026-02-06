# Bill Organizer - Developer Documentation

## Overview

Bill Organizer is a modern web application built with Laravel 12 and Vue.js that helps users manage bills, subscriptions, and financial obligations efficiently. The application features a multi-tenant architecture with team-based collaboration, recurring bill management, and comprehensive notification systems.

## Tech Stack

### Backend
- **Framework**: Laravel 12 (PHP 8.3+)
- **Database**: SQLite (default), MySQL supported
- **Queue System**: Database-based queues
- **Mail**: Configurable mail drivers
- **Authentication**: Laravel's built-in authentication with email verification
- **API**: Inertia.js for seamless SPA experience

### Frontend
- **Framework**: Vue.js 3 with TypeScript
- **Build Tool**: Vite
- **UI Framework**: Tailwind CSS 4
- **Components**: Reka UI, Lucide icons
- **Charts**: ApexCharts
- **Forms**: VeeValidate with Zod validation
- **State Management**: Vue 3 Composition API

### Development Tools
- **Testing**: Pest PHP for backend testing
- **Code Quality**: Laravel Pint, ESLint, Prettier
- **Package Manager**: Yarn for frontend, Composer for backend
- **Containerization**: Docker with Laravel Sail

## Architecture

### Multi-Tenant Design

The application uses a team-based multi-tenancy approach:

- **Teams**: Central organizational unit
- **Users**: Can belong to multiple teams
- **Active Team**: Each user has one active team at a time
- **Data Isolation**: Global scopes ensure team data separation

### Key Models

#### User Model
```php
// Core user functionality with team management
- belongsToMany(Team::class) // Multiple team membership
- belongsTo(Team::class, 'active_team_id') // Current active team
- hasMany(Bill::class) // User's bills
- hasMany(Category::class) // User's categories
```

#### Team Model
```php
// Team management with ownership
- belongsTo(User::class, 'user_id') // Team owner
- belongsToMany(User::class) // Team members
- Global scope for data isolation
```

#### Bill Model
```php
// Core bill management with advanced features
- belongsTo(User::class) // Bill owner
- belongsTo(Category::class) // Bill category
- hasMany(Transaction::class) // Payment transactions
- morphToMany(Note::class) // Associated notes
- Recurring bill support
- Trial period management
- Smart status calculation
```

### Database Schema

Key tables and relationships:
- `users` - User accounts and preferences
- `teams` - Team information and settings
- `team_user` - Many-to-many relationship between users and teams
- `bills` - Bill information with recurrence and trial support
- `categories` - Bill categorization
- `transactions` - Payment records
- `notes` - Flexible note system with polymorphic relationships
- `meta_data` - Key-value storage for flexible data

## Features

### Core Functionality

#### Bill Management
- Create, edit, and delete bills
- Recurring bills (weekly, monthly, yearly)
- Trial period support
- Payment tracking
- Due date management
- Tag-based organization

#### Team Collaboration
- Multi-user teams
- Team switching
- Member invitation system
- Role-based permissions (owner vs member)
- Team-specific currencies

#### Notification System
- Email and web notifications
- Configurable reminder periods
- Trial end notifications
- Smart notification tracking to prevent duplicates

#### Categories & Organization
- Custom bill categories
- Tag-based filtering
- Search functionality
- Dashboard analytics

### Advanced Features

#### Smart Bill Status
Bills automatically calculate their status based on:
- Payment transactions
- Recurrence periods
- Current date context

#### Flexible Metadata System
Uses a polymorphic metadata system for:
- User preferences
- Notification tracking
- Feature flags
- Custom settings

#### Notes System
Polymorphic note system that can attach to any model:
- Bill-specific notes
- Rich text support
- Team-shared notes

## Development Setup

### Prerequisites
- PHP 8.3+
- Composer
- Node.js 18+ with Yarn
- SQLite or MySQL
- Docker (optional)

### Local Development

1. **Clone and Install**
```bash
git clone https://github.com/4msar/bill-organizer.git
cd bill-organizer
composer install
yarn install
```

2. **Environment Setup**
```bash
cp .env.example .env
php artisan key:generate
```

3. **Database Setup**
```bash
php artisan migrate
php artisan db:seed
```

4. **Development Server**
```bash
# Option 1: Individual commands
php artisan serve
yarn dev

# Option 2: Concurrent development (recommended)
composer run dev
```

### Docker Development

```bash
# Start containers
./vendor/bin/sail up -d

# Run migrations
./vendor/bin/sail artisan migrate

# Seed database
./vendor/bin/sail artisan db:seed
```

## Code Organization

### Backend Structure

```
app/
├── Enums/           # Application enums (Status, etc.)
├── Helpers/         # Helper functions and utilities
├── Http/
│   ├── Controllers/ # Request handling
│   └── Middleware/  # Custom middleware
├── Jobs/            # Queue jobs
├── Mail/            # Mail classes
├── Models/          # Eloquent models
│   ├── Pivots/      # Custom pivot models
│   └── Scopes/      # Global scopes
├── Notifications/   # Notification classes
├── Providers/       # Service providers
└── Traits/          # Reusable traits
```

### Frontend Structure

```
resources/js/
├── Components/      # Vue components
├── Layouts/         # Page layouts
├── Pages/           # Inertia pages
├── Types/           # TypeScript definitions
└── Utils/           # Utility functions
```

### Key Traits

#### HasTeam Trait
Provides team-scoped functionality:
```php
trait HasTeam
{
    protected static function bootHasTeam()
    {
        static::creating(function ($model) {
            $model->team_id = Team::current()->id;
        });
    }
}
```

#### HasMetaData Trait
Flexible key-value storage:
```php
trait HasMetaData
{
    public function setMeta(string $key, $value): void
    public function getMeta(string $key, $default = null)
    public function deleteMeta(string $key): void
}
```

## API Routes

### Authentication Routes
- `GET /` - Welcome page
- `POST /register` - User registration
- `POST /login` - User login
- `POST /logout` - User logout

### Team Management
- `GET /team/create` - Create team form
- `POST /team/store` - Store new team
- `GET /team/switch/{team}` - Switch active team
- `GET /team/settings` - Team settings
- `PUT /team/settings` - Update team
- `DELETE /team/delete` - Delete team
- `POST /team/member` - Add team member
- `DELETE /team/member/{user}` - Remove team member

### Bill Management
- `GET /bills` - List bills
- `GET /bills/create` - Create bill form
- `POST /bills` - Store new bill
- `GET /bills/{bill}` - Show bill details
- `GET /bills/{bill}/edit` - Edit bill form
- `PUT /bills/{bill}` - Update bill
- `DELETE /bills/{bill}` - Delete bill
- `PATCH /bills/{bill}/pay` - Mark bill as paid

### Additional Routes
- Categories CRUD
- Transactions management
- Notifications handling
- Notes management (feature-flagged)

## Testing

### Running Tests
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

### Test Structure
```
tests/
├── Feature/         # Integration tests
├── Unit/            # Unit tests
└── TestCase.php     # Base test class
```

## Deployment

### Production Setup

1. **Environment Configuration**
```bash
# Set production environment
APP_ENV=production
APP_DEBUG=false

# Configure database
DB_CONNECTION=mysql
DB_HOST=your-host
DB_DATABASE=your-database

# Configure mail
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
```

2. **Optimization**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

3. **Queue Workers**
```bash
# Start queue worker
php artisan queue:work

# Or use supervisor for production
```

4. **Scheduler**
```bash
# Add to crontab
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### Docker Production

```bash
# Build production image
docker build -t bill-organizer .

# Run with docker-compose
docker-compose -f docker-compose.prod.yml up -d
```

## Configuration

### Environment Variables

Key configuration options:

```env
# Application
APP_NAME="Bill Organizer"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite

# Mail
MAIL_MAILER=log
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Queue
QUEUE_CONNECTION=database

# Docker Ports (for development)
APP_PORT=8500
VITE_PORT=8551
FORWARD_DB_PORT=8536
```

### Feature Flags

The application supports feature flags through user metadata:
- `enable_notes` - Enable/disable notes functionality
- `enable_calendar` - Enable/disable calendar view
- `email_notification` - Email notification preferences
- `web_notification` - Web notification preferences

## Security Considerations

### Multi-Tenancy Security
- Global scopes ensure data isolation
- Team-based access control
- Owner vs member permissions

### Input Validation
- Form request validation
- Database constraints
- XSS protection via Laravel's built-in escaping

### Authentication
- Email verification required
- Password hashing with bcrypt
- Session-based authentication

## Performance Optimization

### Database
- Eager loading relationships
- Proper indexing on frequently queried fields
- Query optimization for large datasets

### Caching
- Route caching in production
- View caching
- Configuration caching

### Frontend
- Vite for optimized builds
- Code splitting
- Asset optimization

## Troubleshooting

### Common Issues

1. **Permission Errors**
```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
```

2. **Database Issues**
```bash
# Reset database
php artisan migrate:fresh --seed
```

3. **Cache Issues**
```bash
# Clear all caches
php artisan optimize:clear
```

4. **Queue Issues**
```bash
# Restart queue workers
php artisan queue:restart
```

### Development Tips

1. **Use Laravel Pail for real-time logs**
```bash
php artisan pail
```

2. **Use the concurrent development script**
```bash
composer run dev
```

3. **Enable query logging for debugging**
```php
DB::enableQueryLog();
// Your code here
dd(DB::getQueryLog());
```

## Contributing

### Code Style
- Follow PSR-12 for PHP
- Use Laravel Pint for formatting
- Follow Vue.js style guide for frontend
- Use TypeScript for type safety

### Pull Request Process
1. Fork the repository
2. Create a feature branch
3. Write tests for new functionality
4. Ensure all tests pass
5. Submit pull request with clear description

### Development Workflow
```bash
# Format code
composer run lint

# Run tests
php artisan test

# Check frontend
yarn lint
yarn format:check
```

## License

This project is open-source and available under the GNU General Public License.