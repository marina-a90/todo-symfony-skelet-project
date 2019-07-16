<?php

namespace App\Security\User;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Entity\User\User;

/**
 * Class UserVoter
 *
 */
class UserVoter extends Voter
{
    const EDIT_USER = "edit_user";
    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        // only vote on User or Profile objects inside this voter
        if (!$subject instanceof User) {
            return false;
        }

        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(
            self::EDIT_USER
        ))
        ) {
            return false;
        }

        return true;
    }

    /**
     * Can edit self
     *
     * @param User    $requester
     * @param User    $user
     *
     * @return bool
     */
    private function canEditProfile(User $requester, User $user)
    {
        if ($requester->getId() === $user->getId()) {
            return true;
        }

        return false;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     *
     * @param string         $attribute
     * @param mixed          $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $requester = $token->getUser();

        if (!$requester instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::EDIT_USER:
                return $this->canEditProfile($requester, $subject);
                break;
        }

        return false;
    }
}