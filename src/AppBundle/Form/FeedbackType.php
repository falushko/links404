<?php

namespace AppBundle\Form;

use AppBundle\Entity\Feedback;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeedbackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
			->add('name', TextType::class, [
				'label' => false,
				'attr' => [
					'class'=>'form-control',
					'placeholder' => 'Name',
				]])

			->add('email', TextType::class, [
				'label' => false,
				'attr' => [
					'class'=>'form-control',
					'placeholder' => 'Email',
				]])

			->add('message', TextareaType::class, [
				'label' => false,
				'attr' => [
					'class'=>'form-control',
					'placeholder' => 'Message',
					'rows' => 4,
				]]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
        	'data_class' => Feedback::class,
			'attr' => [
				'novalidate'=>'novalidate']
		]);
    }
}
