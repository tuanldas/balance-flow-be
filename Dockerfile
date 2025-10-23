FROM ubuntu:24.04

LABEL maintainer="Balance Flow Team"

ARG WWWGROUP
ARG NODE_VERSION=22
ARG POSTGRES_VERSION=17

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC
ENV SUPERVISOR_PHP_COMMAND="/usr/bin/php -d variables_order=EGPCS /var/www/html/artisan serve --host=0.0.0.0 --port=80"
ENV SUPERVISOR_PHP_USER="sail"
ENV PLAYWRIGHT_BROWSERS_PATH=0 

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN echo "Acquire::http::Pipeline-Depth 0;" > /etc/apt/apt.conf.d/99custom && \
    echo "Acquire::http::No-Cache true;" >> /etc/apt/apt.conf.d/99custom && \
    echo "Acquire::BrokenProxy    true;" >> /etc/apt/apt.conf.d/99custom

RUN apt-get update && apt-get upgrade -y \
    && mkdir -p /etc/apt/keyrings \
    && apt-get install -y gnupg gosu curl ca-certificates zip unzip git supervisor sqlite3 libcap2-bin libpng-dev python3 dnsutils librsvg2-bin fswatch ffmpeg nano  \
    && curl -sS 'https://keyserver.ubuntu.com/pks/lookup?op=get&search=0xb8dc7e53946656efbce4c1dd71daeaab4ad4cab6' | gpg --dearmor | tee /etc/apt/keyrings/ppa_ondrej_php.gpg > /dev/null \
    && echo "deb [signed-by=/etc/apt/keyrings/ppa_ondrej_php.gpg] https://ppa.launchpadcontent.net/ondrej/php/ubuntu noble main" > /etc/apt/sources.list.d/ppa_ondrej_php.list \
    && apt-get update \
    && apt-get install -y php8.4-cli php8.4-dev \
       php8.4-pgsql php8.4-sqlite3 php8.4-gd \
       php8.4-curl php8.4-mongodb \
       php8.4-imap php8.4-mysql php8.4-mbstring \
       php8.4-xml php8.4-zip php8.4-bcmath php8.4-soap \
       php8.4-intl php8.4-readline \
       php8.4-ldap \
       php8.4-msgpack php8.4-igbinary php8.4-redis php8.4-swoole \
       php8.4-memcached php8.4-pcov php8.4-imagick php8.4-xdebug \
    && curl -sLS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer \
    && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_VERSION.x nodistro main" > /etc/apt/sources.list.d/nodesource.list \
    && apt-get update \
    && apt-get install -y nodejs \
    && npm install -g npm \
    && npm install -g pnpm \
    && npm install -g bun \
    && npx playwright install-deps \
    && curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | gpg --dearmor | tee /etc/apt/keyrings/yarn.gpg >/dev/null \
    && echo "deb [signed-by=/etc/apt/keyrings/yarn.gpg] https://dl.yarnpkg.com/debian/ stable main" > /etc/apt/sources.list.d/yarn.list \
    && curl -sS https://www.postgresql.org/media/keys/ACCC4CF8.asc | gpg --dearmor | tee /etc/apt/keyrings/pgdg.gpg >/dev/null \
    && echo "deb [signed-by=/etc/apt/keyrings/pgdg.gpg] http://apt.postgresql.org/pub/repos/apt noble-pgdg main" > /etc/apt/sources.list.d/pgdg.list \
    && apt-get update \
    && apt-get install -y yarn \
    && apt-get install -y postgresql-client-$POSTGRES_VERSION \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN setcap "cap_net_bind_service=+ep" /usr/bin/php8.4

RUN userdel -r ubuntu
RUN groupadd --force -g $WWWGROUP sail
RUN useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u 1337 sail
RUN git config --global --add safe.directory /var/www/html

# Create start-container script
RUN echo '#!/usr/bin/env bash' > /usr/local/bin/start-container && \
    echo 'if [ "$SUPERVISOR_PHP_USER" != "root" ] && [ "$SUPERVISOR_PHP_USER" != "sail" ]; then' >> /usr/local/bin/start-container && \
    echo '    echo "You should set SUPERVISOR_PHP_USER to either '\''sail'\'' or '\''root'\''."' >> /usr/local/bin/start-container && \
    echo '    exit 1' >> /usr/local/bin/start-container && \
    echo 'fi' >> /usr/local/bin/start-container && \
    echo 'if [ ! -z "$WWWUSER" ]; then' >> /usr/local/bin/start-container && \
    echo '    usermod -u $WWWUSER sail' >> /usr/local/bin/start-container && \
    echo 'fi' >> /usr/local/bin/start-container && \
    echo 'if [ ! -d /.composer ]; then' >> /usr/local/bin/start-container && \
    echo '    mkdir /.composer' >> /usr/local/bin/start-container && \
    echo 'fi' >> /usr/local/bin/start-container && \
    echo 'chmod -R ugo+rw /.composer' >> /usr/local/bin/start-container && \
    echo 'if [ $# -gt 0 ]; then' >> /usr/local/bin/start-container && \
    echo '    if [ "$SUPERVISOR_PHP_USER" = "root" ]; then' >> /usr/local/bin/start-container && \
    echo '        exec "$@"' >> /usr/local/bin/start-container && \
    echo '    else' >> /usr/local/bin/start-container && \
    echo '        exec gosu $WWWUSER "$@"' >> /usr/local/bin/start-container && \
    echo '    fi' >> /usr/local/bin/start-container && \
    echo 'else' >> /usr/local/bin/start-container && \
    echo '    exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf' >> /usr/local/bin/start-container && \
    echo 'fi' >> /usr/local/bin/start-container

# Create supervisord config
RUN echo '[supervisord]' > /etc/supervisor/conf.d/supervisord.conf && \
    echo 'nodaemon=true' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo 'user=root' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo 'logfile=/var/log/supervisor/supervisord.log' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo 'pidfile=/var/run/supervisord.pid' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo '' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo '[program:php]' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo 'command=%(ENV_SUPERVISOR_PHP_COMMAND)s' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo 'user=%(ENV_SUPERVISOR_PHP_USER)s' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo 'environment=LARAVEL_SAIL="1"' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo 'stdout_logfile=/dev/stdout' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo 'stdout_logfile_maxbytes=0' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo 'stderr_logfile=/dev/stderr' >> /etc/supervisor/conf.d/supervisord.conf && \
    echo 'stderr_logfile_maxbytes=0' >> /etc/supervisor/conf.d/supervisord.conf

# Create PHP config
RUN echo '[PHP]' > /etc/php/8.4/cli/conf.d/99-sail.ini && \
    echo 'post_max_size = 100M' >> /etc/php/8.4/cli/conf.d/99-sail.ini && \
    echo 'upload_max_filesize = 100M' >> /etc/php/8.4/cli/conf.d/99-sail.ini && \
    echo 'variables_order = EGPCS' >> /etc/php/8.4/cli/conf.d/99-sail.ini && \
    echo 'pcov.directory = .' >> /etc/php/8.4/cli/conf.d/99-sail.ini
RUN chmod +x /usr/local/bin/start-container

EXPOSE 80/tcp

ENTRYPOINT ["start-container"]
