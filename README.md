# Генератор Sitemap
Библиотека сканирует страницы сайта, создает карту сайта, и сохраняет с помощью FTP.

## Требования
* PHP >= 5.4
* в `php.ini` включить `phar.readonly = 0;`

## Сборка в Phar
```
php -f phar.php
```
В корневой папке появится файл `sitemap-generator.phar`.

## Запуск 

```
php -f sitemap-generator.phar sites.ini
```

Также можно запускать библиотеку без сборки в Phar, но такой вариант менее компактный и удобный в хранении:
```
php -f index.php sites.ini
```

## Пример ini файла
```ini
[example.com]
    ftp_host = 8.8.8.8
    ftp_login = login
    ftp_password = password
    ftp_sitemap_path = www/sitemap.xml

[example2.com]
    ftp_host = 9.9.9.9
    ftp_login = login
    ftp_password = password
    ftp_sitemap_path = public_html/sitemap.xml
```

### Параметры
**Обязательные:**

* `[Домен]` - является заголовком секции;
* `ftp_login` - логин;
* `ftp_password` - пароль;
* `ftp_sitemap_path` - путь до файла от корня FTP.

**Необязательные:**

* `ftp_host` - по умолчанию равен домену. Рекомендуется указывать явно;
* `ftp_port` - если ftp сервер висит на нестандартном порту. По умолчанию используется 21.
