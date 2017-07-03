<?php

use Symfony\Component\HttpFoundation\Request;
use BilletSimpleAlaska\Domain\Comment;
use BilletSimpleAlaska\Domain\Ticket;
use BilletSimpleAlaska\Domain\User;
use BilletSimpleAlaska\Form\Type\CommentType;
use BilletSimpleAlaska\Form\Type\TicketType;
use BilletSimpleAlaska\Form\Type\UserType;
use BilletSimpleAlaska\Form\Type\RegisterType;

// Home page
$app->get('/', function () use ($app) {
    $tickets = $app['dao.ticket']->findAll();

	return $app['twig']->render('index.html.twig', array('tickets' => $tickets));
})->bind('home');




/**
 * TICKET ROUTES
 */




// Ticket page
// $app->match deals both GET and POST requests ($app->get deals only GET requests)
$app->match('/ticket/{id}', function ($id, Request $request) use ($app) {
    $ticket = $app['dao.ticket']->find($id);
    $comments = $app['dao.comment']->findAllWithChildren($id);
    $commentFormView = NULL; // If no user connected, form view is NULL
    $answerFormView = NULL; // If no user connected, answer form is NULL

    if ($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY'))
    {
    	// A user is fully authenticated : he can add comments
    	// A new Comment object is created, and matched with its Ticket object accordingly
    	$comment = new Comment();
    	$comment->setTicket($ticket);

        // It is a totally new comment so nb report is set to 0
        $comment->setNbReport(0);

    	// User object is then assigned to the new Comment
    	$user = $app['user'];
    	$comment->setAuthor($user);

    	// The comment form is created and is submitted by handleRequest method
    	$commentForm = $app['form.factory']->create(CommentType::class, $comment);
    	$commentForm->handleRequest($request);

    	// If the comment form is submitted and its data valid, CommentDAO is used to save comment in db, and a success message is created
    	if ($commentForm->isSubmitted() AND $commentForm->isValid())
    	{
    		$app['dao.comment']->save($comment);
    		$app['session']->getFlashBag()->add('success', 'Votre commentaire a été ajouté avec succès.');

            // Redirection to the ticket page to refresh the page and see the comment add
            return $app->redirect($app['url_generator']->generate('ticket', array('id' => $comment->getTicket()->getId())));
    	}

    	$commentFormView = $commentForm->createView();
    }

    // Add comment view
	return $app['twig']->render('ticket.html.twig', array(
		'ticket' => $ticket,
		'comments' => $comments,
		'commentForm' => $commentFormView
	));
})->bind('ticket');

// Comment answer treatment
$app->match('/commentAnswer/{ticketId}/{parentId}', function($ticketId, $parentId, Request $request) use ($app) {
    $answerContent = (isset($_POST['answerContent'])) ? $_POST['answerContent'] : '';
    $ticket = $app['dao.ticket']->find($ticketId);
    $author = $app['user'];
    $parent = $app['dao.comment']->find($parentId);


    if ($answerContent !== '')
    {
        $answer = new Comment();
        $answer->setTicket($ticket);
        $answer->setAuthor($author);
        $answer->setContent($answerContent);
        $answer->setParentId($parentId);
        $answer->setDepth(($parent->getDepth() + 1));
        $answer->setNbReport(0);

        $app['dao.comment']->save($answer);
        $app['session']->getFlashBag()->add('success', 'Votre commentaire a été ajouté avec succès.');
    }

    // Redirect to ticket page
    return $app->redirect($app['url_generator']->generate('ticket', array('id' => $parent->getTicket()->getId())));
})->bind('answer_treatment');

// Report a comment abuse
$app->get('/comment/{id}/report', function($id, Request $request) use ($app) {
    $comment = $app['dao.comment']->find($id);
    $app['dao.comment']->reportComment($id);
    $app['session']->getFlashBag()->add('success', 'Le commentaire a bien été signalé. Merci de votre retour.');

    // Redirect to ticket page
    return $app->redirect($app['url_generator']->generate('ticket', array('id' => $comment->getTicket()->getId())));
})->bind('report_comment_abuse');




/**
 * LOGIN ROUTES
 */




// Login form
$app->get('/login', function (Request $request) use ($app) {
	return $app['twig']->render('login.html.twig', array(
		'error'         => $app['security.last_error']($request),
		'last_username' => $app['session']->get('_security.last_username'),
	));
})->bind('login');

// Register form
$app->match('/register', function(Request $request) use ($app) {
        // Create a new User object and set its role to 'ROLE_USER'
    $user = new User();
    $user->setRole('ROLE_USER');

    $registerForm = $app['form.factory']->create(RegisterType::class, $user);
    $registerForm->handleRequest($request);

    if ($registerForm->isSubmitted() && $registerForm->isValid()) {
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

    return $app['twig']->render('register.html.twig', array(
        'title' => 'Inscription',
        'registerForm' => $registerForm->createView()));
})->bind('register');




/**
 * ADMINISTRATION ROUTES
 */




// Administration page
$app->get('/admin', function () use ($app) {
    // Access to this page can be only granted to admin role
    if ($app['security.authorization_checker']->isGranted('ROLE_ADMIN'))
    {
        $tickets = $app['dao.ticket']->findAll();
        $comments = $app['dao.comment']->findAll();
        $commentsReported = $app['dao.comment']->findAllByNbReport();
        $users = $app['dao.user']->findAll();

        return $app['twig']->render('admin.html.twig', array(
            'tickets' => $tickets,
            'comments' => $comments,
            'commentsReported' => $commentsReported,
            'users' => $users
        ));
    }
    else
    {
        $message = 'Vous n\'avez pas accès à cette page';

        return $app['twig']->render('error.html.twig', array('message' => $message));
    }
})->bind('admin');

// Add a new ticket
$app->match('/admin/ticket/add', function(Request $request) use ($app) {
    // Access to this page can be only granted to admin role
    if ($app['security.authorization_checker']->isGranted('ROLE_ADMIN'))
    {
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
    }
    else
    {
        $message = 'Vous n\'avez pas accès à cette page';

        return $app['twig']->render('error.html.twig', array('message' => $message));
    }
})->bind('admin_ticket_add');

// Edit an existing ticket
$app->match('/admin/ticket/{id}/edit', function($id, Request $request) use ($app) {
    // Access to this page can be only granted to admin role
    if ($app['security.authorization_checker']->isGranted('ROLE_ADMIN'))
    {
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
    }
    else
    {
        $message = 'Vous n\'avez pas accès à cette page';

        return $app['twig']->render('error.html.twig', array('message' => $message));
    }
})->bind('admin_ticket_edit');

// Remove a ticket
$app->get('/admin/ticket/{id}/delete', function($id, Request $request) use ($app) {
    // Access to this page can be only granted to admin role
    if ($app['security.authorization_checker']->isGranted('ROLE_ADMIN'))
    {
        // Delete all associated comments
        $app['dao.comment']->deleteAllByTicket($id);

        // Delete the ticket
        $app['dao.ticket']->delete($id);
        $app['session']->getFlashBag()->add('success', 'Le billet a bien été supprimé.');

        // Redirect to admin home page
        return $app->redirect($app['url_generator']->generate('admin'));
    }
    else
    {
        $message = 'Vous n\'avez pas accès à cette page';

        return $app['twig']->render('error.html.twig', array('message' => $message));
    }
})->bind('admin_ticket_delete');

// Edit an existing comment
$app->match('/admin/comment/{id}/edit', function($id, Request $request) use ($app) {
    // Access to this page can be only granted to admin role
    if ($app['security.authorization_checker']->isGranted('ROLE_ADMIN'))
    {
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
    }
    else
    {
        $message = 'Vous n\'avez pas accès à cette page';

        return $app['twig']->render('error.html.twig', array('message' => $message));
    }
})->bind('admin_comment_edit');

// Reinitialize number of reports for a comment
$app->get('/admin/comment/{id}/reinit', function($id, Request $request) use ($app) {
    // Access to this page can be only granted to admin role
    if ($app['security.authorization_checker']->isGranted('ROLE_ADMIN'))
    {
        // Get comment object then put number of report to 0
        $comment = $app['dao.comment']->find($id);
        $comment->setNbReport(0);

        // Then update the comment line in db
        $app['dao.comment']->save($comment);
        $app['session']->getFlashBag()->add('success', 'Le commentaire n\'est plus signalé.');

        // Redirect to admin home page
        return $app->redirect($app['url_generator']->generate('admin'));
    }
    else
    {
        $message = 'Vous n\'avez pas accès à cette page';

        return $app['twig']->render('error.html.twig', array('message' => $message));
    }
})->bind('admin_comment_reinit');

// Remove a comment and its children
$app->get('/admin/comment/{id}/delete', function($id, Request $request) use ($app) {
    // Access to this page can be only granted to admin role
    if ($app['security.authorization_checker']->isGranted('ROLE_ADMIN'))
    {
        // Get comment children with current comment id
        $comment = $app['dao.comment']->find($id);
        // Get its children
        $children = $app['dao.comment']->findChildren($id);

        if ($children)
        {
            // Get parent's children and delete them
            foreach ($children as $child)
            {
                // Get child's children and delete them
                $childrenChildren = $app['dao.comment']->findChildren($child->getId());

                if ($childrenChildren)
                {
                    foreach ($childrenChildren as $childrenChild)
                    {
                        $app['dao.comment']->delete($childrenChild->getId());
                    }
                }

                $app['dao.comment']->delete($child->getId());
            }
        }

        // Then, current comment is deleted
        $app['dao.comment']->delete($id);
        $app['session']->getFlashBag()->add('success', 'Le commentaire a bien été supprimé.');

        // Redirect to admin home page
        return $app->redirect($app['url_generator']->generate('admin'));
    }
    else
    {
        $message = 'Vous n\'avez pas accès à cette page';

        return $app['twig']->render('error.html.twig', array('message' => $message));
    }
})->bind('admin_comment_delete');

// Add a user
$app->match('/admin/user/add', function(Request $request) use ($app) {
    // Access to this page can be only granted to admin role
    if ($app['security.authorization_checker']->isGranted('ROLE_ADMIN'))
    {
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
    }
    else
    {
        $message = 'Vous n\'avez pas accès à cette page';

        return $app['twig']->render('error.html.twig', array('message' => $message));
    }
})->bind('admin_user_add');

// Edit an existing user
$app->match('/admin/user/{id}/edit', function($id, Request $request) use ($app) {
    // Access to this page can be only granted to admin role
    if ($app['security.authorization_checker']->isGranted('ROLE_ADMIN'))
    {
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
    }
    else
    {
        $message = 'Vous n\'avez pas accès à cette page';

        return $app['twig']->render('error.html.twig', array('message' => $message));
    }
})->bind('admin_user_edit');

// Remove a user
$app->get('/admin/user/{id}/delete', function($id, Request $request) use ($app) {
    // Access to this page can be only granted to admin role
    if ($app['security.authorization_checker']->isGranted('ROLE_ADMIN'))
    {
        // Delete all associated comments
        $app['dao.comment']->deleteAllByUser($id);

        // Delete the user
        $app['dao.user']->delete($id);
        $app['session']->getFlashBag()->add('success', 'L\'utilisateur a bien été supprimé.');

        // Redirect to admin home page
        return $app->redirect($app['url_generator']->generate('admin'));
    }
    else
    {
        $message = 'Vous n\'avez pas accès à cette page';

        return $app['twig']->render('error.html.twig', array('message' => $message));
    }
})->bind('admin_user_delete');