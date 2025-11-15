
## About Pimono

This is a simple wallet application built with laravel 12 for a technical test. The application uses Laravel Sanctum to handle Authentication


## Installation

The application is wrapped with a docker container. Ensure docker-compose/docker is installed and running on system before running the command.

install php and run composer

    docker-compose run --rm base_php composer i

Copy the environment file:

    cp .env.example .env

Setup Laravel

    docker-compose run --rm base_php php artisan key:gen

    docker-compose up -d database_server

    docker-compose run --rm base_php php artisan migrate --seed

    docker-compose up -d

To enter webserver (optional)

    docker-compose exec webserver bash -l

The application will be served on http://localhost/ .

### Real time update
Update the following env. I have provided a sandbox pusher account credential for the sake of simplicity but feel free to use yours.

    PUSHER_APP_ID=1238668
    PUSHER_APP_KEY=b5c957260fd11205fc84
    PUSHER_APP_SECRET=f13a52431649e0c74de7

## Testing

Copy the environment file:

    cp .env.example .env.testing

Update the database name in .env.testing

    DB_DATABASE=laravel_test

To test code coverage run:

    php artisan test --coverage --min=80


