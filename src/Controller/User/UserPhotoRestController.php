<?php

namespace App\Controller\User;

use App\Application\Sonata\MediaBundle\Entity\Media;
use App\Controller\BaseRestController;
use App\Entity\User\User;
use App\Event\Media\MediaEvent;
use App\Form\Type\User\PhotoType;
use App\Security\User\UserVoter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class UserPhotoRestController extends BaseRestController
{
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher = null)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Set user photo action.
     *
     * @Route("/user/photo", methods={"POST"})
     *
     * @SWG\Post(
     *     tags={"User Photo"},
     *     summary="User photo",
     *     description="User photo",
     *     consumes={"multipart/form-data"},
     *     produces={"*", "application/json"},
     *     @SWG\Parameter(
     *         name="photo[binaryContent]",
     *         in="formData",
     *         type="file",
     *         required=false,
     *         format="multipart/form-data",
     *         description="The field is used to save the photo"
     *     ),
     *     @SWG\Response(
     *          response=201,
     *          description="Success",
     *          @SWG\Schema(ref=@Model(type=User::class))
     *      ),
     *      @SWG\Response(
     *          response=400, description="Data is invalid"
     *      ),
     *      @SWG\Tag(name="user"),
     *      @SWG\SecurityScheme(
     *         securityDefinition="Bearer",
     *         type="apiKey",
     *         name="Authorization",
     *         in="header"
     *     )
     *  )
     * @param Request $request
     *
     * @return View
     */
    public function postUserPhotoAction(Request $request)
    {
        $user = $this->getUser();

        if ( !$user ) {

            return $this->forbidden($this->get('translator')->trans('security.user.not_have_permission', [], 'AppUser'));
        }

        $this->denyAccessUnlessGranted(UserVoter::EDIT_USER, $user);

        // check if the user already has a photo
        /** @var Media $oldMedia */
        $oldMedia = $user->getPhoto();

        $form = $this->createForm(PhotoType::class, $user, [
            'method' => Request::METHOD_POST
        ]);

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $userManager = $this->getEntityManager();

            if ($oldMedia) {
                $mediaManager = $this->get('sonata.media.manager.media');

                // delete old user photo before posting a new one
                $provider = $this->get($oldMedia->getProviderName());
                $provider->removeThumbnails($oldMedia);
                $mediaManager->delete($oldMedia);
            }

            $userManager->save($data, true);

            return $this->created($data, ['details']);
        }

        return $this->bad($form, ['details']);
    }

    /**
     * Remove user photo.
     *
     * @Route("/user/{id}/photo", methods={"PUT"})
     * @SWG\Put(
     *     tags={"User Photo"},
     *     summary="Remove user photo.",
     *     description="Remove user photo.",
     *     produces={"*", "application/json"},
     *      @SWG\Response(
     *         response=204, description="No content"
     *     ),
     *      @SWG\Response(
     *          response=404, description="Photo not found"
     *      ),
     *      @SWG\Tag(name="user"),
     *  )
     * @param Request $request
     *
     * @return View
     */
    public function putToggleIsDoneTodoAction(Request $request, $id)
    {
        /** @var User $user */
        $user = $this->getUser();
        if ( !$user ) {

            return $this->forbidden($this->get('translator')->trans('security.user.not_have_permission', [], 'AppUser'));
        }

        if ( $user->getId() != $id ) {

            return $this->forbidden($this->get('translator')->trans('security.user.not_have_permission', [], 'AppUser'));
        }

        // check if the user already has a photo
        /** @var Media $oldMedia */
        $oldMedia = $user->getPhoto();

        if ($oldMedia) {
            $mediaManager = $this->get('sonata.media.manager.media');

            // delete user photo
            $provider = $this->get($oldMedia->getProviderName());
            $provider->removeThumbnails($oldMedia);
            $user->setPhoto(null);
            $mediaManager->delete($oldMedia);
        }

        $this->getEntityManager()->save($user, true);

        return $this->ok($user, ["details"]);
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