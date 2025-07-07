##### Symfony version - 7.3.1
##### PHP -  8.2.12

Install Symfony CLI:
https://symfony.com/download

Installation steps:
`git clone <repository-url>`
`cd employee-management-system`
`composer install`
`cp .env.example .env`
configure database connection
`php bin/console doctrine:database:create`
`php bin/console doctrine:migrations:migrate`
`php bin/console doctrine:fixtures:load`
`symfony server:start`

Running tests:
`php bin/console doctrine:database:create --env=test`
`php bin/console doctrine:migrations:migrate --env=test`
`php bin/phpunit`
