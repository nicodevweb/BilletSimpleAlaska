<?php

// Doctrine (db)
$app['db.options'] = array(
    'driver'   => 'pdo_mysql',
    'charset'  => 'utf8',
    'host'     => 'localhost',
    'port'     => '3306',
    'dbname'   => 'billetalaska',
    'user'     => 'billetalaska_user',
    'password' => 'secret',
);

// define log level
$app['monolog.level'] = 'WARNING';