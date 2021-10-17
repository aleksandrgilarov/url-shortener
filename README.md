# Requirements

* Symfony CLI
* php 8
* PostgreSQL database
* composer

# How to run localy

clone repository
cd to the url-shortener directory
create .env and change DB connection string

```
composer install
```
```
php bin/console doctrine:migrations:migrate
```
```
symfony server:start
```
