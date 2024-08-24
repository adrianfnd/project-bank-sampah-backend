#!/bin/bash
git pull
cp .env.production .env
docker build -t banksampah .
docker rm -f banksampah || true
docker run -d --name banksampah --network development --restart always banksampah
# docker exec -it banksampah php artisan migrate
# docker exec -it banksampah php artisan db:seed
# docker exec -it banksampah cat /var/www/html/storage/logs/laravel.log
