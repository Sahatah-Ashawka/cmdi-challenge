FROM php:8.2-apache

COPY .htaccess /var/www/html/.htaccess
COPY index.php /var/www/html/.index.php
COPY 7h15_is_Fl49.txt /7h15_is_Fl49.txt
COPY naruto.jpg /var/www/html/naruto.jpg
COPY images.jpg /var/www/html/images.jpg
COPY bgm.mp3 /var/www/html/bgm.mp3

RUN chmod 644 /var/www/html/.htaccess /var/www/html/.index.php /7h15_is_Fl49.txt /var/www/html/*.jpg /var/www/html/bgm.mp3 && \
    chown www-data:www-data /7h15_is_Fl49.txt

EXPOSE 80
