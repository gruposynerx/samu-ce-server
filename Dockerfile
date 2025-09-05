# FROM php:8.2-fpm

# # Instalar dependências do sistema e extensões PHP
# RUN apt-get update && apt-get install -y \
#     git \
#     curl \
#     unzip \
#     zip \
#     libzip-dev \
#     libpng-dev \
#     libonig-dev \
#     libxml2-dev \
#     libpq-dev \
#     libcurl4-openssl-dev \
#     && docker-php-ext-install pdo pdo_pgsql pdo_mysql mbstring zip exif pcntl bcmath sockets

# # Instalar Composer
# COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# # Diretório de trabalho
# WORKDIR /var/www

# # Copiar os arquivos do projeto
# COPY . .

# # Instalar dependências do Laravel
# RUN composer install --optimize-autoloader --no-dev --no-interaction

# # Permissões
# RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
#     && chmod -R 755 /var/www/storage /var/www/bootstrap/cache

# # Expor porta do servidor embutido do Laravel
# EXPOSE 8000

# # Comando para rodar o servidor Laravel
# CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
FROM php:8.2-fpm

# Instalar dependências do sistema e extensões PHP
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libcurl4-openssl-dev \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql mbstring zip exif pcntl bcmath sockets

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Definir diretório de trabalho
WORKDIR /var/www

# Copiar os arquivos de dependência primeiro para melhor cache
COPY composer.json composer.lock ./

# Copiar todos os arquivos do projeto
COPY . .

RUN composer diagnose || true

# Instalar dependências do Laravel
RUN composer install --optimize-autoloader --no-dev --no-interaction -vvv

# Copiar o script de entrada
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Ajustar permissões
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 755 /var/www/storage /var/www/bootstrap/cache

# Expor porta do artisan serve
EXPOSE 8000

# Usar o script como ponto de entrada
CMD ["/entrypoint.sh"]
