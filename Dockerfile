# Use the official PHP image that already includes Apache
FROM php:8.2-apache

# Copy your project files from your PC into the container’s web folder
COPY ./www /var/www/html

# Enable Apache's mod_rewrite module (optional, for clean URLs)
RUN a2enmod rewrite

# Expose port 80 so it can be accessed from outside
EXPOSE 80