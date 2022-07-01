FROM gitpod/workspace-mysql:latest

RUN sudo install-packages \
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

