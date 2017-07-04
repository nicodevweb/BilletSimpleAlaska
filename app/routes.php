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
$app->get('/', 'BilletSimpleAlaska\Controller\HomeController::indexAction')
->bind('home');




/**
 * TICKET ROUTES
 */




// Ticket page
// $app->match deals both GET and POST requests ($app->get deals only GET requests)
$app->match('/ticket/{id}', 'BilletSimpleAlaska\Controller\HomeController::ticketAction')
->bind('ticket');

// Comment answer treatment
$app->match('/commentAnswer/{ticketId}/{parentId}', 'BilletSimpleAlaska\Controller\HomeController::answerCommentAction')
->bind('answer_treatment');

// Report a comment abuse
$app->get('/comment/{id}/report', 'BilletSimpleAlaska\Controller\HomeController::reportCommentAction')
->bind('report_comment_abuse');




/**
 * LOGIN ROUTES
 */




// Login form
$app->get('/login', 'BilletSimpleAlaska\Controller\HomeController::loginAction')
->bind('login');

// Register form
$app->match('/register', 'BilletSimpleAlaska\Controller\HomeController::registerAction')
->bind('register');




/**
 * ADMINISTRATION ROUTES
 */




// Administration page
$app->get('/admin', 'BilletSimpleAlaska\Controller\AdminController::adminAction')
->bind('admin');

// Add a new ticket
$app->match('/admin/ticket/add', 'BilletSimpleAlaska\Controller\AdminController::addTicketAction')
->bind('admin_ticket_add');

// Edit an existing ticket
$app->match('/admin/ticket/{id}/edit', 'BilletSimpleAlaska\Controller\AdminController::editTicketAction')
->bind('admin_ticket_edit');

// Remove a ticket
$app->get('/admin/ticket/{id}/delete', 'BilletSimpleAlaska\Controller\AdminController::removeTicketAction')
->bind('admin_ticket_delete');

// Edit an existing comment
$app->match('/admin/comment/{id}/edit', 'BilletSimpleAlaska\Controller\AdminController::editCommentAction')
->bind('admin_comment_edit');

// Reinitialize number of reports for a comment
$app->get('/admin/comment/{id}/reinit', 'BilletSimpleAlaska\Controller\AdminController::reinitCommentAction')
->bind('admin_comment_reinit');

// Remove a comment and its children
$app->get('/admin/comment/{id}/delete', 'BilletSimpleAlaska\Controller\AdminController::removeCommentAction')
->bind('admin_comment_delete');

// Add a user
$app->match('/admin/user/add', 'BilletSimpleAlaska\Controller\AdminController::addUserAction')
->bind('admin_user_add');

// Edit an existing user
$app->match('/admin/user/{id}/edit', 'BilletSimpleAlaska\Controller\AdminController::editUserAction')
->bind('admin_user_edit');

// Remove a user
$app->get('/admin/user/{id}/delete', 'BilletSimpleAlaska\Controller\AdminController::removeUserAction')
->bind('admin_user_delete');