<?php

use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;

// Register global error and exception handlers
ErrorHandler::register();
ExceptionHandler::register();

// Register service providers.
$app->register(new Silex\Provider\DoctrineServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));
$app->register(new Silex\Provider\AssetServiceProvider(), array(
	'assets.version' => 'v1'
));

// Register services.
$app['dao.ticket'] = function ($app) {
    return new BilletSimpleAlaska\DAO\TicketDAO($app['db']);
};

$app['dao.comment'] = function ($app) {
	$commentDAO = new BilletSimpleAlaska\DAO\CommentDAO($app['db']);
	$commentDAO->setTicketDAO($app['dao.ticket']);
	return $commentDAO;
};