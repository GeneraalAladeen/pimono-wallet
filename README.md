
## About Pimono

This is a simple wallet application built with laravel 12 for a technical test. The application uses Laravel Sanctum to handle Authentication


## Installation

The application is wrapped with a docker container. Ensure docker-compose/docker is installed and running on system before running the command. To install docker visit `https://docs.docker.com/desktop/`. 

Clone the repo

    git clone https://github.com/GeneraalAladeen/pimono-wallet.git

Switch to repo folder

    cd pimono-wallet

install php and run composer

    docker-compose run --rm base_php composer i

Copy the environment file:

    cp .env.example .env

Real time update. Update the following env. I have provided a sandbox pusher account credential for the sake of simplicity but feel free to use yours.

    PUSHER_APP_ID=1238668
    PUSHER_APP_KEY=b5c957260fd11205fc84
    PUSHER_APP_SECRET=f13a52431649e0c74de7

    BROADCAST_CONNECTION=pusher

Setup Laravel

    docker-compose run --rm base_php php artisan key:gen

    docker-compose up -d database_server

    docker-compose run --rm base_php php artisan migrate --seed

    docker-compose up -d
    (note: if error is encountered at any point,  stop other docker containers running and try again)

The application will be served on http://localhost/ .

Real time update. Update the following env. I have provided a sandbox pusher account credential for the sake of simplicity but feel free to use yours.

    PUSHER_APP_ID=1238668
    PUSHER_APP_KEY=b5c957260fd11205fc84
    PUSHER_APP_SECRET=f13a52431649e0c74de7

    BROADCAST_CONNECTION=pusher

## Testing

Copy the environment file:

    cp .env.example .env.testing

Update the database name in .env.testing

    DB_DATABASE=laravel_test

Enter docker container

    docker-compose exec webserver bash -l

Setup test databse

    php artisan key:gen --env=testing

    php artisan migrate --env=testing

You'll get a message saying

    The database 'laravel_test' does not exist on the 'mysql' connection.

    Would you like to create it? (yes/no) [yes]

Yes is selected by default so just accept.

To test code coverage, while still inside the docker container run:

    php artisan test --coverage --min=80


