<?php

namespace App\Controller\Todo;

use App\Controller\BaseRestController;
use App\Entity\Todo\Todo;
use App\Entity\User\User;
use App\Form\Type\Todo\TodoType;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class TodoRestController extends BaseRestController
{
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
     * @return View
     */
    public function getTodosAction()
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->forbidden(
                $this->get('translator')->trans(
                    'security.user.not_have_permission',
                    [],
                    'AppUser'
                )
            );
        }

        /** @var Todo $todos */
        $todos = $this->getEntityManager()->findAll();

        if (!$todos) {
            return $this->notFound(
                $this->get('translator')->trans(
                    'form.not_found_todos',
                    [],
                    'AppTodo'
                )
            );
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
     */
    public function getIncompelteTodosAction()
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->forbidden(
                $this->get('translator')->trans(
                    'security.user.not_have_permission',
                    [],
                    'AppUser'
                )
            );
        }

        /** @var Todo $todos */
        $todos = $this->getEntityManager()->findIncompleteTodos();

        if (!$todos) {
            return $this->notFound(
                $this->get('translator')->trans(
                    'form.not_found_todos',
                    [],
                    'AppTodo'
                )
            );
        }

        return $this->ok($todos, ['list']);
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
     */
    public function getSingleTodoAction($id)
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->forbidden(
                $this->get('translator')->trans(
                    'security.user.not_have_permission',
                    [],
                    'AppUser'
                )
            );
        }

        /** @var Todo $todo */
        $todo = $this->getEntityManager()->find($id);
        if (!$todo) {
            return $this->notFound(
                $this->get('translator')->trans(
                    'form.not_found_todo',
                    [],
                    'AppTodo'
                )
            );
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
            return $this->forbidden(
                $this->get('translator')->trans(
                    'security.user.not_have_permission',
                    [],
                    'AppUser'
                )
            );
        }

        /** @var Todo $todo */
        $todo = new Todo();

        $form = $this->createForm(TodoType::class, $todo, [
            'method' => Request::METHOD_POST,
            'csrf_protection' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getEntityManager();
            $em->save($todo);

            return $this->ok($todo, ['list']);
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
     * @return View
     */
    public function patchTodoAction(Request $request, $id)
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->forbidden(
                $this->get('translator')->trans(
                    'security.user.not_have_permission',
                    [],
                    'AppUser'
                )
            );
        }

        /** @var Todo $todo */
        $todo = $this->todoRepository->findOneBy(['id' => $id]);
        if (!$todo) {
            return $this->notFound(
                $this->get('translator')->trans(
                    'form.not_found_todo',
                    [],
                    'AppTodo'
                )
            );
        }

        $form = $this->createForm(TodoType::class, $todo, [
            'method' => Request::METHOD_PATCH,
            'csrf_protection' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getEntityManager();
            $em->save($todo);

            return $this->ok($todo, ['list']);
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
            return $this->forbidden(
                $this->get('translator')->trans(
                    'security.user.not_have_permission',
                    [],
                    'AppUser'
                )
            );
        }

        /** @var Todo $todo */
        $todo = $this->todoRepository->find($id);
        if (!$todo) {
            return $this->notFound(
                $this->get('translator')->trans(
                    'form.not_found_todo',
                    [],
                    'AppTodo'
                )
            );
        }

        $todo->setIsDone( !$todo->getIsDone() );

        $em = $this->getEntityManager();
        $em->save($todo);

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
        $todo = $this->getEntityManager()->find($id);
        if (!$todo) {
            return $this->notFound(
                $this->get('translator')->trans(
                    'form.not_found_todo',
                    [],
                    'AppTodo'
                )
            );
        }

        $em = $this->getEntityManager();
        $em->delete($todo);

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