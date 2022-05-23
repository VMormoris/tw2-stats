FROM php:8.0-apache
COPY . /var/www/tw2-stats
WORKDIR /var/www/tw2-stats
RUN mv config/tw2-stats.conf /etc/apache2/sites-available/ &&\
    a2dissite 000-default && a2ensite tw2-stats &&\
    a2enmod rewrite
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN apt update && apt upgrade -y && apt install nano libzip-dev libpq-dev zip -y &&\
    docker-php-ext-install bcmath zip &&\
    docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql && docker-php-ext-install pdo_pgsql pgsql &&\
    composer install &&\
    cp .env.example .env && php artisan key:generate
RUN curl -fsSL https://deb.nodesource.com/setup_16.x &&\
    apt install nodejs npm -y &&\
    npm install && npm run dev
RUN apt install python3-pip -y &&\
    pip3 install python-socketio websocket-client psycopg2-binary &&\
    cp dumper-app/config.example.json dumper-app/config.json
RUN chown -R www-data:www-data /var/www/tw2-stats