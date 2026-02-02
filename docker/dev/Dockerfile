FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
	git \
	curl \
	libpng-dev \
	libonig-dev \
	libxml2-dev \
	libzip-dev \
	unzip \
	gnupg \
	&& docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
	&& pecl install redis \
	&& docker-php-ext-enable redis \
	&& apt-get clean && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
	&& apt-get update \
	&& apt-get install -y nodejs \
	&& apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

EXPOSE 80 5173

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]
