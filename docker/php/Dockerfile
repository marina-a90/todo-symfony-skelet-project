FROM php:7.2-fpm

RUN apt-get update && apt-get install -y \
    unzip \
    curl \
    wget \
	libfreetype6-dev \
	libjpeg62-turbo-dev \
	libmcrypt-dev \
	libpng-dev \
    git \
    zsh \
    ssh \
    nano

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version

RUN docker-php-ext-install pdo \
    && docker-php-ext-install pdo_mysql \
	&& docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
	&& docker-php-ext-install -j$(nproc) gd \
	&& docker-php-ext-install bcmath \
	&& docker-php-ext-install zip

#### XDebug
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

#### ZSH
RUN chsh -s $(which zsh)
RUN wget https://github.com/robbyrussell/oh-my-zsh/raw/master/tools/install.sh -O - | zsh || true

#### netstat
RUN apt-get update && apt-get install -y \
	net-tools

RUN apt-get update && apt-get install -y ffmpeg

RUN apt-get update && apt-get install -y zlib1g-dev libzmq3-dev \
    && pecl install zmq-beta \
    && docker-php-ext-enable zmq

RUN docker-php-ext-install sysvsem

#### mailer
RUN apt-get update && apt-get install -q -y ssmtp mailutils && rm -rf /var/lib/apt/lists/*
RUN echo "hostname=todo-api.com" > /etc/ssmtp/ssmtp.conf
RUN echo "mailhub=maildev" >> /etc/ssmtp/ssmtp.conf
RUN echo "sendmail_path=sendmail -i -t" >> /usr/local/etc/php/conf.d/php-sendmail.ini
RUN echo "todo-api todo-api.com" >> /etc/hosts

RUN useradd -ms /bin/bash todo

#### SSH
#ADD ./ssh ./ssh
#RUN cp -r ./ssh /root/.ssh
#
#RUN chmod 400 ~/.ssh/id_rsa

WORKDIR /app