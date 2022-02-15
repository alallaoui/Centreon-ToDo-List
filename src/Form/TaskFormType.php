<?php

namespace App\Form;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class TaskFormType extends AbstractType
{
    protected EntityManagerInterface $entityManager;

    /**
     * TaskFormType constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'constraints'=>[
                    new NotBlank(['message' => 'Veuillez indiquer un titre.']),
                    new Regex([
                        'pattern' => '/^[a-z0-9-_\.\,]+$/i',
                        'message' => 'Le titre ne peut contenir que des caractères alphanumériques, undescores, points et virgules.'
                    ]),
                    new length([
                        'max'=> 40,
                        'maxMessage' => 'Le titre ne peut contenir plus de 40 caractères'
                    ])
                ]
            ])
            ->add('description', TextareaType::class, [
                'required'=>false,
                'constraints'=>[
                    new Regex([
                        'pattern' => '/^[a-z0-9-_\.\,]+$/i',
                        'message' => 'La description ne peut contenir que des caractères alphanumériques, undescores, points et virgules.'
                    ]),
                    new Length([
                        'max'=> 500,
                        'maxMessage' => 'La description ne peut contenir plus de 500 caractères'
                    ])
                ]
            ])
            ->add('status', ChoiceType::class, [
                'choices'  => [
                    'A faire' => 'A faire',
                    'En cours' => 'En cours',
                    'Terminée' => 'Terminée',
                ],
            ]);

            $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);

    }

    /**
     * @param FormEvent $event
     * @throws TransportExceptionInterface
     */
    public function onPostSubmit(FormEvent $event)
    {
        $task = $event->getData();
        $form = $event->getForm();
        if ($form->isValid()) {
            if ($form->getConfig()->getOptions()['edit']) {
                $task->setUpdatedOn( New \DateTime());
            }

            $this->entityManager->persist($task);
            $this->entityManager->flush();
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
            'edit' => false,
        ]);
    }
}
