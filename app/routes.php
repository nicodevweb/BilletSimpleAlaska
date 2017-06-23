<?php

use Symfony\Component\HttpFoundation\Request;

// Home page
$app->get('/', function () use ($app) {
    $tickets = $app['dao.ticket']->findAll();
	return $app['twig']->render('index.html.twig', array('tickets' => $tickets));
})->bind('home');

// Ticket page
$app->get('/ticket/{id}', function ($id) use ($app) {
    $ticket = $app['dao.ticket']->find($id);
    $comments = $app['dao.comment']->findAllByTicket($id);
	return $app['twig']->render('ticket.html.twig', array('ticket' => $ticket, 'comments' => $comments));
})->bind('ticket');

// Login form
$app->get('/login', function (Request $request) use ($app) {
	return $app['twig']->render('login.html.twig', array(
		'error'         => $app['security.last_error']($request),
		'last_username' => $app['session']->get('_security.last_username'),
	));
})->bind('login');