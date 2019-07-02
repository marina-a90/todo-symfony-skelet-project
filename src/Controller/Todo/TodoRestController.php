<?php

namespace App\Controller\Todo;

use App\Entity\Todo\Todo;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;

class TodoRestController extends AbstractFOSRestController
{
//    /**
//     * Get list of Todos.
//     *
//     * @Route("/todos", methods={"GET"})
//     *
//     * @SWG\Get(
//     *     tags={"Todo"},
//     *     summary="Get list of Todos",
//     *     description="Get list of Todos",
//     *     produces={"application/json"},
//     *     @SWG\Tag(name="Todo"),
//     *     @SWG\Response(
//     *         response=200, description="Success"
//     *     ),
//     *     @SWG\Response(
//     *         response=204, description="No content"
//     *     )
//     *  )
//     *
//     * @return View
//     */
//    public function getTodosAction()
//    {
//        $todos = $this->getDoctrine()
//            ->getRepository(Todo::class)
//            ->findAll()
//        ;
//
//        return $this->getViewHandler($this->view($todos));
////        return $this->handleView($this->view($todos));
//    }


    /**
     * Create todo.
     * @Route("/todo", methods={"POST"})
     *
     * @return array
     */
    public function postTodoAction(Request $request)
    {
        $todo = new todo();
        $todo->setTodo('first todo');
        $em = $this->getDoctrine()->getManager();
        $em->persist($todo);
        $em->flush();
        return View::create($todo, Response::HTTP_CREATED , []);

    }

}