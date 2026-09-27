# Meridian

Meridian is a production-style publishing platform built with PHP 8.2+, MySQL 8 / MariaDB, and a small amount of vanilla JavaScript. It includes a public journal, editorial admin, comments, newsletter, contact desk, media library, and SEO endpoints.

## Requirements

- PHP 8.2 or newer with `pdo_mysql`, `mbstring`, `gd`, `fileinfo`, `json`, and `iconv`
- MySQL 8+ or MariaDB 10.6+
- Apache with `mod_rewrite` (production) or PHP’s built-in server (local)

## Local setup

```bash
cp .env.example .env
# edit DB_* and set APP_URL plus a long APP_KEY

mysql -u root -p -e "CREATE DATABASE meridian CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p -e "CREATE USER 'meridian'@'localhost' IDENTIFIED BY 'a-strong-password'; GRANT ALL ON meridian.* TO 'meridian'@'localhost';"

mysql -u meridian -p meridian < database/schema.sql
mysql -u meridian -p meridian < database/seed.sql

php -S 127.0.0.1:8080 router.php
```

Open `http://127.0.0.1:8080`. The built-in server uses `router.php` because it does not read `.htaccess`.

## Seed accounts

Every seeded account uses the password `Meridian#2026`.

| Name | Email | Role |
| --- | --- | --- |
| Elena Voss | elena@meridian.test | Super Admin |
| Marcus Hale | marcus@meridian.test | Admin |
| Priya Raman | priya@meridian.test | Editor |
| Jonah Ellis | jonah@meridian.test | Author |
| Amira Solt | amira@meridian.test | Author |

Sign in at `/login`. Newsroom accounts land on `/admin`. Reader accounts created at `/register` use the User role and can edit `/account`.

Change these passwords before any public deployment.

## Apache

Point the document root at this project. `AllowOverride All` must be enabled so `.htaccess` can run.

The root rewrite file:

- sends `/admin/...` to `admin/index.php`
- sends every other non-file request to `index.php`
- forbids direct access to `config`, `includes`, `controllers`, `models`, `views`, `routes`, `database`, `storage`, `.env`
- enables deflate or brotli when the modules exist
- sets cache headers for static assets

`uploads/.htaccess` refuses PHP and other executable extensions. Uploaded images are also re-encoded with GD, so a polyglot file cannot be stored as the original bytes.

Required Apache modules: `rewrite`. Recommended: `headers`, `expires`, `deflate` or `brotli`.

## Production

1. Set `APP_ENV=production` and `APP_DEBUG=false`.
2. Set `APP_URL` to the canonical https origin. Titles, Open Graph, the sitemap, and password-reset links use it.
3. Set `APP_KEY` to a long random string. It salts IP hashes and remember-me tokens.
4. Serve the site over HTTPS so session and remember-me cookies are marked Secure.
5. Set `MAIL_ENABLED=true` only when `mail()` is configured. Otherwise reset links are written to `storage/logs/mail.log`.
6. Make `uploads/` and `storage/logs/` writable by the PHP user and not listable.
7. Import `database/schema.sql`, then `database/seed.sql` only for a first demo. Do not re-import the seed on a live database; it truncates content.
8. Remove or rotate the seed passwords.

`display_errors` is off when debug is false. Exceptions are written to `storage/logs/app.log`. Visitors see `404.php`, `403.php`, `419.php`, or `500.php`.

## URLs

| Path | Page |
| --- | --- |
| `/` | Homepage |
| `/blog`, `/blog/page/2` | Journal |
| `/blog/{slug}` | Article |
| `/category/{slug}` | Section |
| `/tag/{slug}` | Tag |
| `/author/{username}` | Author |
| `/search?q=` | Search |
| `/about`, `/contact`, `/privacy`, `/terms` | Pages |
| `/sitemap.xml`, `/robots.txt` | SEO |
| `/admin` | Newsroom |

## Security checklist

- PDO prepared statements for user input; table names are allow-listed
- `password_hash()` / `password_verify()`; no plaintext passwords
- CSRF token on state-changing forms and JSON requests
- Output escaped with `htmlspecialchars`
- Article HTML passed through an allow-list sanitizer on save and on render
- Session ID regenerated on login; cookies are HttpOnly and SameSite=Lax
- Login throttling (5 failures / 15 minutes) and generic rate limits
- Remember-me tokens stored as selector plus SHA-256 validator
- Password reset tokens stored hashed and expired after one hour
- Upload checks for size, extension, and MIME, then re-encodes with GD
- Role permissions enforced in the newsroom; authors edit only their own posts
- Security headers, including a Content-Security-Policy
- Honeypot and timing check on public comments and contact
- Approved comments only on the public article

## Testing checklist

- Homepage, journal, article, category, tag, author, search, about, contact, privacy, terms
- Mobile navigation, search modal, newsletter form
- Sign in, sign out, register, forgot password, reset password, remember me
- Newsroom blocked for anonymous users and for authors on sections they do not own
- Create, edit, duplicate, delete, feature, and schedule a post
- Categories, tags, media upload, alt text, comment moderation
- Newsletter subscribe, duplicate prevention, CSV export
- Contact message stored and marked read
- Sitemap, robots.txt, canonical and Open Graph tags
- Unknown URL returns 404; a form without CSRF returns 419
- A `.php` upload is rejected; comment HTML is escaped

`tests/smoke.php` checks the public routes against a running server:

```bash
php tests/smoke.php http://127.0.0.1:8080
```

## Project map

```
index.php, router.php, .htaccess
admin/                 newsroom front controller, controllers, views
api/                   search, newsletter, comments, views
assets/css, assets/js  public design system, newsroom UI, editor
config/                app and database settings
controllers/ models/   public HTTP and data access
database/schema.sql    tables, indexes, foreign keys
database/seed.sql      roles, people, sections, stories
includes/              bootstrap, database, auth, sanitizer, views
uploads/               user images, PHP disabled
views/                 public templates
```
