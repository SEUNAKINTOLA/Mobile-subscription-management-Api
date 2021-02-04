
cd docker
docker-compose build
docker-compose up --build -d

docker-compose exec php php /var/www/html/artisan migrate --force