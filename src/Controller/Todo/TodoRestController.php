<?php

namespace App\Controller\Todo;

use App\Entity\Todo\Todo;
use App\Form\Type\Todo\TodoType;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Nelmio\ApiDocBundle\Tests\Functional\Controller\ApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class TodoRestController extends FOSRestController
{
    private $todoManager;
    private $todoRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->todoManager = $entityManager;
        $this->todoRepository = $this->todoManager->getRepository(Todo::class);
    }

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
//     * @return view
     * @return Response
     */
    public function getTodosAction()
    {
        $todos = $this->todoRepository->findAll();

        if (!$todos) {
            return new Response('No todos.');
        }

        return new Response($this->json($todos));
    }

    /**
     * Get list of incompleted Todos.
     *
     * @Route("/todos", methods={"GET"})
     *
     * @SWG\Get(
     *     tags={"Todo"},
     *     summary="Get list of incompleted Todos",
     *     description="Get list of incompleted Todos",
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
     * @return Response
     */
    public function getIncompeltedTodosAction()
    {
        $todos = $this->todoRepository->findBy(['isDone' => 0]);

        if (!$todos) {
            return new Response('No incomplete todos.');
        }
;
        return new Response($this->json($todos));
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
//     * @return View
     * @return Response
     */
    public function getSingleTodoAction($id)
    {
        $todo = $this->todoRepository->findOneBy(['id' => $id]);

        if (!$todo) {
            return new Response('Todo not found.');
        }

        return new Response($this->json($todo));
//      return $this->view($todo, Response::HTTP_OK);
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
     * @return Response
     */
    public function postTodoAction(Request $request)
    {
        $todo = new Todo();

        $form = $this->createForm(TodoType::class, $todo, [
            'method' => Request::METHOD_POST,
            'csrf_protection' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->todoManager;
            $em->persist($todo);
            $em->flush();

            return new Response($this->json($todo));
//            return $this->handleView($this->view(['status' => 'ok'], Response::HTTP_CREATED));
        }

        return $this->handleView($this->view($form->getErrors()));
    }

    /**
     * Edit todo.
     * @Route("/todo/{id}/edit", methods={"PATCH"})
     *
     * @SWG\Patch(
     *     tags={"Todo"},
     *     summary="Edit new todo",
     *     description="Edit new todo.",
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
     * @return Response
     */
    public function patchTodoAction(Request $request, $id)
    {
        $todo = $this->todoRepository->findOneBy(['id' => $id]);

        if (!$todo) {
            return new Response('Todo not found.');
        }

        $form = $this->createForm(TodoType::class, $todo, [
            'method' => Request::METHOD_POST,
            'csrf_protection' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->todoManager;
            $em->persist($todo);
            $em->flush();

            return new Response($this->json($todo));
        }

        return $this->handleView($this->view($form->getErrors()));
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
        $todo = $this->todoRepository->find($id);

        if (!$todo) {
            return new Response('Todo not found.');
        }

        $todo->setIsDone( !$todo->getIsDone() );

        $em = $this->todoManager;
        $em->persist($todo);
        $em->flush();

        return new Response($this->json($todo));
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
     * @return Response
     */
    public function deleteTodoAction(Request $request, $id)
    {
        $todo = $this->todoRepository->find($id);

        if (!$todo) {
            return new Response('No incomplete todos.');
        }

        $this->todoManager->remove($todo);
        $this->todoManager->flush();

        return new Response('Successfully deleted');
    }

}