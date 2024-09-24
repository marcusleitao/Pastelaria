# Use a imagem base PHP
FROM php:8.2-fpm-alpine

# Instalar dependências necessárias para o PostgreSQL e o PHP
RUN apk add --no-cache \
    postgresql-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Copiar o arquivo composer.json e composer.lock
COPY composer.json composer.lock ./