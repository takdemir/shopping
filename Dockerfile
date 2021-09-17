FROM php:7.4-fpm

RUN apt-get clean
RUN rm -rf /var/lib/apt/lists/*
RUN apt-get update
RUN apt-get install -y zlib1g-dev g++ git libicu-dev zip libzip-dev zip
RUN docker-php-ext-install intl opcache pdo pdo_mysql
RUN pecl install apcu
RUN docker-php-ext-enable apcu
RUN docker-php-ext-configure zip
RUN docker-php-ext-install zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony/bin/symfony /usr/local/bin/symfony
#RUN composer install

# Add user for symfony application
#RUN groupadd -g 1000 www
#RUN useradd -u 1000 -ms /bin/bash -g www www
#-m is for creating new usernamed folder under home directory / -u (--UID) userID / -g (--groups) groupID / -s (--shell) gives how to login shell

# Give existing application directory permissions
#RUN chown -R www /var/www/html && chmod -R u+rwx /var/www/html

# Change current user to www
#USER www

EXPOSE 9000
CMD ["php-fpm"]




#FROM - fundemental instructions
#ARG - fundemental instructions
#RUN - configuration instructions
#ADD | COPY - configuration instructions
#ENV - configuration instructions
#CMD - execution instructions
#ENTRYPOINT - execution instructions
#EXPOSE - execution instructions

#ARG CODE_VERSION=16.04
#FROM ubuntu:${CODE_VERSION}
#RUN apt-get update && apt-get install nginx -y && apt-get install apache2 -y && apt-get install -y curl && apt-get clean && rm -rf /var/lib/apt/lists/*
#RUN mkdir /home/Codes
#ENV USER taner
#ENV SHELL /bin/bash
#ENV APACHE_LOG_DIR /var/log/apache2
#EXPOSE 80
#CMD ["nginx","-g"]
#CMD ["/usr/sbin/apache2", "-D", "FOREGROUND"]

#RUN mkdir -p /home/app
#COPY . /home/app
#CMD["node","/home/app/app.js"] means node app.js also CMD["nodemon","bin/www]


#docker image pull nginx:latest nginx:alpine
#docker image inspect <docker image repository:tag>
#docker build -t img_from-env .  // -t is the tag option to name of the image and also . is shows that the Dockerfile is in the present directory
#docker images // to see the images
#docker run -itd --network bridge --name container_run-env -p 8080:80 img_run // to run the container named container_run-env // i is interactive t is terminal and d is detached
#docker ps -a // to see all the container, use -a but only see the running container -a is not neccessary
#docker run --help
#docker exec -it container_run-env bash
#docker stop container_run-env
#docker start, stop
#docker rename <abc> <def>
#docker rm <container-id1> <container-id2> <container-id3>
#docker attach <docker-container-name> or <docker-container-id> // if we exit, container will be stopped
#docker network create --driver bridge my-bridge-name
#docker network create --driver bridge --subnet=192.168.0.0/16 --ip-range=192.168.5.0/24 my-bridge-name-2
#docker network ls
#docker network connect my-bridge-name my-container-name
#docker container inspect my-container-name
#docker network inspect my-bridge-name
#docker network disconnect my-bridge-name my-container-name
#docker volume create volume-name
#docker volume ls --filter="dangling=true" // means list the volumes which are not being mounted to any container
#docker volume inspect volume-name
#docker volume rm volume-name
# cd /var/lib/docker/volumes
#docker-compose up -d
#docker-compose config --services
#docker-compose logs --tail=10
#docker-compose ps
#docker-compose top // running processes
#docker-compose down
#docker-compose stop
#docker exec -it  global-redis sh // docker-cli, keys *, set key value, del key