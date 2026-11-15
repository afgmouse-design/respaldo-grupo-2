# === ETAPA 1: Compilar el Frontend ===
FROM node:20-alpine AS frontend-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
# Compilamos Vue/Vite (Generará los archivos en public/build)
RUN npm run build

# === ETAPA 2: El entorno de producción PHP ===
FROM php:8.2-apache AS backend-runtime

# 1. Instalar dependencias del sistema y extensiones de PHP necesarias para Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd

# 2. Habilitar el módulo de reescritura de Apache (Vital para las rutas de Laravel)
RUN a2enmod rewrite

# 3. Configurar Apache para que apunte a la carpeta /public de Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 4. Establecer el directorio de trabajo
WORKDIR /var/www/html

# 5. Copiar el código del proyecto Laravel
COPY . .

# 6. TRAER EL FRONTEND COMPILADO desde la Etapa 1
COPY --from=frontend-builder /app/public/build ./public/build

# 7. Instalar Composer de forma oficial y ejecutar la instalación de paquetes PHP
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --optimize-autoloader --no-dev

# 8. Otorgar permisos correctos para que Apache pueda escribir en storage y bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Exponer el puerto 80 estándar
EXPOSE 80

# Arrancar Apache en primer plano
CMD ["apache2-foreground"]