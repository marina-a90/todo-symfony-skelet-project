<?php

namespace App\Controller;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\FOSRestController;
use App\Service\Manager\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class BaseRestController
 */
abstract class BaseRestController extends FOSRestController
{
    /**
     * Get entity manager for resource
     *
     * @return EntityManagerInterface
     */
    abstract protected function getEntityManager();

    /**
     * Get form class for resource.
     *
     * @return string
     */
    abstract protected function getFormClass();

    /**
     * Find all entity data by given criteria.
     *
     * @param array $filter     List of filters to filter by
     * @param array $order      List of sort=>direction pairs to order by
     * @param int   $limit      Number of results to return
     * @param int   $offset     Number to start from
     * @param bool  $totalCount Show total count in response headers
     *
     * @param array $groups
     *
     * @return View
     */
    protected function getAllAction(
        $filter = [],
        $order = [],
        $limit = null,
        $offset = null,
        $totalCount = true,
        $groups = array()
    ) {
        $data = $this->getEntityManager()->findAll($filter, $order, $limit, $offset);
        $total = $this->getEntityManager()->count($filter);

        $view = $this->ok($data, $groups);

        if ($totalCount) {
            $view->setHeader('X-Total-Count', $total);
        }

        return $view;
    }

    /**
     * Returns representation of single entity.
     *
     * @param int   $id Identifier of entity to return
     *
     * @param array $groups
     *
     * @return View
     */
    protected function getAction($id, $groups = array())
    {
        $data = $this->findOr404($id);

        return $this->ok($data, $groups);
    }

    /**
     * Creates new entity and returns representation of that entity
     *
     * @param Request $request
     *
     * @return View
     */
    protected function postAction(Request $request)
    {

        $entity = $this->getEntityManager()->create();

        return $this->handleForm(
            $this->getFormClass(),
            $entity,
            $request,
            Request::METHOD_POST,
            'create'
        );
    }

    /**
     * Update all fields of entity with given id and returns updated representation of that entity.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     *
     * @throws NotFoundHttpException
     */
    protected function putAction(Request $request, $id)
    {
        $entity = $this->findOr404($id);

        return $this->handleForm(
            $this->getFormClass(),
            $entity,
            $request,
            Request::METHOD_PUT,
            'edit'
        );
    }

    /**
     * Update passed fields of entity with given id and returns updated representation of that entity.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     *
     * @throws NotFoundHttpException
     */
    protected function patchAction(Request $request, $id)
    {
        $entity = $this->findOr404($id);

        return $this->handleForm(
            $this->getFormClass(),
            $entity,
            $request,
            Request::METHOD_PATCH,
            'edit'
        );
    }

    /**
     * Delete entity with given identifier.
     *
     * @param int $id
     *
     * @return View
     *
     * @throws NotFoundHttpException
     */
    protected function deleteAction($id)
    {
        $entity = $this->findOr404($id);

        $this->denyAccessUnlessGranted('delete', $entity);

        $this->getEntityManager()->delete($entity);

        return $this->noContent();
    }

    /**
     * Return 200
     *
     * @param mixed $data
     *
     * @param array $groups
     * @return View
     */
    protected function ok($data = null, $groups = array())
    {
        if ($groups) {
            $context = new Context();
            foreach ($groups as $group) {
                $context->addGroup($group);
            }

            $view = $this->view($data, Response::HTTP_OK);
            $view->setContext($context);

            return $view;
        }
        return $this->view($data, Response::HTTP_OK);
    }

    /**
     * Return 204
     *
     * @return View
     */
    protected function noContent()
    {
        return $this->view();
    }

    /**
     * Return 201
     *
     * @param object $data
     *
     * @param array $groups
     * @return View
     */
    protected function created($data, $groups = array())
    {
        if ($groups) {
            $context = new Context();

            foreach ($groups as $group) {
                $context->addGroup($group);
            }

            $view = $this->view($data, Response::HTTP_CREATED);
            $view->setContext($context);

            return $view;
        }

        return $this->view($data, Response::HTTP_CREATED);
    }

    /**
     * Return 404
     *
     * @param mixed $data Data to return in response
     *
     * @return View
     */
    protected function notFound($data = null)
    {
        if (is_array($data) && $data['message']) {
            $data;
        } else {
            $data;
        }

        return $this->view($data, Response::HTTP_NOT_FOUND);
    }

    /**
     * Return 403
     *
     * @param mixed $data Data to return in response
     *
     * @return View
     */
    protected function forbidden($data = null)
    {
        return $this->view($data, Response::HTTP_FORBIDDEN);
    }

    /**
     * @param FormInterface $form
     *
     * @return array
     */
    private function getFormErrors(FormInterface $form)
    {
        $errors = [];
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getFormErrors($childForm)) {
                    foreach ($childErrors as $childErrorKey => $childError) {
                        $errors[] = [
                            'element' => $childForm->getName(),
                            'message' => $childError
                        ];
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Return 400
     *
     * @param mixed $data Data to return in response
     *
     * @return view
     */
    protected function bad($data = null)
    {
        if ($data instanceof FormInterface) {
            $errors = $this->getFormErrors($data);

            $formattedData = [
                'message' => isset($data['message']) ? $data['message'] : '',
                'errors' => $errors
            ];

        } elseif (is_array($data)) {
            $formattedData = [
                'message' => isset($data['message']) ? $data['message'] : '',
                'errors' => isset($data['errors']) ? $data['errors'] : [],
            ];
            // TODO - if need to formatted
//            if (isset($data['children'])) {
//                foreach ($data['children'] as $child => $errors) {
//                    $formattedData['errors'][] = [
//                        'element' => $child,
//                        'message' => $errors
//                    ];
//                }
//            }
        } else {
            $formattedData = [
                'message' => isset($data['message']) ? $data['message'] : '',
                'errors' => $data,
            ];
        }

        return $this->view($formattedData, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Find entity by ID of throw 404.
     *
     * @param int $id Entity ID
     *
     * @return mixed
     */
    protected function findOr404($id)
    {
        $data = $this->getEntityManager()->find($id);

        if (empty($data)) {
            throw new NotFoundHttpException();
        }

        return $data;
    }

    /**
     * Handle form submition, validation and entity managment.
     *
     * @param string  $formClass  Form class name
     * @param object  $entity     Entity
     * @param Request $request    Request parameters
     * @param string  $method     HTTP Method (POST|PUT|PATCH)
     * @param string  $permission Permission attribute to check
     *
     * @return mixed
     */
    protected function handleForm($formClass, $entity, $request, $method, $permission = null)
    {
        if (empty($entity)) {
            return $this->notFound();
        }

        $form = $this->createForm($formClass, $entity, array(
            'method'            => $method,
            'csrf_protection'   => false,
        ));

        $form->handleRequest($request);

        if ($permission) {
            $this->denyAccessUnlessGranted($permission, $entity);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $this->getEntityManager()->save($data);

            if ($method == Request::METHOD_POST) {
                return $this->created($data);
            } else {
                return $this->ok($data);
            }
        } else {
            return $this->bad($form);
        }
    }

//    /**
//     * Get Elastic Search  Repository
//     *
//     * @param string $repository repository to find
//     *
//     * @return \FOS\ElasticaBundle\Repository The repository class.
//     */
//    public function getEsRepo(string $repository)
//    {
//        return $this->get('fos_elastica.manager')->getRepository($repository);
//    }
}