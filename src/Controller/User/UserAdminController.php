<?php

namespace App\Controller\User;

use App\Entity\User\User;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserAdminController extends CRUDController
{

    public function editAction($id = null)
    {

        $templateKey = 'edit';
        $em = $this->container->get('doctrine.orm.entity_manager');

        $id = $this->get('request_stack')->getCurrentRequest()->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        $this->admin->setSubject($object);

        $form = $this->admin->getForm();
        $form->setData($object);

        if ($this->getRestMethod() === 'POST') {

            $form->handleRequest($this->get('request_stack')->getCurrentRequest());
            $isFormValid = $form->isValid();

            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {

                try {

                    $enabled = $form['enabled']->getData();
                    if ($enabled === true){
                        if ($object instanceof User) {
                            $object->setConfirmationToken(null);
                            $em->persist($object);
                            $em->flush($object);
                        }
                    }


                    if ($this->isXmlHttpRequest()) {
                        return $this->renderJson(array(
                            'result'    => 'ok',
                            'objectId'  => $this->admin->getNormalizedIdentifier($object)
                        ));
                    }

                    $this->addFlash('sonata_flash_success', $this->admin->trans('flash_edit_success', array('%name%' => $this->admin->toString($object)), 'SonataAdminBundle'));

                    return $this->redirectTo($object);


                } catch (ModelManagerException $e) {
                    $isFormValid = false;
                }
            }

            if (!$isFormValid) {
                if (!$this->isXmlHttpRequest()) {
                    $this->addFlash('sonata_flash_error', $this->admin->trans('flash_edit_error', array('%name%' => $this->admin->toString($object)), 'SonataAdminBundle'));
                }
            } elseif ($this->isPreviewRequested()) {
                $templateKey = 'preview';
                $this->admin->getShow();
            }
        }



        $view = $form->createView();

        $this->get('twig')->getExtension('Symfony\Bridge\Twig\Extension\FormExtension')->renderer->setTheme($view, $this->admin->getFormTheme());

        return $this->render($this->admin->getTemplate($templateKey), array(
            'action' => 'edit',
            'form'   => $view,
            'object' => $object,
        ));
    }


}