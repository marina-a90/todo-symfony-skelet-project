<?php

namespace App\Controller\Todo;

use App\Controller\BaseRestController;
use App\Entity\Todo\Todo;
use App\Entity\User\User;
use App\Form\Type\Todo\TodoType;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class TodoRestController extends BaseRestController
{
//    private $todoManager;
//    private $todoRepository;
//
//    public function __construct(EntityManagerInterface $entityManager)
//    {
//        $this->todoManager = $entityManager;
//        $this->todoRepository = $this->todoManager->getRepository(Todo::class);
//    }

    /**
     * Get list of Todos.
     *
     * @Route("/all-todos", methods={"GET"})
     *
     * @SWG\Get(
     *     tags={"Todo"},
     *     summary="Get list of Todos",
     *     description="Get list of Todos",
     *     produces={"application/json"},
     *     @SWG\Tag(name="Todo"),
     *     @SWG\Response(
     *         response=200, description="Success"
     *     ),
     *     @SWG\Response(
     *         response=204, description="No content"
     *     )
     *  )
     *
     * @return Response|View
     */
    public function getTodosAction()
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->forbidden(['message' =>'You do not have permission for the action.']);
        }

        /** @var Todo $todos */
//        $todos = $this->todoRepository->findAll();
        $todos = $this->getEntityManager()->findAll();

        if (!$todos) {
            return $this->notFound(['message' =>'No todos.']);
        }

        return $this->ok($todos, ['list']);
    }

    /**
     * Get list of incomplete Todos.
     *
     * @Route("/todos", methods={"GET"})
     *
     * @SWG\Get(
     *     tags={"Todo"},
     *     summary="Get list of incomplete Todos",
     *     description="Get list of incomplete Todos",
     *     produces={"application/json"},
     *     @SWG\Tag(name="Todo"),
     *     @SWG\Response(
     *         response=200, description="Success"
     *     ),
     *     @SWG\Response(
     *         response=204, description="No content"
     *     )
     *  )
     *
     * @return View
//     * @return Response
     */
    public function getIncompelteTodosAction()
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->forbidden(['message' =>'You do not have permission for the action.']);
        }

        /** @var Todo $todos */
//        $todos = $this->todoRepository->findBy(['isDone' => 0]);
        $todos = $this->getEntityManager()->findIncompleteTodos();

        if (!$todos) {
            return $this->notFound(['message' =>'No incomplete todos.']);
        }

        return $this->ok($todos, ['list']);
//        return new Response($this->json($todos));
    }

    /**
     * Get a Todo.
     *
     * @Route("/todo/{id}", methods={"GET"})
     *
     * @SWG\Get(
     *     tags={"Todo"},
     *     summary="Get a Todo",
     *     description="Get a Todo",
     *     produces={"application/json"},
     *     @SWG\Tag(name="Todo"),
     *     @SWG\Response(
     *         response=200, description="Success"
     *     ),
     *     @SWG\Response(
     *         response=204, description="No content"
     *     )
     *  )
     *
     * @return View
//     * @return Response
     */
    public function getSingleTodoAction($id)
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->forbidden(['message' =>'You do not have permission for the action.']);
        }

        /** @var Todo $todo */
        $todo = $this->getEntityManager()->find($id);
