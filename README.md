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

The app container now runs PHP 8.5 and serves with `php artisan serve`, while `compose.prod.yaml` runs PHP 8.4 under Apache, so the `public/.htaccess` rules only apply there. The server starts with `--no-reload`, so restart it (`sail restart app`) after editing `.env`.

To go back, switch to the commit before Sail (`git switch --detach <commit>`, which refuses to overwrite uncommitted changes), rename the two variables in `docker/.env` back, and run `docker compose up -d --build app`. The database volume is the same in both setups.

## Contributing
* _For reporting a bug please open an issue describing the bug._
* _If you want to contribute or suggest a new feature open an issue describing what you want._
* _Feel free to work to any of the open issues and make a pull request._

## Third Party Tools
* [PostgreSQL](https://www.postgresql.org/)
* [Apache2](https://httpd.apache.org/)
* [Vite](https://vite.dev/)
* [Laravel](https://laravel.com/)
* [laravel-cte](https://github.com/staudenmeir/laravel-cte)
* [Vue3](https://vuejs.org/)
* [Bootstrap](https://getbootstrap.com/)
* [vue-chartjs](https://vue-chartjs.org/)
* [ChartJS](https://www.chartjs.org/)
* [ForkAwesome](https://forkaweso.me/Fork-Awesome/)

If you want to **keep up with changes** or if you have **questions** that don't require opening issue come and join our [discord server](https://discord.com/invite/vxZbCrShaP).
