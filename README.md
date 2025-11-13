
## About Pimono

This is a simple wallet application built with laravel 12 for a technical test. The application uses Laravel Sanctum to handle Authentication


## Installation

The folder to store caches, configs, mysql data etc. I suggest using a path outside the source code such as somewhere in your home folder like /home/ojie-oriarewo/pimono-config.

DB_PASSWORD=root
DOCKER_CONFIG_FOLDER=/home/ojie-oriarewo/pimono-config

- docker-compose run --rm base_php composer i
- docker-compose run --rm base_php php artisan key:gen
- docker-compose up -d database_server
- docker-compose run --rm base_php php artisan migrate --seed
- docker-compose up -d
- docker-compose exec webserver bash -l

## Testing

To test code coverage run
- ** php artisan test --coverage --min=80**

