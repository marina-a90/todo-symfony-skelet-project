<?php

namespace App\Form\Transformer;

use App\Service\Manager\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class UserToSlugTransformer
 */
class UserToSlugTransformer implements DataTransformerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $userManager;

    /**
     * GenresTransformer constructor.
     *
     * @param EntityManagerInterface $userManager
     */
    public function __construct(EntityManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * { @inheritdoc }
     */
    public function transform($value)
    {
        if ($value) {
            return $value->getUsername();
        }
        else {
            return null;
        }
    }

    /**
     * { @inheritdoc }
     */
    public function reverseTransform($value)
    {
        if ($value) {
            return $this->userManager->findUserByUsernameOrEmail($value);
        } else {
            return null;
        }
    }
}