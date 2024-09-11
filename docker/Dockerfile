FROM robinpmt/php7-fpm

VOLUME /home/www/app
EXPOSE 80 443 1025 6001
# Copy configuration files
ADD docker/local /

# Replace default nginx user and group with IDs, matching current host user (developer)
ARG hostUID=1000
ARG hostGID=1000
ENV hostUID=$hostUID
ENV hostGID=$hostGID
RUN echo "uid:gid=$hostUID:$hostGID" &&\
    oldUID=`id -u nginx` &&\
    deluser nginx &&\
    addgroup -g $hostGID nginx &&\
    adduser -S -u $hostUID -G nginx -h /home/www -s /sbin/nologin nginx &&\
    find /var -user $oldUID -exec chown -v $hostUID:$hostGID {} \;

#### Add more supporting stuff
# Declaration
ENV NPM_HOME_ROOT=/root/.npm
ENV NPM_HOME=/home/www/.npm
ENV CONFIG_HOME=/home/www/.config
ENV COMPOSER_HOME=/home/www/.composer
ENV YARN_HOME=/home/www/.yarn
ENV YARN_HOME_ROOT=/root/.yarn
# ENV YARN_CACHE_FOLDER=/home/www/.cache/yarn
# ENV YARN_GLOBAL_FOLDER=/home/www/.yarn
# Create file/dir
RUN mkdir $NPM_HOME_ROOT
RUN mkdir $NPM_HOME
RUN mkdir $CONFIG_HOME
RUN mkdir $COMPOSER_HOME
RUN mkdir $YARN_HOME_ROOT
RUN mkdir $YARN_HOME
# RUN mkdir $YARN_CACHE_FOLDER
# RUN mkdir $YARN_GLOBAL_FOLDER

# Change owner
RUN chown -v $hostUID:$hostGID $NPM_HOME_ROOT
RUN chown -v $hostUID:$hostGID $NPM_HOME
RUN chown -R $hostUID:$hostGID $CONFIG_HOME
RUN chown -R $hostUID:$hostGID $COMPOSER_HOME
RUN chown -v $hostUID:$hostGID $YARN_HOME_ROOT
RUN chown -v $hostUID:$hostGID $YARN_HOME
# RUN chown -v $hostUID:$hostGID $YARN_CACHE_FOLDER
# RUN chown -v $hostUID:$hostGID $YARN_GLOBAL_FOLDER


RUN apk update && \
    apk add --no-cache supervisor curl nodejs npm && \
    npm install -g yarn


# Copy package.json và yarn.lock vào thư mục làm việc
# WORKDIR /home/www/app
# COPY package.json yarn.lock /home/www/app/
# USER $hostUID
# RUN yarn install

# RUN yarn global add laravel-echo-server@1.6.2

COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
