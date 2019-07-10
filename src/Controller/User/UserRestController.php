<?php

namespace App\Controller\User;

use App\Controller\BaseRestController;
use App\Entity\OAuth\Client;
use App\Entity\User\User;
use App\Form\Type\User\ChangePasswordFormType;
use App\Form\Type\User\ConfirmRegistrationType;
use App\Form\Type\User\ForgotPasswordType;
use App\Form\Type\User\ResettingType;
use App\Form\Type\User\UserRegisterType;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class UserRestController extends BaseRestController
{
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

        $em = $this->getEntityManager();

        $userEmail = $request->get('email', null);

        /** @var User $user */
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
     * Confirm User action.
     *
     * @Route("/user/confirm", methods={"PATCH"})
     * @SWG\Patch(
     *     tags={"User"},
     *     summary="Confirm user email",
     *     description="Confirm user email",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *          @Model(type=ConfirmRegistrationType::class)
     *     ),
     *     @SWG\Response(
     *          response=200, description="Request has been processed successfully",
     *      ),
     *      @SWG\Response(
     *          response=404, description="Given email was not found"
     *      ),
     *      @SWG\Tag(name="user")
     *  )
     * @param Request $request
     *
     * @return View
     */
    public function patchUsersConfirmAction(Request $request)
    {
        $form = $this->createForm(ConfirmRegistrationType::class, null, [
            'method' => Request::METHOD_PATCH,
            'csrf_protection' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $oauthService = $this->get('fos_oauth_server.server');
            /** @var Client $client */
            $client = $oauthService->getClient($form['client_id']->getData());

            if (empty($client)) {
                return $this->bad([
                    'message' => $this->get('translator')->trans(
                        'application.token.invalid_client_id',
                        [],
                        'AppUser'
                    ),
                ]);
            }

            if (!$oauthService->validateClientCredentials($client, $client->getSecret())) {
                return $this->bad([
                    'message' => $this->get('translator')->trans(
                        'application.token.invalid_credentials',
                        [],
                        'AppUser'
                    ),
                ]);
            }

            $token = $form['token']->getData();
            if (empty($token)) {
                return $this->bad([
                    'message' => $this->get('translator')->trans(
                        'registration.token.not_valid',
                        [],
                        'AppUser'
                    ),
                ]);
            }

            $userManager = $this->getEntityManager();

            /** @var User $user */
            $user = $userManager->findByToken($token);

            if (null === $user) {
                return $this->notFound($this->get('translator')->trans(
                    'registration.token.not_valid',
                    [],
                    'AppUser'
                ));
            }

            // check 24 hrs activation link
            if (!$user->isEnabled()) {
                if (strtotime($user->getCreatedAt()->format('Y-m-d H:i:s')) < strtotime('-1 day')) {
                    return $this->bad(['message' => 'Activation link has been expired']);
                }
            }

            $dispatcher = $this->get('event_dispatcher');

            $user->setConfirmationToken(null);
            $user->setEnabled(true);

            $event = new UserEvent($user, $request);
            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRM, $event);

            $userManager->update($user);

            $token = $oauthService->createAccessToken($client, $user);

            $view = $this->ok([
                'user' => $user,
                'token' => $token,
            ], ["details"]);
        } else {
            $view = $this->bad($form);
        }

        return $view;
    }

    /**
     * Reset password action.
     *
     * @Route("/user/reset", methods={"PATCH"})
     *
     * @SWG\Patch(
     *     tags={"User"},
     *     summary="Reset password",
     *     description="Reset password",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *          @Model(type=ResettingType::class)
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="Password has been successfully changed",
     *          @SWG\Schema(
     *              @SWG\Items(
     *                ref=@Model(type=User::class),
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *          response=400, description="Password fields are not valid"
     *      ),
     *      @SWG\Response(
     *          response=404, description="User with given token is not found"
     *      ),
     *      @SWG\Tag(name="user")
     *  )
     * @param Request $request
     *
     * @return View
     */
    public function patchUsersResetAction(Request $request)
    {
        $token = $request->get("token", null);

        $dispatcher = $this->get('event_dispatcher');
        $userManager = $this->getEntityManager();

        $user = $userManager->findByToken($token);

        if (null === $user) {
            return $this->notFound($this->get('translator')->trans(
                'registration.token.not_valid',
                [],
                'AppUser'
            ));
        }

        $event = new GetResponseUserEvent($user, $request);
        if (!$user->isPasswordRequestNonExpired($this->getParameter('fos_user.resetting.token_ttl'))) {
            return $this->bad($this->get('translator')->trans(
                'resetting.password.token_expired',
                [],
                'AppUser'
            ));
        }

        if (null !== $event->getResponse()) {
            return $this->bad();
        }

        $form = $this->createForm(ResettingType::class, $user, [
            'method' => Request::METHOD_PATCH,
            'csrf_protection' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $oauthService = $this->get('fos_oauth_server.server');
            /** @var Client $client */
            $client = $oauthService->getClient($form['client_id']->getData());

            if (empty($client)) {
                return $this->bad([
                    'message' => $this->get('translator')->trans(
                        'application.token.invalid_client_id',
                        [],
                        'AppUser'
                    ),
                ]);
            }

            if (!$oauthService->validateClientCredentials($client, $client->getSecret())) {
                return $this->bad([
                    'message' => $this->get('translator')->trans(
                        'application.token.invalid_credentials',
                        [],
                        'AppUser'
                    ),
                ]);
            }

            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_SUCCESS, $event);

            $userManager->update($user);

            if (null === $response = $event->getResponse()) {
                $response = new Response();
            }

            $dispatcher->dispatch(
                FOSUserEvents::RESETTING_RESET_COMPLETED,
                new FilterUserResponseEvent($user, $request, $response)
            );

            $token = $oauthService->createAccessToken($client, $user);

            return $this->ok([
                'user' => $user,
                'token' => $token,
            ], ["details"]);
        } else {
            return $this->bad($form);
        }
    }

    /**
     * Change password action.
     *
     * @Route("/user/change-password", methods={"PATCH"})
     *
     * @SWG\Patch(
     *     tags={"User"},
     *     summary="Change password",
     *     description="Change password",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *          @Model(type=ChangePasswordFormType::class)
     *     ),
     *     @SWG\Response(
     *          response=200,
     *          description="Password has been successfully changed",
     *          @SWG\Schema(
     *              @SWG\Items(
     *                ref=@Model(type=User::class),
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *          response=400, description="Password fields are not valid"
     *      ),
     *      @SWG\Response(
     *         response=403, description="User not authenticated"
     *     ),
     *      @SWG\Tag(name="user")
     *  )
     * @param Request $request
     *
     * @return View
     */
    public function patchChangePasswordAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->forbidden(['message' =>'You do not have permission for the action']);
        }

        $dispatcher = $this->get('event_dispatcher');
        $userManager = $this->getEntityManager();

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $this->bad();
        }

        $form = $this->createForm(ChangePasswordFormType::class, $user, [
            'method' => Request::METHOD_PATCH,
            'csrf_protection' => false,
            'block_name' => '',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_SUCCESS, $event);

            $userManager->update($user);

            if (null === $response = $event->getResponse()) {
                $response = new Response();
            }

            $dispatcher->dispatch(
                FOSUserEvents::CHANGE_PASSWORD_COMPLETED,
                new FilterUserResponseEvent($user, $request, $response)
            );

            return $this->ok($user);
        } else {
            return $this->bad($form);
        }
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