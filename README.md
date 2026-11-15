![logo](public/images/logo.png)

`tw2-stats` is a tool that provides statistics for the online browser game "Tribal Wars 2". You can find a working version of the tool at: [tw2-stats.com](https://tw2-stats.com/). For information about how to set it up locally visit our [wiki page](https://github.com/VMormoris/tw2-stats/wiki).<br>

> [!NOTE]
> _Although I'm not against people using it locally for themselves. I will highly encourage you to either help me host it or host it yourself so other community members can benefit from it as well._

__* tw2-stats is not associated with or endorsed by InnoGames.__

## Development
The development environment runs on [Laravel Sail](https://laravel.com/docs/sail). Copy your environment variables example files, and fill in the passwords on `docker/.env`
```sh
cp .env.example .env
cp docker/.env.example docker/.env
```
Sail is one of the PHP dependencies, so install them once with Composer's image. The Composer scripts run later, inside Sail:
```sh
docker run --rm -u "$(id -u):$(id -g)" -e COMPOSER_HOME=/tmp/composer -v "$PWD":/app -w /app composer:2 composer install --no-scripts
```

Useful `sail` commands:
```sh
alias sail='sh vendor/bin/sail' # Or type vendor/bin/sail every time
sail up -d # Builds the app image the first time, then starts app and postgres containers in the background
sail composer install # Checks the PHP platform and runs the package discovery skipped above
sail artisan key:generate # Generates random key for your app
sail restart app # Reloads the generated key into the development server
sail npm ci # Installs all front-end dependencies from scratch
sail npm run build # Builds the front-end assets
sail npm run dev # Starts the vite development server, with hot reload
sail test # Runs the tests
sail exec -u postgres db psql -d tw2-stats # Opens psql as the postgres superuser
sail --profile dumper up -d # Starts the dumper too (needs docker/dumper/accounts.json)
sail build --no-cache # Rebuilds the app image, e.g. after a Sail update
```
The site is served at http://127.0.0.1:8000, PostgreSQL at `127.0.0.1:5432`, and Vite at `127.0.0.1:5173`.

> [!TIP]
> `sail` passes your user and group ids to the app container, so the files it writes in the repository are owned by you. Plain `docker compose` commands work too, but take them from `WWWUSER` and `WWWGROUP` in `docker/.env`: set those to `id -u` and `id -g` if they aren't 1000.

### Moving an existing checkout to Sail
The database service and its volume don't change, so your data stays as it is. Before updating, back it up anyway:
```sh
docker compose exec -T -u postgres db pg_dumpall > ~/tw2-stats-dev.sql
```
After updating:
1. In `docker/.env`, rename `UID` to `WWWUSER` and `GID` to `WWWGROUP`.
2. In `.env`, add the line `APP_SERVICE=app` from `.env.example`.
3. If you have a `compose.override.yaml`, change its mounts into the app container from `/var/www/tw2-stats` to `/var/www/html`, and if it overrides the app's `ports`, keep `5173:5173` in the list for Vite.
4. Install Sail with the `docker run ... composer install --no-scripts` command above, then run `sail up -d` and `sail composer install`. The app container is replaced by the Sail one; the old `node` service is gone, `sail npm` replaces it.

The app container now runs PHP 8.5 and serves with `php artisan serve`, while `compose.prod.yaml` runs PHP 8.4 with PHP-FPM behind Nginx, whose routing rules are in `docker/web/default.conf`. The server starts with `--no-reload`, so restart it (`sail restart app`) after editing `.env`.

To go back, switch to the commit before Sail (`git switch --detach <commit>`, which refuses to overwrite uncommitted changes), rename the two variables in `docker/.env` back, and run `docker compose up -d --build app`. The database volume is the same in both setups.

## Hosting with compose.prod.yaml
The setup is described on the [wiki page](https://github.com/VMormoris/tw2-stats/wiki). `compose.prod.yaml` builds two images from one checkout:
* `tw2-stats-web` runs the `web` service. It's Nginx with the public files and the built assets. It's the only service on the reverse proxy's network, where it's called `tw2-stats`, and it has no database credentials.
* `tw2-stats-php` runs the `app` service. It's PHP-FPM with the code, its dependencies and the Vite manifest, and it runs as `www-data`, which can write only to `storage`. The code stays read-only. The configuration and routes are cached at every start, so changes to `.env.production` take effect after `docker compose up -d`.

Useful commands, with the variables from the top of `compose.prod.yaml` exported:
```sh
docker compose build --pull app web # Builds both images; always build them together, so the assets match the code
docker compose up -d # Starts or updates the services, keeping the database
docker compose ps # db, app, web and backup should say healthy or running
docker compose logs -f web app # Access log of Nginx, errors of PHP
docker compose run --rm cli php artisan world:create en130 # Adds a world; asks for the details not given as options
docker compose exec app php artisan cache:forget worlds.ids # Shows a new world on the site now rather than within 5 minutes
docker compose run --rm cli php artisan tinker # Uses the read-only api connection by default
docker compose exec db psql -U postgres -d tw2-stats # Opens psql as the postgres superuser
```
`cli` is excluded from normal startup and is intended for `docker compose run`, in a container of the PHP image that is removed afterwards. It connects as the read-only `api` role, while `world:create` connects as the `tw2-stats` role with `DB_ADMIN_PASSWORD`, like the dumper, and asks for the password if that's not set. The site keeps the list of worlds in the `app` container's own cache, so `world:create` can't clear it from `cli`. Run the `cache:forget` command above afterwards.

### Moving from the Apache image
Before the update, `app` was one Apache container, built as the `tw2-stats-app` image. The `db`, `dumper` and `backup` services and the volumes don't change, so they keep running and the data stays as it is. Make sure `backups/` has a recent dump anyway. Then, with the variables exported:
1. Switch to the new commit, and build while the old container keeps serving: `docker compose build --pull app web`.
2. Run `docker compose up -d`. It replaces `app` and starts `web`, so the site is down for a few seconds. The reverse proxy keeps forwarding to `http://tw2-stats:80`.
3. Check `docker compose ps` and the site. If the proxy still answers with 502 after a minute, restart the proxy, which can keep the old container's address.
4. The old `tw2-stats-app` image stays, for going back. Once the new setup has run for a while, remove it with `docker image rm tw2-stats-app`.

To go back, switch to the commit before the update (`git switch --detach <commit>`) and run `docker compose up -d --remove-orphans`. It starts `app` from the kept `tw2-stats-app` image without building and removes `web`. Never add `-v` to `docker compose down`, which deletes the database volume.

## Contributing
* _For reporting a bug please open an issue describing the bug._
* _If you want to contribute or suggest a new feature open an issue describing what you want._
* _Feel free to work to any of the open issues and make a pull request._

## Third Party Tools
* [PostgreSQL](https://www.postgresql.org/)
* [Nginx](https://nginx.org/)
* [Vite](https://vite.dev/)
* [Laravel](https://laravel.com/)
* [laravel-cte](https://github.com/staudenmeir/laravel-cte)
* [Vue3](https://vuejs.org/)
* [Bootstrap](https://getbootstrap.com/)
* [vue-chartjs](https://vue-chartjs.org/)
* [ChartJS](https://www.chartjs.org/)
* [ForkAwesome](https://forkaweso.me/Fork-Awesome/)

If you want to **keep up with changes** or if you have **questions** that don't require opening issue come and join our [discord server](https://discord.com/invite/vxZbCrShaP).
