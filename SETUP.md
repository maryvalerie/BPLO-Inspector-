# Standalone Inspector Portal

This app provides an inspector login and workspace for scanning permit QR
codes, checking permit validity, searching issued permits, and viewing scan
history. It shares the permit database and private storage with the Applicant
and BPLO apps, so released permits and recorded scans are visible across them.

## Requirements

- PHP 8.3+ with `pdo_sqlite` (or `pdo_mysql`), `mbstring`, `fileinfo`, `gd`, and `zip`
- Composer
- Node.js 22.12+ and npm

## Setup

Run these commands from the `INSPECTOR_SYSTEM` folder:

```powershell
composer install
copy .env.example .env
php artisan key:generate
npm ci
npm run build
```

When the three folders are side by side locally, `.env.example` points this app
to the Applicant app's SQLite database and private storage. For deployments
from separate GitHub repositories or servers, configure all three apps with
the same hosted MySQL database credentials and the same mounted private-storage
directory or private S3 bucket.

Keep the inspector app's `APP_KEY`, `SESSION_COOKIE`, and `CACHE_PREFIX` unique.
`APP_URL` is the inspector portal address (default `http://localhost:8002`).
Set `PERMIT_PUBLIC_URL` to the same phone-reachable Applicant verification URL
used by the other apps, never `localhost` for QR codes scanned on a phone.

Run schema migrations and seed initial users/configuration once from the
Applicant app against the shared database. Do not seed this standalone portal.
Sign in here with an active shared user whose role is `inspector`; BPLO,
treasurer, admin, and applicant accounts cannot sign in to this portal.

## Run

```powershell
php artisan serve --port=8002
```

Open `http://localhost:8002`. Camera access requires HTTPS or localhost and
browser permission to use the camera. The scanner and image upload use a
locally bundled QR decoder, so they do not depend on native `BarcodeDetector`
support or an external CDN. You can also enter the verification code/link manually. Scans are recorded in
the shared QR verification history and appear in the Inspector portal.

Run the portal tests with `php artisan test`.

## Shared data and deployment

Released permits and QR scan history are shared with the Applicant and BPLO
apps. Back up the shared database and storage, use HTTPS, set `APP_DEBUG=false`,
and do not expose private uploads publicly. Include the generated
`public/build` assets when deploying the portal.
