<?php

use Symfony\Component\HttpFoundation\Request;
use BilletSimpleAlaska\Domain\Comment;
use BilletSimpleAlaska\Domain\Ticket;
use BilletSimpleAlaska\Domain\User;
use BilletSimpleAlaska\Form\Type\CommentType;
use BilletSimpleAlaska\Form\Type\TicketType;
use BilletSimpleAlaska\Form\Type\UserType;

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

// Administration page
$app->get('/admin', function () use ($app) {
    $tickets = $app['dao.ticket']->findAll();
    $comments = $app['dao.comment']->findAll();
    $users = $app['dao.user']->findAll();

    return $app['twig']->render('admin.html.twig', array(
        'tickets' => $tickets,
        'comments' => $comments,
        'users' => $users
    ));
})->bind('admin');

// Add a new ticket
$app->match('/admin/ticket/add', function(Request $request) use ($app) {
    $ticket = new Ticket();
    $ticketForm = $app['form.factory']->create(TicketType::class, $ticket);
    $ticketForm->handleRequest($request);

    if ($ticketForm->isSubmitted() && $ticketForm->isValid())
    {
        $app['dao.ticket']->save($ticket);
        $app['session']->getFlashBag()->add('success', 'Le billet a bien été créé.');
    }

    return $app['twig']->render('ticket_form.html.twig', array(
        'title' => 'Nouveau billet',
        'ticketForm' => $ticketForm->createView()));
})->bind('admin_ticket_add');

// Edit an existing ticket
$app->match('/admin/ticket/{id}/edit', function($id, Request $request) use ($app) {
    $ticket = $app['dao.ticket']->find($id);
    $ticketForm = $app['form.factory']->create(TicketType::class, $ticket);
    $ticketForm->handleRequest($request);

    if ($ticketForm->isSubmitted() && $ticketForm->isValid())
    {
        $app['dao.ticket']->save($ticket);
        $app['session']->getFlashBag()->add('success', 'Le billet a bien été mis à jour.');
    }

    return $app['twig']->render('ticket_form.html.twig', array(
        'title' => 'Edition du billet',
        'ticketForm' => $ticketForm->createView()));
})->bind('admin_ticket_edit');

// Remove a ticket
$app->get('/admin/ticket/{id}/delete', function($id, Request $request) use ($app) {
    // Delete all associated comments
    $app['dao.comment']->deleteAllByTicket($id);

    // Delete the ticket
    $app['dao.ticket']->delete($id);
    $app['session']->getFlashBag()->add('success', 'Le billet a bien été supprimé.');

    // Redirect to admin home page
    return $app->redirect($app['url_generator']->generate('admin'));
})->bind('admin_ticket_delete');

// Edit an existing comment
$app->match('/admin/comment/{id}/edit', function($id, Request $request) use ($app) {
    $comment = $app['dao.comment']->find($id);
    $commentForm = $app['form.factory']->create(CommentType::class, $comment);
    $commentForm->handleRequest($request);

    if ($commentForm->isSubmitted() && $commentForm->isValid())
    {
        $app['dao.comment']->save($comment);
        $app['session']->getFlashBag()->add('success', 'Le commentaire a bien été mis à jour.');
    }

    return $app['twig']->render('comment_form.html.twig', array(
        'title' => 'Edition du commentaire',
        'commentForm' => $commentForm->createView()));
})->bind('admin_comment_edit');

// Remove a comment
$app->get('/admin/comment/{id}/delete', function($id, Request $request) use ($app) {
    $app['dao.comment']->delete($id);
    $app['session']->getFlashBag()->add('success', 'Le commentaire a bien été supprimé.');

    // Redirect to admin home page
    return $app->redirect($app['url_generator']->generate('admin'));
})->bind('admin_comment_delete');

// Add a user
$app->match('/admin/user/add', function(Request $request) use ($app) {
    $user = new User();
    $userForm = $app['form.factory']->create(UserType::class, $user);
    $userForm->handleRequest($request);

    if ($userForm->isSubmitted() && $userForm->isValid()) {
        // generate a random salt value
        $salt = substr(md5(time()), 0, 23);
        $user->setSalt($salt);
        $plainPassword = $user->getPassword();

        // find the default encoder
        $encoder = $app['security.encoder.bcrypt'];

        // compute the encoded password
        $password = $encoder->encodePassword($plainPassword, $user->getSalt());
        $user->setPassword($password); 
        $app['dao.user']->save($user);
        $app['session']->getFlashBag()->add('success', 'L\'utilisateur a bien été créé.');
    }

    return $app['twig']->render('user_form.html.twig', array(
        'title' => 'Nouvel utilisateur',
        'userForm' => $userForm->createView()));
})->bind('admin_user_add');

// Edit an existing user
$app->match('/admin/user/{id}/edit', function($id, Request $request) use ($app) {
    $user = $app['dao.user']->find($id);
    $userForm = $app['form.factory']->create(UserType::class, $user);
    $userForm->handleRequest($request);
    if ($userForm->isSubmitted() && $userForm->isValid()) {
        $plainPassword = $user->getPassword();

        // find the encoder for the user
        $encoder = $app['security.encoder_factory']->getEncoder($user);

        // compute the encoded password
        $password = $encoder->encodePassword($plainPassword, $user->getSalt());
        $user->setPassword($password); 
        $app['dao.user']->save($user);
        $app['session']->getFlashBag()->add('success', 'L\'utilisateur a bien été modifié.');
    }

    return $app['twig']->render('user_form.html.twig', array(
        'title' => 'Edition du profil utilisateur',
        'userForm' => $userForm->createView()));
})->bind('admin_user_edit');

// Remove a user
$app->get('/admin/user/{id}/delete', function($id, Request $request) use ($app) {
    // Delete all associated comments
    $app['dao.comment']->deleteAllByUser($id);

    // Delete the user
    $app['dao.user']->delete($id);
    $app['session']->getFlashBag()->add('success', 'L\'utilisateur a bien été supprimé.');

    // Redirect to admin home page
    return $app->redirect($app['url_generator']->generate('admin'));
})->bind('admin_user_delete');