create database if not exists billetalaska character set utf8 collate utf8_unicode_ci;
use billetalaska;

grant all privileges on billetalaska.* to 'billetalaska_user'@'localhost' identified by 'secret';