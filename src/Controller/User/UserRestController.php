<?php

namespace App\Controller\User;

use App\Controller\BaseRestController;
use App\Entity\User\User;
use App\Form\Type\User\UserRegisterType;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class UserRestController extends BaseRestController
{
//    private $userManager;
//    private $userRepository;
//
//    public function __construct(EntityManagerInterface $entityManager)
//    {
//        $this->userManager = $entityManager;
//        $this->userRepository = $this->userManager->getRepository(User::class);
//    }

    /**
     * Get list of members
     *
     * @Route("/users", methods={"GET"})
     *
     * @SWG\Get(
     *     tags={"User"},
     *     summary="Get list of users",
     *     description="Get list of users.",
     *     produces={"application/json"},
     *     @SWG\Tag(name="User"),
     *     @SWG\Response(
     *         response=403, description="User not authenticated"
     *     ),
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
    public function getUsersAction()
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->forbidden(['message' =>'You do not have permission for the action.']);
        }

        /** @var User $users */
        $users = $this->getEntityManager()->findAll();
//        $users = $this->userRepository->findAll();

        if (!$users) {
            return $this->notFound(['message' =>'No users.']);
        }

        return $this->ok($users, ['list']);
    }

    /**
     * Retrieve logged user.
     *
     *  @Route("/user/me", methods={"GET"})
     *
     *  @SWG\Get(
     *     tags={"User"},
     *     summary="Get logged user",
     *     description="Get logged user",
     *     produces={"application/json"},
     *     @SWG\Response(
     *          response=200,
     *          description="Success",
     *          @SWG\Schema(ref=@Model(type=User::class))
     *     ),
     *     @SWG\Response(
     *         response=204, description="No content"
     *     ),
     *     @SWG\Tag(name="User"),
     *    @SWG\SecurityScheme(
     *         securityDefinition="Bearer",
     *         type="apiKey",
     *         name="Authorization",
     *         in="header"
     *     )
     *  )
     *
     * @return View
     */
    public function getUserMeAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->notFound(['message' =>'Please log in.']);
        }

        return $this->ok($user, ['list']);
    }

    /**
     * Register new user
     *
     * @Route("/user/register", methods={"POST"})
     *
     * @SWG\Post(
     *     tags={"User"},
     *     summary="Register new user",
     *     description="Register new user.",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *          @Model(type=UserRegisterType::class)
     *     ),
     *     @SWG\Response(
     *          response=201,
     *          description="Success",
     *          @SWG\Schema(ref=@Model(type=User::class))
     *      ),
     *      @SWG\Response(
     *          response=400, description="Data is invalid"
     *      )
     *  )
     * @param Request $request
     *
     * @return View
     */
    public function userRegisterAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user) {
            return $this->forbidden(['message' =>'You cannot register while you are logged in. To make a new account, please log out.']);
        }

        $dispatcher = $this->get('event_dispatcher');
//        $userRepository = $this->userRepository;
        $em = $this->getEntityManager();

        $userEmail = $request->get('email', null);
        /** @var User $user */
//        $user = $userRepository->findOneBy(['email' => $userEmail]);
        $user = $em->findOneBy(['email' => $userEmail]);

        if (!$user) {
            $user = $em->create();
        }

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);
        $form = $this->createForm(UserRegisterType::class, $user, [
            'method' => Request::METHOD_POST,
            'csrf_protection' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);
            $em->update($user);

            return $this->ok($user, ['list']);
        }

        return $this->bad($form);
    }

    /**
     * Get entity manager
     */
    protected function getEntityManager()
    {
        return $this->get('app.user.user.manager');
    }

    /**
     * { @inheritdoc }
     */
    protected function getFormClass()
    {
        return null;
    }

}