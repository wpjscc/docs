

## Docs

文档系统，支持多语言

example https://docs.wpjs.cc

## install

github
```
git clone git@github.com:wpjscc/docs.git ~/docs
cd docs
composer install
cp .env.example .env
php artisan key:gene
php artisan winter:up
# 下载文档并翻译
php artisan doc:process wpjscc.docs.wintercmszhcn
php artisan serve
```

composer

```
composer create-project wpjscc/docs docs dev-master -vvv
cd docs
cp .env.example .env
php artisan key:gene
php artisan winter:up
# 下载文档并翻译
php artisan doc:process wpjscc.docs.wintercmszhcn
php artisan serve
```

## docker

```
git clone git@github.com:wpjscc/docs.git ~/docs
```

```

docker run -p 8000:8000 -v ~/docs:/www --rm -it wpjscc/php:8.0.9-fpm-alpine3.13 sh

cp .env.example .env
php artisan key:gene
php artisan winter:up
# 下载文档并翻译
php artisan doc:process wpjscc.docs.wintercmszhcn
php artisan serve

```


## visit

http://127.0.0.1:8000

http://127.0.0.1:8000/backend





## ref

* https://github.com/wintercms/wn-nabu-theme
* https://github.com/wintercms/docs


## other

build docker image

```
docker build -t registry.cn-shanghai.aliyuncs.com/wpjscc/docs:8.0.9-fpm-alpine3.13 . -f Dockerfile
docker push registry.cn-shanghai.aliyuncs.com/wpjscc/docs:8.0.9-fpm-alpine3.13
```