//        $todo = $this->todoRepository->findOneBy(['id' => $id]);
        if (!$todo) {
            return $this->notFound(['message' =>'Todo not found.']);
        }

        return $this->ok($todo, ['list']);
    }


    /**
     * Create todo.
     * @Route("/todo/new", methods={"POST"})
     *
     * @SWG\Post(
     *     tags={"Todo"},
     *     summary="Create new todo",
     *     description="Create new todo.",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *          @Model(type=TodoType::class)
     *     ),
     *     @SWG\Response(
     *         response=403, description="User not authenticated"
     *     ),
     *     @SWG\Response(
     *          response=201,
     *          description="Success",
     *          @SWG\Schema(ref=@Model(type=Todo::class))
     *      ),
     *      @SWG\Response(
     *          response=400, description="Data is invalid"
     *      )
     *  )
     * @param Request $request
     *
     * @return View
     */
    public function postTodoAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->forbidden(['message' =>'You do not have permission for the action.']);
        }

        /** @var Todo $todo */
        $todo = new Todo();

        $form = $this->createForm(TodoType::class, $todo, [
            'method' => Request::METHOD_POST,
            'csrf_protection' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
//            $em = $this->todoManager;
//            $em->persist($todo);
//            $em->flush();
//
//            return $this->ok($todo, ['list']);

            $em = $this->getEntityManager();
            $em->save($todo);

            return $this->ok($todo, ['list']);

//            $data = $form->getData();
//            $em = $this->todoManager;
//            $em->persist($data);
//            $em->flush();

//            return $this->ok($data, ['list']);
        }

        return $this->bad($form);
    }

    /**
     * Edit todo.
     * @Route("/todo/{id}/edit", methods={"PATCH"})
     *
     * @SWG\Patch(
     *     tags={"Todo"},
     *     summary="Edit todo",
     *     description="Edit todo.",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *          @Model(type=TodoType::class)
     *     ),
     *     @SWG\Response(
     *         response=403, description="User not authenticated"
     *     ),
     *     @SWG\Response(
     *          response=201,
     *          description="Success",
     *          @SWG\Schema(ref=@Model(type=Todo::class))
     *      ),
     *      @SWG\Response(
     *          response=400, description="Data is invalid"
     *      )
     *  )
     *
     * @param Request $request
     * @param integer $id
     *
     * @return Response|View
     */
    public function patchTodoAction(Request $request, $id)
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->forbidden(['message' =>'You do not have permission for the action.']);
        }

        /** @var Todo $todo */
        $todo = $this->todoRepository->findOneBy(['id' => $id]);
        if (!$todo) {
            return $this->notFound(['message' =>'Todo not found.']);
        }

        $form = $this->createForm(TodoType::class, $todo, [
            'method' => Request::METHOD_PATCH,
            'csrf_protection' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
//            $em = $this->todoManager;
//            $em->persist($todo);
//            $em->flush();
//
//            return $this->ok($todo, ['list']);

            $em = $this->getEntityManager();
            $em->save($todo);

            return $this->ok($todo, ['list']);

//            $data = $form->getData();
//            $em = $this->todoManager;
//            $em->persist($data);
//            $em->flush();
//
//            return $this->ok($data, ['list']);
        }

        return $this->bad($form);
    }

    /**
     * Done todo toggle.
     *
     * @Route("/todo/{id}/done", methods={"PUT"})
     * @SWG\Put(
     *     tags={"Todo"},
     *     summary="Done/not done todo",
     *     description="Done/not done todo",
     *     produces={"*", "application/json"},
     *      @SWG\Response(
     *         response=204, description="No content"
     *     ),
     *      @SWG\Response(
     *          response=404, description="Todo not found"
     *      ),
     *      @SWG\Tag(name="Todo"),
     *  )
     * @param Request $request
     *
     * @return View
     */
    public function putToggleIsDoneTodoAction(Request $request, $id)
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->forbidden(['message' =>'You do not have permission for the action.']);
        }

        /** @var Todo $todo */
        $todo = $this->todoRepository->find($id);
        if (!$todo) {
            return $this->notFound(['message' =>'Todo not found.']);
        }

        $todo->setIsDone( !$todo->getIsDone() );

        $em = $this->getEntityManager();
        $em->save($todo);


//        $em = $this->todoManager;
//        $em->persist($todo);
//        $em->flush();

        return $this->ok($todo, ['list']);
    }


    /**
     * Delete todo.
     *
     * @Route("/todo/{id}", methods={"DELETE"})
     * @SWG\Delete(
     *     tags={"Todo"},
     *     summary="Delete todo",
     *     description="Delete todo",
     *     produces={"*", "application/json"},
     *     @SWG\Response(
     *         response=403, description="User not authenticated"
     *     ),
     *      @SWG\Response(
     *         response=204, description="No content"
     *     ),
     *      @SWG\Response(
     *          response=404, description="Todo with given id is not found"
     *      ),
     *      @SWG\Tag(name="Todo"),
     *      @SWG\SecurityScheme(
     *         securityDefinition="Bearer",
     *         type="apiKey",
     *         name="Authorization",
     *         in="header"
     *     )
     *  )
     * @param Request $request
     * @param integer $id
     *
     * @return Response|View
     */
    public function deleteTodoAction(Request $request, $id)
    {
        /** @var Todo $todo */
//        $todo = $this->todoRepository->find($id);
        $todo = $this->getEntityManager()->find($id);
        if (!$todo) {
            return $this->notFound(['message' =>'Todo not found.']);
        }

        $em = $this->getEntityManager();
        $em->delete($todo);

//        $this->todoManager->remove($todo);
//        $this->todoManager->flush();

        return new Response('Successfully deleted the todo: ' . $todo->getTodo());
    }

    /**
     * Get entity manager
     */
    protected function getEntityManager()
    {
        return $this->get('app.todo.todo.manager');
    }

    /**
     * { @inheritdoc }
     */
    protected function getFormClass()
    {
        return null;
    }

}