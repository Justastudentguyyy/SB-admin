# Local Login Credentials

These accounts are for local development and testing only. Do not use the sample
test password in a deployed environment.

## Login page

Open `/Login` to use the database-backed login form.

## Seeded accounts

| Account | Email | Password | Status |
|---|---|---|---|
| Administrator | `admin@example.com` | The value of `ADMIN_PASSWORD` in your local `.env` file | Active and email-verified |
| Test User | `testuser@example.com` | `password` | Active and email-verified |

Set `ADMIN_PASSWORD` in `.env` before seeding. The actual administrator password
is intentionally not documented here; keep it in the ignored local `.env` file
and never commit or share it.

```dotenv
ADMIN_PASSWORD=choose-a-local-password
```

Then create or update the seeded accounts with:

```powershell
php artisan migrate --force
php artisan db:seed
```

The test user's default password comes from `database/factories/UserFactory.php`.

## Admin dashboard

The custom `/Login` form redirects successful logins to `/admin`. The admin
dashboard and user-management actions require an administrator account. The
role migration marks the existing `admin@example.com` account as an admin
without changing its password; other existing accounts default to Student.

For MySQL, configure `DB_CONNECTION=mysql` and set `DB_DATABASE` to an existing
schema in `.env`. Laravel migrations and seeders use that same configured
connection. Apply schema changes with:

```powershell
php artisan migrate
```
