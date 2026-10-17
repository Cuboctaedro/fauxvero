# Deployment (Hetzner Cloud + Coolify)

Production runs on an existing Hetzner Cloud server managed by [Coolify](https://coolify.io). The server is shared:
it already hosts another project, and each site gets its own Coolify project. Sites on this stack (Laravel + Filament,
this repo's `Dockerfile` and `docker-compose.yml`) use one shared MySQL instance.

| Piece | Where it runs |
| --- | --- |
| Server | Hetzner Cloud server, Hetzner Cloud Firewall in front |
| Coolify + Traefik proxy | on the server; Traefik routes each domain to its site and handles Let's Encrypt certificates |
| MySQL | one Coolify database resource in the **Shared** project; one database + user per site |
| Each site | its own Coolify project, built with the **Docker Compose** build pack from its GitHub repo |
| Fauxvero `app` (nginx + PHP-FPM, port 8080) | compose service; runs migrations, `storage:link` and caches on every start |
| Fauxvero `queue` (`queue:work`) | compose service, same image; generates the queued image conversions |
| Uploaded media | `storage` volume mounted at `storage/app`, shared by `app` and `queue` |
| Backups | MySQL: Coolify scheduled backups to S3-compatible storage. Whole server (including media volumes): Hetzner Backups |

The server and Coolify are already installed and working. Part **A** checks that the server has what this site needs;
anything already in place from the first project is just confirmed, not redone. Part **B** sets up the shared MySQL.
Part **C** deploys this site. Part **D** is the checklist for every later site.

Throughout, replace `example.gr` with your real domain and `<server-ip>` with the server's IPv4 address. Server
commands are shown as `root`, Hetzner's default login; if you log in as another user, prefix them with `sudo`.

---

## Part A: Server check (once)

### A1. Capacity

The first build and the two new containers need headroom next to the project that is already running. In Coolify,
open **Servers → localhost → Metrics**, or on the server run:

```bash
free -h   # memory and swap
df -h /   # disk
```

Fauxvero adds roughly 200–400 MB of RAM (`app` + `queue`) plus the shared MySQL (around 400–500 MB) if the server does
not have one yet. Image builds (composer + npm) briefly use a lot more. If the server has no swap, add a 2 GB swap file
so a build is not killed for lack of memory:

```bash
fallocate -l 2G /swapfile
chmod 600 /swapfile
mkswap /swapfile
swapon /swapfile
echo '/swapfile none swap sw 0 0' >> /etc/fstab
```

If memory is tight, rescale the server in the Hetzner Cloud Console (**server → Rescale**; the server is powered off
for a few minutes). Choose **CPU and RAM only** to keep the disk size, which keeps a later downgrade possible.

### A2. Hetzner Cloud Firewall

In the Hetzner Cloud Console, open **Firewalls** and check that a firewall is applied to the server. Its inbound rules
should be:

| Protocol | Port | Purpose |
| --- | --- | --- |
| TCP | 22 | SSH |
| TCP | 80 | HTTP (Let's Encrypt challenges, redirect to HTTPS) |
| TCP | 443 | HTTPS |
| ICMP | | ping |

Everything not listed is dropped. The firewall is stateful, so replies to the server's own outgoing connections need
no rule, and it filters IPv4 and IPv6 alike. It runs in front of the server, so it also covers ports that Docker
publishes, which a firewall on the server itself (ufw) does not.

Ports **8000** and **6001-6002** (Coolify's dashboard, realtime and terminal) should not be open: the dashboard is
reached through its HTTPS domain. If they are still open because the dashboard is used at `http://<server-ip>:8000`,
give it a domain first (**Settings → Configuration → Domain** in Coolify, with an `A` record pointing at the server),
then remove those rules. MySQL (3306) is never opened in the firewall: sites reach it over Docker's internal network,
and a desktop client connects through SSH (see **B5**).

### A3. Coolify settings

Confirm, or set if the first project did not need them:

- **Profile → Two-factor authentication**: enabled.
- **Settings → Configuration**: instance timezone (e.g. `Europe/Athens`).
- **Servers → localhost → Configuration → Advanced**: Docker cleanup enabled (a disk-usage threshold such as 80%, or a
  schedule). Each deploy builds a new image, and old images fill the disk otherwise.
- **Notifications** (optional): email, Telegram or Discord, subscribed to failed deployments and failed backups.

### A4. GitHub access

Coolify needs a GitHub App that can read `Cuboctaedro/fauxvero`.

- **A GitHub App already exists** under **Sources** on the same GitHub account: on GitHub, go to **Settings →
  Applications → Installed GitHub Apps → (your Coolify app) → Configure** and add `Cuboctaedro/fauxvero` to the
  selected repositories.
- **None exists, or it belongs to another account**: in Coolify, go to **Sources → + Add → GitHub App**, keep the
  defaults, register it on your GitHub account, choose **Only select repositories** and pick `Cuboctaedro/fauxvero`.

The GitHub App lets Coolify clone private repos and receive push webhooks, so a push to `main` deploys automatically.

### A5. Backup storage

Database dumps go to S3-compatible storage. If **Storages** in Coolify already lists one, reuse it and skip this step.
Otherwise set up Cloudflare R2, whose free tier includes 10 GB of storage, plenty for dumps of small sites:

1. Create a free account at [cloudflare.com](https://www.cloudflare.com) and open **R2 Object Storage**. Activating R2
   may ask for a payment method even on the free tier.
2. **Create bucket**, e.g. `coolify-backups`.
3. **Manage API tokens → Create API token**: permission **Object Read & Write**, limited to that bucket. Note the
   **Access Key ID**, **Secret Access Key** and the S3 endpoint shown with them. The secret is shown only once.
4. In Coolify, go to **Storages → + Add** and fill in:
   - **Endpoint**: `https://<account-id>.r2.cloudflarestorage.com`
   - **Region**: `auto`
   - **Bucket**, **Access key**, **Secret key**
5. Save, then **Validate connection**.

### A6. Server backups

Coolify does not back up volumes, so the uploaded images are only covered by a backup of the whole server. In the
Hetzner Cloud Console, open the server's **Backups** tab and check that backups are enabled. They cost 20% of the
server price, run daily in a window Hetzner assigns, and keep the last 7.

Backups cover the server's own disk only. If Docker's data was moved to an attached Hetzner **Volume**, that volume is
not included and needs its own copy.

---

## Part B: Shared MySQL (once)

If the server already has a MySQL resource that this site should share, skip B1 and continue from B2 with that
resource. A database that belongs to the other project alone is better left alone: create the shared one here.

### B1. Create the database server

1. In Coolify, go to **Projects → + Add**, name it **Shared**, and open its **production** environment.
2. **+ New → Databases → MySQL**. Pick 8.4 (the current LTS) and name it `mysql`.
3. Leave **Make it publicly available** off for now, then click **Start**. **B5** turns it on for desktop-client
   access.

### B2. Note the connection details

On the MySQL resource's **General** page, note:

- the **internal host**: the resource's UUID, a random string such as `k8s0c4wg…`. It is shown in the internal
  connection URL, `mysql://…@<uuid>:3306/…`.
- the **root password**.

Sites connect with the UUID as `DB_HOST` and port `3306`.

### B3. Scheduled backups

On the MySQL resource, open **Backups → + Add**:

- **Frequency**: `0 3 * * *` (daily at 03:00)
- **Save to S3**: on, with the storage from A5
- **Databases to backup**: every site database, comma-separated, e.g. `fauxvero`. Update this list whenever you add a
  site.
- **Retention**: e.g. keep 14 locally and 30 in S3

Save, click **Backup now** once, and check in the bucket that the backup contains every listed database.

### B4. Create a database and user for a site

Repeat this for every site. On the MySQL resource, open **Terminal**:

```bash
mysql -u root -p   # paste the root password from B2
```

```sql
CREATE DATABASE fauxvero CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'fauxvero'@'%' IDENTIFIED BY '<long random password>';
GRANT ALL PRIVILEGES ON fauxvero.* TO 'fauxvero'@'%';
```

Generate the password with e.g. `openssl rand -base64 24` and keep it in your password manager. Each site's user can
see only its own database. Ignore the `default` database Coolify creates.

### B5. Connecting with a desktop client

To browse or edit the databases, use a desktop client such as TablePlus, Sequel Ace or DBeaver over an SSH tunnel.
Nothing extra runs on the server, and MySQL stays unreachable from the internet.

1. **Bind MySQL to a host port.** On the MySQL resource's **General** page, turn on **Make it publicly available** and
   set **Public port** to `3306`. Coolify starts a small proxy container that publishes that port on the server.
   Despite the name, the port is not reachable from the internet: the Hetzner Cloud Firewall (A2) has no rule for
   3306, and it filters ports that Docker publishes too.
2. **Check that it is closed from outside.** On your Mac, this must time out:

   ```bash
   nc -zv -G 5 <server-ip> 3306
   ```

   If it connects, the firewall from A2 is not applied. Turn the setting off again and fix A2 first.
3. **Set up the client** with a connection of type MySQL over SSH:

   | Setting | Value |
   | --- | --- |
   | SSH host | `<server-ip>` |
   | SSH port | `22` |
   | SSH user | `root`, with the same SSH key you use for the server |
   | MySQL host | `127.0.0.1` |
   | MySQL port | `3306` |
   | MySQL user and password | the site's user (e.g. `fauxvero`) for that site's database, or `root` with the password from B2 for all of them |

4. **Clients without built-in SSH**: open the tunnel by hand, then connect the client to `127.0.0.1`, port `3308`:

   ```bash
   ssh -N -L 3308:127.0.0.1:3306 root@<server-ip>
   ```

   Local port 3308 avoids the MySQL servers already running on your Mac (Herd's is on 3307).

Prefer the site's own user for everyday work: `root` can drop any site's database.

---

## Part C: Deploy Fauxvero

### C1. DNS

At your DNS provider, point the site's domain at the server **before** the first deploy, so Let's Encrypt can issue the
certificate straight away:

- `example.gr` → `A` `<server-ip>`
- `www.example.gr` → `A` `<server-ip>`, or a `CNAME` to `example.gr`

Add `AAAA` records only if the server has IPv6 working and the firewall from A2 is applied. On Cloudflare DNS, use
**DNS only** (grey cloud).

### C2. Database

Run **B4** with database and user `fauxvero`, and add `fauxvero` to the backup list in **B3**.

### C3. Create the application

1. In Coolify, go to **Projects → + Add**, name it **Fauxvero**, and open **production**.
2. **+ New → Private Repository (with GitHub App)**, then pick the GitHub App from A4 and `Cuboctaedro/fauxvero`.
3. **Branch**: `main`. **Build pack**: **Docker Compose**. **Docker Compose location**: `/docker-compose.yml`.
4. Continue. Coolify reads the compose file and lists the `app` and `queue` services.

### C4. Network

In the application's **General** settings, enable **Connect To Predefined Network**. Without it, the stack gets its own
isolated network and cannot reach the MySQL resource in the Shared project.

### C5. Domains

Set the **Domains** for the `app` service to:

```
https://example.gr:8080,https://www.example.gr:8080
```

The `:8080` only tells Traefik which **container** port to forward to (the image's nginx listens on 8080). Visitors use
plain `https://example.gr`. Leave `queue` without a domain; it serves no traffic.

### C6. Environment variables

Coolify lists the variables from `docker-compose.yml` on the **Environment Variables** tab. Fill in:

| Variable | Value |
| --- | --- |
| `APP_KEY` | run `php artisan key:generate --show` locally and paste the output. Generate it once and keep it: it encrypts sessions and other encrypted data |
| `APP_URL` | `https://example.gr` (must be https, so media URLs are https) |
| `DB_HOST` | the MySQL UUID from B2 |
| `DB_DATABASE` | `fauxvero` |
| `DB_USERNAME` | `fauxvero` |
| `DB_PASSWORD` | the password from C2 |
| `LOG_LEVEL`, `MAIL_MAILER` | optional; default to `warning` and `log` |
| `SESSION_SECURE_COOKIE` | optional; defaults to `true`. Set it to `false` only while testing on Coolify's http `sslip.io` domain (browsers drop secure cookies over http, so every login fails with "This page has expired"), and delete it once the real https domain is set |

`DB_PORT` defaults to `3306`, and `APP_NAME` to `Fauxvero`. Everything else is fixed in `docker-compose.yml`.

### C7. Deploy

Click **Deploy** and watch the logs. The first build takes a few minutes. On start, `app` runs all migrations, and
`queue` starts once `app` is healthy.

Then check:

- `https://example.gr/up` returns 200 (and `https://www.example.gr/up` too)
- `https://example.gr` loads the site
- the other project on the server still responds normally

**Auto deploy** is on by default with the GitHub App: every push to `main` builds and deploys.

### C8. Admin user

Open the `app` service's **Terminal** in Coolify and run:

```bash
php artisan make:filament-user
```

That creates the first user only. Add everyone else from **Users** in the panel: either type a password for them, or
leave it empty to email them a link to set their own (needs mail, see **Not set up yet**).

Log in to the Filament panel, upload a product image and check that the resized WebP versions appear shortly after.
That proves the `queue` worker is running.

### C9. Moving existing local content (optional)

**Database.** Dump the local database and load it into the new `fauxvero` database:

```bash
# on your Mac
mysqldump -h 127.0.0.1 -P 3307 -u root fauxvero > fauxvero.sql
scp fauxvero.sql root@<server-ip>:/tmp/

# on the server (the MySQL container is named after its UUID from B2)
docker exec -i -e MYSQL_PWD='<fauxvero password>' <mysql-uuid> mysql -u fauxvero fauxvero < /tmp/fauxvero.sql
rm /tmp/fauxvero.sql
```

**Media.** Copy `storage/app/public` into the `app` container's storage volume. Run `docker ps` on the server to find
the container name, which starts with `app-`:

```bash
# on your Mac
scp -r storage/app/public root@<server-ip>:/tmp/media

# on the server
docker cp /tmp/media/. <app-container>:/var/www/html/storage/app/public/
docker exec -u root <app-container> chown -R www-data:www-data /var/www/html/storage/app
rm -rf /tmp/media
```

---

## Part D: Adding the next site

Every new site on this stack follows the same recipe:

1. **Repo**: copy `Dockerfile`, `docker-compose.yml` and `.dockerignore` from this repo, plus the `trustProxies` line in
   `bootstrap/app.php`. Adjust the `APP_NAME` default and locales in `docker-compose.yml`, and any extra PHP extensions
   in the `Dockerfile`. If the site has no queued jobs, you can drop the `queue` service.
2. **GitHub access**: on GitHub, go to **Settings → Applications → Installed GitHub Apps → (your Coolify app) →
   Configure** and add the new repository.
3. **Database**: run **B4** with the new name, and add it to the backup list in **B3**.
4. **DNS**: as in **C1**, for the new domain.
5. **Coolify**: a new project named after the site, then **C3 to C8** with the new repo, domain and credentials.
6. **Capacity**: check as in **A1** before adding the site, and rescale the server when memory gets tight.

---

## Day to day

- **Deploying**: push to `main`. Coolify builds and replaces both containers. Migrations run when `app` starts, and the
  queue worker restarts with the new code.
- **Logs**: each service's **Logs** tab in Coolify (the app logs to stderr).
- **Rolling back**: the application's **Rollback** tab redeploys a previous image. Migrations are not rolled back.
- **Database access**: a desktop client over an SSH tunnel (B5).
- **Backups**: MySQL dumps go to S3 storage daily and are kept for 30 days (B3). The whole server, uploaded images
  included, has Hetzner's daily backup, which keeps the last 7 (A6). Try a restore once, e.g. load a dump into a
  scratch database, so you know the steps before you need them.
- **Updates**: Coolify updates itself if auto-update is on (**Settings → Configuration**), and Ubuntu installs
  security updates. Reboot the server every now and then (`reboot`; all containers restart on their own).

## Not set up yet

- **Scheduler**: nothing is scheduled today. When the first scheduled task is added, add a `scheduler` service to
  `docker-compose.yml` running `php /var/www/html/artisan schedule:work`.
- **Mail**: `MAIL_MAILER` defaults to `log`, so password-reset and new-user emails from the panel only reach the
  `queue` service's logs. Until mail is set up, admins set passwords from **Users** instead. Configure SMTP variables
  when order emails are added. Hetzner blocks outgoing ports 25 and 465 on new accounts, so use a mail provider on
  port 587, or ask Hetzner to unblock them.
