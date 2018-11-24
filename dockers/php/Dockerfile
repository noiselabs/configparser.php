FROM php:7.2-fpm

ARG http_proxy
ARG https_proxy
ARG no_proxy

ENV TERM=dumb
ENV http_proxy=$http_proxy
ENV https_proxy=$https_proxy
ENV no_proxy=$no_proxy

RUN pear config-set http_proxy $http_proxy; exit 0

RUN apt-get update; \
	apt-get install -y --no-install-recommends \
	git \
    zip \
    unzip \
	zlib1g-dev \
    wget \
	&& docker-php-ext-install -j$(nproc) pdo pdo_mysql zip

RUN { \
        echo 'error_reporting  =  E_ALL'; \
        echo 'log_errors = On'; \
        echo 'display_errors = Off'; \
        echo 'date.timezone = Europe/Paris'; \
        echo 'memory_limit = 1G'; \
    } > /usr/local/etc/php/php.ini

# Install Composer
RUN curl https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer --version \
    && mkdir /.composer/ \
    && chmod 777 -R /.composer/

CMD ["php-fpm"]

WORKDIR /usr/local/apache2/htdocs/
EXPOSE 9000
