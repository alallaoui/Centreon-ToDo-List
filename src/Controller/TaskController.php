<?php

namespace App\Controller;

use App\Exception\ApiException;
use App\Exception\InvalidFormException;
use App\Service\Form\FormErrorsSerializer;
use App\Traits\ControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Task;
use App\Repository\TaskRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Form\TaskFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * Class TaskController
 * @package App\Controller
 * @Route("api/tasks")
 */
class TaskController extends AbstractController
{
    use ControllerTrait;

    /**
     * List all the tasks.
     *
     * @Route("/", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns all the task",
     * )
     * @OA\Tag(name="tasks")
     */
    public function index(TaskRepository $taskRepository, SerializerInterface $serializer)
    {
        try {
            // Liste toutes les tâches + Trie par date de création (plus recente au debut)
            $taskList= $taskRepository->findBy(array(), array('createdAt'=>'desc'));
        } catch (ApiException $e) {
            return $this->jsonBadRequestResponse($e->getData());
        }
        return $this->jsonOkResponse($taskList);
    }

    /** 
     * @Route("/create", name="create_task", methods={"POST"})
     *
     * @OA\Response(
     *     response=200,
     *     description="A new task has been created",
     * ),
     * @OA\Response(
     *     response=500,
     *     description="no task has been created (invalid values, ... )",
     * )
     * @OA\Parameter(
     *     name="form",
     *     in="query",
     *     required=true,
     *     description="Required values before submit",
     *     @Model(type=TaskFormType::class),
     * )
     *
     * @OA\Tag(name="tasks")
     */
    public function create(Request $request, FormErrorsSerializer $formErrorsSerializer)
    {
        try {
            $task = new Task();

            //traitememt formulaire creation de tâches et enregistrement en base de donnée
            $taskForm = $this->createForm(TaskFormType::class, $task);

            //submit du formulaire et retour de la réponse si le fomrulaire est valide
            if ($taskForm->submit($request->query->all(), true)->isValid()) {
                return $this->jsonCreatedResponse($task);
            }

            // INVALID_FORM_EXCEPTION si le formulaire est invalide
            throw new InvalidFormException(
                'INVALID_FORM',
                0,
                $formErrorsSerializer->serialize($taskForm),
                $taskForm
            );

        } catch (ApiException $e) {
            return $this->jsonBadRequestResponse($e->getData());
        }
    }
     /**
      * @Route("/edit/{id}", name="edit_task", methods={"PUT"})
      *
      * @OA\Response(
      *     response=200,
      *     description="task has been modified",
      * ),
      * @OA\Response(
      *     response=500,
      *     description="no task has been created (invalid values, ... )",
      * )
      *
      * @OA\Response(
      *     response=404,
      *     description="Task was not found",
      * )
      * @OA\Parameter(
      *     name="form",
      *     in="query",
      *     required=true,
      *     description="Required values before submit",
      *     @Model(type=TaskFormType::class),
      * )
      * @ParamConverter("address", class="App\Entity\Task")
      * @OA\Tag(name="tasks")
     */
    public function edit(Request $request, FormErrorsSerializer $formErrorsSerializer, Task $task = null)
    {
        if (null === $task) {
            throw new NotFoundHttpException('TASK_NOT_FOUND');
        }

        try {
            //traitememt formulaire creation de tâches et enregistrement en base de donnée
            $taskForm= $this->createForm(TaskFormType::class, $task, ['edit' => true]);

            //submit du formulaire et retour de la réponse si le fomrulaire est valide
            if ($taskForm->submit($request->query->all(), true)->isValid()) {
                return $this->jsonOkResponse($task);
            }

            // INVALID_FORM_EXCEPTION si le formulaire est invalide
            throw new InvalidFormException(
                'INVALID_FORM',
                0,
                $formErrorsSerializer->serialize($taskForm),
                $taskForm
            );
        } catch (ApiException $e) {
            return $this->jsonBadRequestResponse($e->getData());
        }
    }

    /**
     * @Route("/delete/{id}", name="delete_task", methods={"DELETE"})
     *
     * @OA\Response(
     *     response=200,
     *     description="Task has been deleted",
     * ),
     * @OA\Response(
     *     response=500,
     *     description="Internal error while deleteting the task",
     * )
     * @OA\Response(
     *     response=404,
     *     description="Task was not found",
     * )
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="task id to be deleted",
     * )
     * @ParamConverter("address", class="App\Entity\Task")
     * @OA\Tag(name="tasks")
     */
    public function delete(EntityManagerInterface $entityManager, Task $task = null)
    {
        // Not found exception si la tâches n'existe pas
        if (null === $task) {
            throw new NotFoundHttpException('TASK_NOT_FOUND');
        }
        try {
            //Suppression de la tache
            $entityManager->remove($task);
            $entityManager->flush();
        } catch (ApiException $e) {
            return $this->jsonBadRequestResponse($e->getData());
        }

        return $this->jsonNoContentResponse($task);
    }
}


