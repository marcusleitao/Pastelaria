# Use a imagem base PHP
FROM php:8.2-fpm-alpine

# Instalar dependências necessárias para o PostgreSQL e o PHP
RUN apk add --no-cache \
    postgresql-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Instalar o Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copiar o arquivo composer.json e composer.lock
COPY composer.json composer.lock ./

# Rodar o Composer para instalar as dependências
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Copiar o restante do código da aplicação
COPY . /var/www/html

# Definir o diretório de trabalho
WORKDIR /var/www/html

# Comando padrão
CMD ["php-fpm"]
