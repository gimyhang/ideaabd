# Repository guidance

## Build, test, and lint

Run these from the repository root:

| Task | Command |
| --- | --- |
| Run the full PHPUnit suite | `composer test` |
| Run one test file | `php artisan test tests/Feature/AuthFlowTest.php` |
| Run one test method | `php artisan test tests/Feature/AuthFlowTest.php --filter=test_full_lifecycle` |
| Check PHP formatting | `vendor/bin/pint --test` |
| Build frontend assets | `npm run build` |
| Start the Vite development server | `npm run dev` |

`composer test` clears the Laravel config cache before running `php artisan test`. PHPUnit tests use SQLite `:memory:`; `tests/TestCase.php` refuses to run them if the active connection is pointed elsewhere. There is no separate npm lint script.

## Architecture

- This is a Laravel 12 application. Shared application code lives in `app/`; application routes are defined in `routes/web.php` and `routes/console.php`.
- Feature domains live in `Modules/<Name>/`, with their own controllers, models, services, routes, views, and (where present) migrations. Composer maps `Modules\\` to `Modules/`.
- `App\Providers\ModularServiceProvider` discovers immediate module directories and automatically loads each module's `Routes/web.php`, `Routes/api.php`, `Resources/views`, and `Database/Migrations`. Module API routes receive the `api` middleware and `/api` prefix; module web routes receive `web` middleware. Module views are registered under the lowercase module name (for example, `book::...`).
- Providers that need custom boot or registration logic are explicitly listed in `bootstrap/providers.php`. Global middleware, middleware aliases, and exception handling are configured in `bootstrap/app.php`.
- Core Blade views are in `resources/views`; module views stay with their module. Vite builds the `resources/css/app.css` and `resources/js/app.js` entry points.

## Repository conventions

- Keep feature-specific code inside its existing `Modules/<Name>/` domain when that domain owns the behavior; use `app/` for shared application functionality.
- Add module routes, views, and migrations in the exact paths consumed by `ModularServiceProvider`. Use the lowercase module name when referencing its namespaced views.
- The request locale is managed by `SetLocale` and currently supports Bengali (`bn`) and English (`en`). Shared Blade directives for Bengali number/date and taka formatting are registered in `AppServiceProvider`: `@bn`, `@bnDate`, `@taka`, `@takaS`, `@takaInWords`, and `@takaInWordsEn`.
- Feature and unit tests live in `tests/Feature` and `tests/Unit`. Tests extending `Tests\TestCase` must use the in-memory SQLite database configured in `phpunit.xml`; tests commonly use Laravel's `RefreshDatabase` trait.
