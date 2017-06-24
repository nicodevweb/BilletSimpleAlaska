<?php

namespace BilletSimpleAlaska\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CommentType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		// @string content = @attr $content \BilletSimpleAlaska\Domain\Comment
		$builder->add('content', TextareaType::class);
	}

	public function getName()
	{
		return 'comment';
	}
}