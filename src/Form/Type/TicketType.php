<?php

namespace BilletSimpleAlaska\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class TicketType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		// @string content = @attr $content \BilletSimpleAlaska\Domain\Billet
		$builder
			->add('title', TextType::class)
			->add('content', TextareaType::class);
	}

	public function getName()
	{
		return 'ticket';
	}
}