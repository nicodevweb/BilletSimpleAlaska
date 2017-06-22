<?php

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