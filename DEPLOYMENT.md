# Deployment (Hetzner + Coolify)

Production runs on a Hetzner server managed by [Coolify](https://coolify.io). Coolify builds the repo with the
**Docker Compose** build pack using [`docker-compose.yml`](docker-compose.yml) and the [`Dockerfile`](Dockerfile).

| Piece | Where it runs |
| --- | --- |
| `app` (nginx + PHP-FPM, port 8080) | compose service; runs migrations and caches on every start |
| `queue` (`queue:work`) | compose service, same image; generates the queued image conversions |
| MySQL | separate Coolify database resource, with Coolify's scheduled backups |
| Uploaded media | `storage` volume mounted at `storage/app`, shared by `app` and `queue` |

## One-time setup

### 1. Server

1. Create a Hetzner Cloud server (Ubuntu LTS, at least 2 GB RAM; the image build needs it).
2. Firewall: allow inbound 22, 80 and 443, plus 8000 for the Coolify dashboard until it has its own domain.
3. Install Coolify as described in its docs: `curl -fsSL https://cdn.coollabs.io/coolify/install.sh | sudo bash`
4. Open `http://<server-ip>:8000`, create the admin account, and point your domain's DNS (A record) at the server.

### 2. Database

1. In your Coolify project: **New resource → Database → MySQL**. Start it.
2. Note the **internal** connection details: host (the container name), port `3306`, database, user and password.
3. **Backups**: add an S3 destination (e.g. Hetzner Object Storage) under *Storage*, then enable scheduled backups
   on the database.

### 3. Application

1. **New resource → Application →** your GitHub repo (use the GitHub App for a private repo), branch `main`.
2. **Build pack: Docker Compose**, compose file `/docker-compose.yml`.
3. Enable **Connect To Predefined Network** so the stack can reach the MySQL resource.
4. **Domains**: set the `app` service to `https://your-domain.gr:8080`. The `:8080` only tells the proxy which
   container port to use; visitors still use normal HTTPS. Leave `queue` without a domain.
5. **Environment variables** (Coolify lists them from the compose file):

   | Variable | Value |
   | --- | --- |
   | `APP_KEY` | output of `php artisan key:generate --show` (generate once, keep it: it encrypts sessions) |
   | `APP_URL` | `https://your-domain.gr` (must be https so media URLs are https) |
   | `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | the internal details from step 2 |
   | `LOG_LEVEL`, `MAIL_MAILER` | optional; default to `warning` and `log` |

6. **Deploy**. The first start runs all migrations.
7. Create an admin user: open the `app` container's **Terminal** in Coolify and run
   `php artisan make:filament-user`.

### 4. Moving existing local content (optional)

```bash
# Database: dump locally, then import into the Coolify MySQL (e.g. via its terminal or a temporary public port)
mysqldump -h 127.0.0.1 -P 3307 -u root fauxvero > fauxvero.sql

# Media: copy storage/app/public into the app container's storage volume
scp -r storage/app/public root@<server-ip>:/tmp/media
ssh root@<server-ip> 'docker cp /tmp/media/. <app-container>:/var/www/html/storage/app/public/'
```

Afterwards, fix ownership if needed: `docker exec -u root <app-container> chown -R www-data:www-data /var/www/html/storage/app`.

## Deploying changes

Push to `main`. With **Auto Deploy** enabled (GitHub App), Coolify builds and replaces both containers; migrations run
automatically when `app` starts, and the queue worker restarts with the new code.

## Backups

- **Database**: Coolify scheduled backups (step 2).
- **Media volume**: not covered by Coolify. Enable Hetzner server backups/snapshots, or add a volume backup job.

## Not set up yet

- **Scheduler**: nothing is scheduled today. When the first scheduled task is added, add a `scheduler` service to
  `docker-compose.yml` running `php /var/www/html/artisan schedule:work`.
- **Mail**: `MAIL_MAILER` defaults to `log`. Configure SMTP variables when order emails are added.
