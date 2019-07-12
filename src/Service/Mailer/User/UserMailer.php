<?php

namespace App\Service\Mailer\User;

use FOS\UserBundle\Mailer\TwigSwiftMailer;
use FOS\UserBundle\Model\UserInterface;

/**
 * Class UserMailer
 */
class UserMailer extends TwigSwiftMailer
{
    /**
     * Overridden to change confirmation url.
     *
     * @param UserInterface $user
     */
    public function sendConfirmationEmailMessage(UserInterface $user)
    {
        $template = $this->parameters['template']['confirmation'];
        $url = sprintf('%s?token=%s', $this->parameters['url']['confirmation'], $user->getConfirmationToken());

        $context = [
            'user' => $user,
            'confirmationUrl' => $url,
        ];

        $this->sendMessage($template, $context, $this->parameters['from_email']['confirmation'], $user->getEmail());
    }

    /**
     * Overridden to change resenting url.
     *
     * @param UserInterface $user
     */
    public function sendResettingEmailMessage(UserInterface $user)
    {
        $template = $this->parameters['template']['resetting'];
        $url = sprintf('%s?token=%s', $this->parameters['url']['resetting'], $user->getConfirmationToken());

        $context = [
            'user' => $user,
            'confirmationUrl' => $url
        ];

        $this->sendMessage($template, $context, $this->parameters['from_email']['resetting'], $user->getEmail());
    }


}