FROM nginx:1.25.5

RUN rm -rf /etc/nginx/sites-enabled

RUN apt-get update
RUN apt-get install php8.2-fpm -y
RUN apt-get install php-sqlite3 -y

RUN service php8.2-fpm start

COPY nginx.conf /etc/nginx/nginx.conf
COPY entrypoint.sh /root/entrypoint.sh

WORKDIR /app
COPY /app /app
RUN chown www-data -R /app

EXPOSE 9090

CMD ["/root/entrypoint.sh"]