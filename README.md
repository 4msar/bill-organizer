<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Bill Manager App

An open-source application to manage bill effectively. Built using the latest Laravel framework and Vue.js, it provides a seamless and modern user experience.

---

## Features

- Manage bill and subscriptions efficiently.
- Modern front-end built with Vue.js.
- Backend powered by Laravel 12.
- Docker support for easy deployment.
- Yarn as the package manager for front-end dependencies.

---

## Prerequisites

Make sure you have the following installed on your system:

- PHP 8.3 or the latest version
- Composer
- Node.js with Yarn
- MySQL or other supported databases
- Docker (optional, for containerized setup)

---

## Follow the steps to run the app:

1. Clone the repository:
   ```bash
   git clone https://github.com/4msar/bill-organizer.git
   cd bill-organizer
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Copy the `.env.example` file to `.env` and update the database credentials:
   ```bash
   cp .env.example .env
   ```

4. Set up the subdomain configuration as required.

5. Generate the application key:
   ```bash
   php artisan key:generate
   ```

6. Run the database migrations:
   ```bash
   php artisan migrate
   ```

7. Seed the database:
   ```bash
   php artisan db:seed
   ```

8. Install Node.js dependencies using Yarn:
   ```bash
   yarn install
   ```

9. Build the front-end assets for development:
   ```bash
   yarn dev
   ```

10. Start the development server:
    ```bash
    php artisan serve
    ```

---

## Docker

The app can also be run using Docker. This setup uses Laravel Sail for containerization. To run the app with Docker, update your `.env` file accordingly and follow these steps:

1. Start the containers:
   ```bash
   ./vendor/bin/sail up
   ```

2. Run the database migrations:
   ```bash
   ./vendor/bin/sail artisan migrate
   ```

3. Seed the database:
   ```bash
   ./vendor/bin/sail artisan db:seed
   ```

---

## Testing

To ensure the application works as expected, you can run tests using the following command:

```bash
php artisan test
```

---

## Contributing

Contributions are welcome! Please follow these steps to contribute:

1. Fork the repository.
2. Create a new branch for your feature or bug fix.
3. Commit your changes with clear and concise messages.
4. Submit a pull request with a detailed description of your changes.

---

## License

This project is open-source and available under the [GNU GENERAL PUBLIC LICENSE](LICENSE).

---

Feel free to replace placeholders (e.g., "Trade License App") with the actual app's name if it differs. Let me know if you need further refinements!
