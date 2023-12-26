## Get started Laravel project


#### Install composer dependencies
```sh
composer install
```

*or*

```sh
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs
```

-----

#### Copy envinronment

```sh
cp .env.example .env
```

-----

#### Copy `docker-compose` recipe

```sh
cp docker-compose.dev.yml docker-compose.yml
```

Make adjusts on `docker-compose.yml` and `.env` files.

-----

#### Up `sail`

```sh
./vendor/bin/sail up
```
*or*

```sh
./vendor/laravel/sail/bin/sail up
```

> **!Tip:** `sail` alias
> ```sh
> alias sail='./vendor/laravel/sail/bin/sail up'
> ```

-----

#### Key generate
```sh
sail php artisan key:generate
```

-----

#### Migrate and seed
```sh
sail php artisan migrate --step --seed
```
*or*
```sh
sail php artisan db:seed
```

-----

#### Storage path
```sh
sail php artisan storage:link --force
```

-----

#### 🎊DONE🎉
