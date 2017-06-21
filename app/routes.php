<?php

// Home page
$app->get('/', function () use ($app) {
    $tickets = $app['dao.ticket']->findAll();
	return $app['twig']->render('index.html.twig', array('tickets' => $tickets));
})->bind('home');