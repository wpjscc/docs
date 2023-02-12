
## Docs

文档系统，支持多语言

## install

github
```
git clone git@github.com:wpjscc/docs.git ~/docs
cd docs
composer install
cp .env.example .env
php artisan key:gene
php artisan winter:up
php artisan serve
```

composer

```
composer create-project wpjscc/docs docs dev-master -vvv
cd docs
cp .env.example .env
php artisan key:gene
php artisan winter:up
php artisan serve
```

## docker

git clone git@github.com:wpjscc/docs.git ~/docs

```

docker run -p 8000:8000 -v ~/docs:/www --rm -it wpjscc/php:8.0.9-fpm-alpine3.13 sh

cp .env.example .env
php artisan key:gene
php artisan winter:up
php artisan serve

```


## visit

http://127.0.0.1:8000


## ref

* https://github.com/wintercms/wn-nabu-theme
* https://github.com/wintercms/docs
