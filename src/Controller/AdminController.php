<?php

namespace BilletSimpleAlaska\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use BilletSimpleAlaska\Domain\Ticket;
use BilletSimpleAlaska\Domain\Comment;
use BilletSimpleAlaska\Domain\User;
use BilletSimpleAlaska\Form\Type\TicketType;
use BilletSimpleAlaska\Form\Type\CommentType;
use BilletSimpleAlaska\Form\Type\UserType;

class AdminController
{
	/**
	 * Administration page controller.
	 *
	 * @param Application $app Silex application
	 */

	public function adminAction(Application $app)
	{
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
	        $message = 'Vous n\'avez pas accès à cette page.';

	        return $app['twig']->render('error.html.twig', array('message' => $message));
	    }
	}

	/**
	 * Administration Ticket controllers
	 */

	/**
	 * Administration add ticket controller.
	 *
	 * @param Request $request Incoming request
	 * @param Application $app Silex application
	 */

	public function addTicketAction(Request $request, Application $app)
	{
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
	        $message = 'Vous n\'avez pas accès à cette page.';

	        return $app['twig']->render('error.html.twig', array('message' => $message));
	    }
	}

	/**
	 * Administration edit ticket controller.
	 *
	 * @param integer $id Ticket id
	 * @param Request $request Incoming request
	 * @param Application $app Silex application
	 */

	public function editTicketAction($id, Request $request, Application $app)
	{
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
	        $message = 'Vous n\'avez pas accès à cette page.';

	        return $app['twig']->render('error.html.twig', array('message' => $message));
	    }
	}

	/**
	 * Administration remove ticket controller.
	 *
	 * @param integer $id Ticket id
	 * @param Request $request Incoming request
	 * @param Application $app Silex application
	 */

	public function removeTicketAction($id, Request $request, Application $app)
	{
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
	        $message = 'Vous n\'avez pas accès à cette page.';

	        return $app['twig']->render('error.html.twig', array('message' => $message));
	    }
	}

	/**
	 * Administration comment controllers
	 */

	/**
	 * Administration edit comment controller.
	 *
	 * @param integer $id Comment id
	 * @param Request $request Incoming request
	 * @param Application $app Silex application
	 */

	public function editCommentAction($id, Request $request, Application $app)
	{
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
	        $message = 'Vous n\'avez pas accès à cette page.';

	        return $app['twig']->render('error.html.twig', array('message' => $message));
	    }
	}

	/**
	 * Administration reinitialize comment's reports controller.
	 *
	 * @param integer $id Comment id
	 * @param Request $request Incoming request
	 * @param Application $app Silex application
	 */

	public function reinitCommentAction($id, Request $request, Application $app)
	{
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
	        $message = 'Vous n\'avez pas accès à cette page.';

	        return $app['twig']->render('error.html.twig', array('message' => $message));
	    }
	}

	/**
	 * Administration remove comment and its children controller.
	 *
	 * @param integer $id Comment id
	 * @param Request $request Incoming request
	 * @param Application $app Silex application
	 */

	public function removeCommentAction($id, Request $request, Application $app)
	{
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
	        $message = 'Vous n\'avez pas accès à cette page.';

	        return $app['twig']->render('error.html.twig', array('message' => $message));
	    }
	}

	/**
	 * Administration user controllers
	 */

	/**
	 * Administration add user controller.
	 *
	 * @param Request $request Incoming request
	 * @param Application $app Silex application
	 */

	public function addUserAction(Request $request, Application $app)
	{
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
	        $message = 'Vous n\'avez pas accès à cette page.';

	        return $app['twig']->render('error.html.twig', array('message' => $message));
	    }
	}

	/**
	 * Administration edit user controller.
	 *
	 * @param $id User id
	 * @param Request $request Incoming request
	 * @param Application $app Silex application
	 */

	public function editUserAction($id, Request $request, Application $app)
	{
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
	        $message = 'Vous n\'avez pas accès à cette page.';

	        return $app['twig']->render('error.html.twig', array('message' => $message));
	    }
	}

	/**
	 * Administration remove user controller.
	 *
	 * @param $id User id
	 * @param Request $request Incoming request
	 * @param Application $app Silex application
	 */

	public function removeUserAction($id, Request $request, Application $app)
	{
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
	        $message = 'Vous n\'avez pas accès à cette page.';

	        return $app['twig']->render('error.html.twig', array('message' => $message));
	    }
	}
}