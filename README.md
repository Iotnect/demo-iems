# IEMS — Integrated Environmental Monitoring System

A Laravel web application demonstrating a Phase 1 environmental monitoring workspace for PETRONAS. It brings methane, air quality, and water quality observations together with geographic context, historical baselines, anomaly reviews, and reports.

The application uses illustrative data for two Malaysian areas of interest: Coastal Industrial in Pengerang, Johor, and River Catchment in Kota Tinggi, Johor. There is no live satellite, drone, IoT, or AI ingestion.

## Features

| Workspace | Capabilities |
| --- | --- |
| Environmental Overview | Summary metrics, observation trends, geographic context, and anomaly highlights. |
| Areas of Interest | Explore the two demonstration areas and their monitoring scope. |
| Environmental Monitoring | Filter observations by area, parameter, and date range; export observations as CSV. |
| Historical Baseline | Compare observations against seasonal and annual reference values. |
| Alerts & Anomalies | Filter findings by severity and status; save review statuses and analyst notes. |
| Reports & Insights | Prepare report views and use the browser's Print / Save PDF option. |
| PoC Evaluation & Roadmap | Review the demonstration assessment and proposed future capabilities. |
| Administration | Save draft monitoring thresholds, frequency, and baseline settings for the current session. |

The interface includes responsive navigation, map layer controls, date selection, and a session-based demo login.

## Technology

- PHP 8.3 or later and Laravel 13.
- Blade templates, Tailwind CSS 4, custom CSS, and vanilla JavaScript.
- Vite 8 for frontend development and asset builds.
- SQLite by default, including database-backed sessions, cache, and queue storage.
- PHPUnit 12 for automated tests.

## Requirements

- PHP 8.3+ with the extensions required by Composer, including PDO SQLite for the default database.
- Composer 2.
- Node.js 22.12+ and npm.
- Git.

## Local installation

Clone the repository and install the locked dependencies:

```sh
git clone https://github.com/Iotnect/demo-iems.git
cd demo-iems
composer install
npm ci --ignore-scripts
```

Create the local environment file, application key, and SQLite database:

```sh
php -r "file_exists('.env') || copy('.env.example', '.env');"
php artisan key:generate --no-interaction
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate --no-interaction
```

The supplied `.env.example` selects SQLite and database-backed sessions and cache. Keep those defaults for the demo. No database seeding or external API keys are required to use the monitoring workspace.

Start the application in one terminal:

```sh
php artisan serve
```

Start the frontend development server in a second terminal:

```sh
npm run dev
```

Open the application address printed by `php artisan serve` and sign in using the demo account below. Keep both terminals running during development.

For a local preview without the Vite development server, run `npm run build`, then start `php artisan serve`.

### Laragon

Point the Laragon virtual host at the project's `public` directory and set `APP_URL` in `.env` to the configured application address. Run `npm run dev` for development or `npm run build` to generate the assets served by Laravel.

## Demo account

| Field | Value |
| --- | --- |
| Username | `admin` |
| Password | `admin123` |

This shared account is defined in `app/Http/Controllers/LoginController.php`. It provides demonstration access; the sample roles in Administration are illustrative and do not enforce permissions.

## Suggested walkthrough

1. Sign in and explore the Coastal Industrial overview.
2. Switch to River Catchment to compare a second area of interest.
3. Filter methane, air quality, or water quality observations and compare baseline references.
4. Select an October 2026 date range to view sample observations. September 2026 demonstrates the empty-data state.
5. Open Alerts & Anomalies, select a finding, and save a review status and note.
6. Export filtered observations from Environmental Monitoring or prepare a report from Reports & Insights.
7. Adjust draft settings in Administration and sign out when finished.

Reviews and draft settings belong to the current session and are cleared on sign-out. Threshold changes do not recalculate the fixed sample anomaly register.

## Deploy the demo to Vercel

The repository includes a Vercel configuration using the [community PHP runtime](https://github.com/vercel-community/php) pinned to PHP 8.3. It builds Laravel as a function and publishes a separate static bundle containing only frontend assets and map imagery.

1. Import this repository into Vercel and select the `main` branch.
2. Set **Application Preset / Framework Preset** to **Other** and keep **Root Directory** at `./`.
3. Leave build and output overrides disabled. The `builds` configuration in `vercel.json` controls both PHP and frontend builds.
4. Select **Node.js 22.x** in the project settings if prompted; this version is also declared in `package.json`.
5. Generate a dedicated application key locally:

   ```sh
   php artisan key:generate --show --no-interaction
   ```

6. Add the complete generated value, including the `base64:` prefix, as the Vercel environment variable **APP_KEY**. Enable it for Production and Preview. Keep this key stable across redeployments and out of Git.
7. Click **Deploy**. After deployment, set **APP_URL** to the assigned application address and redeploy if you need console-generated URLs.

Other demo environment settings are supplied by `vercel.json`: production mode, disabled debug output, secure cookie sessions, temporary file cache, synchronous queues, and logs sent to stderr. A database is not required for this deployment.

The Vercel entrypoint creates writable Laravel directories in temporary storage and trusts the platform's forwarded protocol headers for HTTPS links. Cookie sessions keep demo login and short reviews available across function invocations; browser cookie size limits make this suitable for small demonstration sessions. Use an external session store for larger reviews. The temporary file cache, including login throttling counters, is local to each function instance and can reset on a cold start.

The build runs frontend compilation in both builders so the PHP function has its Vite manifest and the static deployment has matching assets. PHP source files and local environment files are excluded from the static output.

## Development commands

```sh
# Build frontend assets
npm run build

# Run the automated test suite
php artisan test --compact

# Run login or dashboard feature tests
php artisan test --compact tests/Feature/LoginTest.php
php artisan test --compact tests/Feature/MonitoringDashboardTest.php

# Inspect application routes
php artisan route:list --except-vendor
```

The test configuration uses an in-memory SQLite database and array-backed sessions and cache.

## Project structure

| Location | Purpose |
| --- | --- |
| `app/Http/Controllers/MonitoringController.php` | Demo observations, filtering, anomaly reviews, settings, CSV exports, and report data. |
| `app/Http/Controllers/LoginController.php` | Shared demo login and logout. |
| `app/Http/Middleware/EnsureDemoAuthenticated.php` | Session checks for workspace access. |
| `resources/views/iems/` | Dashboard screens and report templates. |
| `resources/views/components/` | Shared interface components. |
| `resources/css/` and `resources/js/` | Application styles and browser interactions. |
| `public/images/map/` | Bundled demonstration map imagery. |
| `routes/web.php` | Workspace, login, export, and report routes. |
| `tests/Feature/` | Login and monitoring workflow coverage. |

## Troubleshooting

- **Missing Vite manifest or unstyled pages:** run `npm run build`, or keep `npm run dev` running during development.
- **Missing database or session tables:** confirm PDO SQLite is enabled, create `database/database.sqlite` using the installation command, and run `php artisan migrate --no-interaction`.
- **Missing application key:** run `php artisan key:generate --no-interaction`.
- **Environment changes are not reflected:** run `php artisan config:clear`.

Local environment files, SQLite databases, installed dependencies, and generated frontend assets are excluded from Git.

## Demonstration scope

All observations, AOI boundaries, thresholds, and findings are illustrative. Satellite source labels describe the intended monitoring context; the displayed values are generated by the application. Reports and exports contain demonstration data. Production authentication, persistent monitoring records, live ingestion, and operational integrations require further implementation.
