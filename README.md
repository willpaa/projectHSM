<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Run with Docker

The Docker setup runs the Laravel app on port `8000` and MySQL 8 in a persistent
named volume. Uploaded files and Laravel logs continue to use this project's
existing `storage` directories. Database migrations do not run automatically.

### First run

1. Install Docker Engine and the Docker Compose plugin.
2. Keep your existing `.env` file. If you do not have one, create it with
   `cp .env.example .env` and set `APP_KEY` before starting the app.
3. Add `DOCKER_DB_USERNAME=hospital`, `DOCKER_DB_PASSWORD=...`, and
   `DOCKER_DB_ROOT_PASSWORD=...` to `.env`, using private values for this local
   Docker database. The Docker username is separate from the host MySQL
   `DB_USERNAME`. Do not commit `.env`.
4. Build the app image and start the Docker database:

   ```sh
   docker compose build app
   docker compose up -d db
   ```

5. If you are moving the existing XAMPP database, follow **Bring the existing
   MySQL data into Docker** below before starting the app. For a brand-new,
   empty database only, start the app and create its schema:

   ```sh
   docker compose up -d app
   docker compose exec app php artisan migrate --force
   ```

   Then open <http://localhost:8000>.

Compose passes the existing `APP_KEY` into the app, sets `APP_URL` to the local
Docker address, and points Laravel at the `db` container. MySQL data stays
in the `hospital_mysql_data` Docker volume when containers stop or are rebuilt.
On startup, the app container adjusts ownership of the mounted Laravel storage
directories so Apache can write uploads and logs; it does not remove or rewrite
their contents.
Do not run `docker compose down -v`: that command removes the database volume.

### Bring the existing MySQL data into Docker

The first start creates a new, empty Docker database; it never edits or deletes
your current XAMPP database. To move a copy of your current `hosptalms` database:

1. Stop writes to the current app, then make a SQL backup with the MySQL tools
   used by your XAMPP installation. Save it somewhere outside this project and
   confirm that the backup file exists before continuing. For example:

   ```sh
   mysqldump --single-transaction --routines --triggers -u root hosptalms > /path/to/backup/hosptalms.sql
   ```

   Add the credentials/options required by your local MySQL setup. Keep this
   file private because hospital data can contain sensitive information.
2. Start only Docker's database, then import the backup into it:

   ```sh
   docker compose up -d db
   docker compose exec -T db sh -c 'exec mysql -u root -p"$MYSQL_ROOT_PASSWORD" hosptalms' < /path/to/backup/hosptalms.sql
   ```

   If your dump contains `CREATE DATABASE` statements, review it first and
   adjust the import command for that dump.
3. Confirm the import and make a second backup before changing the schema. Only
   after that, start the app and apply migrations if the app reports they are
   needed:

   ```sh
   docker compose up -d app
   docker compose exec app php artisan migrate --force
   ```

   Open <http://localhost:8000> after the app container is running.

   Migrations can change the schema, so keep the original XAMPP database and
   both backups until you have verified the app and records.

Useful commands:

```sh
docker compose logs -f app
docker compose stop
docker compose start
```

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
