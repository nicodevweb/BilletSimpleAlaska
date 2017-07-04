<?php

namespace BilletSimpleAlaska\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use BilletSimpleAlaska\Domain\Comment;
use BilletSimpleAlaska\Domain\User;
use BilletSimpleAlaska\Form\Type\CommentType;
use BilletSimpleAlaska\Form\Type\RegisterType;

class HomeController
{
	/**
	 * Home page controller.
	 *
	 * @param Ticket $app Silex application
	 */

	public function indexAction(Application $app)
	{
		$tickets = $app['dao.ticket']->findAll();

		return $app['twig']->render('index.html.twig', array('tickets' => $tickets));
	}


	/**
	 * Ticket controller
	 */

	/**
	 * Ticket page controller.
	 *
	 * @param integer $id Ticket id
	 * @param Request $request Incoming request
	 * @param Application $app Silex application
	 */

	public function ticketAction($id, Request $request, Application $app)
	{
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
	}

	/**
	 * Comment answer controller.
	 *
	 * @param integer $ticketId Ticket id
	 * @param integer $parentId Comment parent id
	 * @param Request $request Incoming request
	 * @param Application $app Silex application
	 */

	public function answerCommentAction($ticketId, $parentId, Request $request, Application $app)
	{
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
	}

	/**
	 * Report comment controller.
	 *
	 * @param $id Comment id
	 * @param Request $request Incoming request
	 * @param Application $app Silex application
	 */

	public function reportCommentAction($id, Request $request, Application $app)
	{
		$comment = $app['dao.comment']->find($id);
	    $app['dao.comment']->reportComment($id);
	    $app['session']->getFlashBag()->add('success', 'Le commentaire a bien été signalé. Merci de votre retour.');

	    // Redirect to ticket page
	    return $app->redirect($app['url_generator']->generate('ticket', array('id' => $comment->getTicket()->getId())));
	}

	/**
	 * Login controller
	 */

	/**
	 * Login page controller.
	 *
	 * @param Request $request Incoming request
	 * @param Application $app Silex application
	 */

	public function loginAction(Request $request, Application $app)
	{
		return $app['twig']->render('login.html.twig', array(
			'error'         => $app['security.last_error']($request),
			'last_username' => $app['session']->get('_security.last_username'),
		));
	}

	/**
	 * Registration page controller.
	 *
	 * @param Request $request Incoming request
	 * @param Application $app Silex application
	 */

	public function registerAction(Request $request, Application $app)
	{
		// Create a new User object and set its role to 'ROLE_USER'
	    $user = new User();
	    $user->setRole('ROLE_USER');

	    $registerForm = $app['form.factory']->create(RegisterType::class, $user);
	    $registerForm->handleRequest($request);

	    if ($registerForm->isSubmitted() && $registerForm->isValid())
	    {
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
	}


}