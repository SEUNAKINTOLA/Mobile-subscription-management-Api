
cd docker
docker-compose exec php php /var/www/html/artisan queue:restart
docker-compose exec php php /var/www/html/artisan queue:work