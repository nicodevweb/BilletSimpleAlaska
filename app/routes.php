<?php

use Symfony\Component\HttpFoundation\Request;
use BilletSimpleAlaska\Domain\Comment;
use BilletSimpleAlaska\Form\Type\CommentType;

// Home page
$app->get('/', function () use ($app) {
    $tickets = $app['dao.ticket']->findAll();
	return $app['twig']->render('index.html.twig', array('tickets' => $tickets));
})->bind('home');

// Ticket page
// $app->match deals both GET and POST requests ($app->get deals only GET requests)
$app->match('/ticket/{id}', function ($id, Request $request) use ($app) {
    $ticket = $app['dao.ticket']->find($id);
    $commentFormView = NULL; // If no user connected, form view is NULL

    if ($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY'))
    {
    	// A user is fully authenticated : he can add comments
    	// A new Comment object is created, and matched with its Ticket object accordingly
    	$comment = new Comment();
    	$comment->setTicket($ticket);

    	// User object is then assigned to the new Comment
    	$user = $app['user'];
    	$comment->setAuthor($user);

    	// The comment form is created and is submitted by handleRequest method
    	$commentForm = $app['form.factory']->create(CommentType::class, $comment);
    	$commentForm ->handleRequest($request);

    	// If the comment form is submitted and its data valid, CommentDAO is used to save comment in db, and a success message is created
    	if ($commentForm->isSubmitted() AND $commentForm->isValid())
    	{
    		$app['dao.comment']->save($comment);
    		$app['session']->getFlashBag()->add('success', 'Votre commentaire a été ajouté avec succès.');
    	}

    	$commentFormView = $commentForm->createView();
    }
    // Add comment view
    $comments = $app['dao.comment']->findAllByTicket($id);

	return $app['twig']->render('ticket.html.twig', array(
		'ticket' => $ticket,
		'comments' => $comments,
		'commentForm' => $commentFormView
	));
})->bind('ticket');

// Login form
$app->get('/login', function (Request $request) use ($app) {
	return $app['twig']->render('login.html.twig', array(
		'error'         => $app['security.last_error']($request),
		'last_username' => $app['session']->get('_security.last_username'),
	));
})->bind('login');