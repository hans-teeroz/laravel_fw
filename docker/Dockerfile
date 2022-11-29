FROM robinpmt/php7-fpm

VOLUME /home/www/app
EXPOSE 80 443
# Copy configuration files
ADD docker/local /

#### Add more supporting stuff
# Declaration
ENV NPM_HOME_ROOT=/root/.npm
ENV NPM_HOME=/home/www/.npm
ENV CONFIG_HOME=/home/www/.config
ENV COMPOSER_HOME=/home/www/.composer
# Create file/dir
RUN mkdir $NPM_HOME_ROOT
RUN mkdir $NPM_HOME
RUN mkdir $CONFIG_HOME
RUN mkdir $COMPOSER_HOME
