<?php

namespace App\Controller\User;

use App\Controller\BaseRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use OAuth2\OAuth2ServerException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;

/**
 * Class OauthRestController
 */
class OauthRestController extends BaseRestController
{
    /**
     * Token action
     *
     * @Route("/oauth/v2/token", methods={"POST"})
     * @SWG\Post(
     *    tags={"Oauth"},
     *
     *    @SWG\Response(
     *          response=200,
     *          description="Response token",
     *      )
     * )
     * @param Request $request
     *
     * @return View|Response
     */
    public function tokenAction(Request $request)
    {
        $server = $this->get('fos_oauth_server.server');
        
        $user = $this->get('fos_user.user_manager')->findUserByUsernameOrEmail($request->get('username', null));

        if ($user) {
            if (!$user->isEnabled() && !$user->getConfirmationToken()) {
                return $this->bad([
                    'message' => $this->get('translator')->trans(
                        'security.user.is_blocked',
                        [],
                        'AppUser'
                    ),
                ]);
            }
            if (!$user->isEnabled()) {
                return $this->bad([
                    'message' => $this->get('translator')->trans(
                        'security.user.is_not_enabled',
                        [],
                        'AppUser'
                    ),
                ]);
            }
        }

        try {
            $data = $server->grantAccessToken($request);

            return $this->ok($data);
        } catch (OAuth2ServerException $e) {
            return $this->bad($e->getDescription());
        }
    }

    /**
     * Get entity manager
     */
    protected function getEntityManager()
    {
        return null;
    }

    /**
     * { @inheritdoc }
     */
    protected function getFormClass()
    {
        return null;
    }
}
