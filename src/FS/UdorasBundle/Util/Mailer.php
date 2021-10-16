<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 26.09.2016
 * Time: 16:57
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\UdorasBundle\Util;


use FS\TrainingProgramsBundle\Entity\TrainingProgram;
use FS\UserBundle\Entity\Employee;
use FS\UserBundle\Entity\User;
use FS\UserBundle\Entity\Vendor;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class Mailer
{
    protected $mailer;
    protected $templating;
    protected $notificationEmail;
    protected $router;

    public function __construct
    (
        \Swift_Mailer $mailer,
        \Twig_Environment $templating,
        UrlGeneratorInterface $router,
        $notificationEmail
    )
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->router = $router;
        $this->notificationEmail = $notificationEmail;
    }

    private function sendMail(\Swift_Message $message)
    {
        return $this->mailer->send($message);
    }

    private function render($view, $parameters)
    {
        return $this->templating->render($view, $parameters);
    }

    private function send($email, $body, $subject)
    {
        /** @var \Swift_Message $message */
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($this->notificationEmail, 'Udoras')
            ->setTo($email)
            ->setBody($body, 'text/html');

        $res = false;
        if ($email == null) {
            return $res;
        }
        try {
            $res = $this->sendMail($message);
        } catch (\Exception $e) {
            /*NOP*/
        }

        return $res;
    }

    /**
     * @param User $user
     * @return bool|int
     */
    public function sendLoginEmailMessage(UserInterface $user)
    {
        $url = $this->router->generate('login_set_password', ['token' => $user->getPasswordSetToken()], true);

        $body = $this->render(
            "FSUserBundle:Security:login_email.html.twig",
            [
                'loginLink' => $url,
                'user' => $user,
            ]
        );
        $subject = 'Welcome to uDoras';

        return $this->send($user->getEmail(), $body, $subject);
    }

    public function sendRequestMessage(User $user, TrainingProgram $trainingProgram)
    {
        $loginLink = null;

        if (!$user->isEnabled()) {
            $loginLink = $this->router->generate('login_set_password', [
                'token' => $user->getPasswordSetToken()
            ], true);
        }

        $subject = 'Invitation to a training program from uDoras';
        $body = $this->render("FSTrainingProgramsBundle:Requests:trainingRequestEmail.html.twig", [
            'employee' => $user,
            'trainingProgram' => $trainingProgram,
            'loginLink' => $loginLink
        ]);

        return $this->send($user->getEmail(), $body, $subject);
    }

    /**
     * Share Certificate via Email
     *
     * @param $email
     * @param $PDFData
     * @return bool|int
     */
    public function sendShareCertificateMessage($email, $PDFData)
    {
        $subject = 'Shared certificate from uDoras';
        $message = $this->getSwiftMessage($email, $subject);
        $message->attach(\Swift_Attachment::newInstance($PDFData, 'certificate.pdf', 'application/pdf'));

        return $this->sendSwiftMessage($message);
    }

    /**
     * Get Swift_Message without body
     *
     * @param $email
     * @param $subject
     * @return \Swift_Mime_SimpleMessage
     */
    private function getSwiftMessage($email, $subject)
    {
        return \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($this->notificationEmail, 'Udoras')
            ->setTo($email)
        ;
    }

    /**
     * @param $message
     * @return bool|int
     */
    private function sendSwiftMessage($message)
    {
        $result = false;

        try {
            $result = $this->sendMail($message);
        } catch (\Exception $e) {
            /*NOP*/
        }

        return $result;
    }
}