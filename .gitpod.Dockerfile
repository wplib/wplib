FROM gitpod/workspace-mysql:latest

RUN sudo install-packages \
    php7.4-cli \
    php7.4-bcmath \
    php7.4-curl \
    php7.4-dev \
    php7.4-gd \
    php7.4-imagick \
    php7.4-intl \
    php7.4-mbstring \
    php7.4-mysql \
    php7.4-pspell \
    php7.4-redis \
    php7.4-xdebug \
    php7.4-zip \
    php8.1-cli \
    php8.1-bcmath \
    php8.1-curl \
    php8.1-dev \
    php8.1-gd \
    php8.1-imagick \
    php8.1-intl \
    php8.1-mbstring \
    php8.1-mysql \
    php8.1-pspell \
    php8.1-redis \
    php8.1-xdebug \
    php8.1-zip

ENV NGINX_DOCROOT_IN_REPO="www"
