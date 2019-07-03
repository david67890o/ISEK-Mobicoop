<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL and proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <gnu.org/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 **************************/

namespace App\Communication\Service;

use App\Communication\Entity\Email;
use Psr\Log\LoggerInterface;

/**
 * Email sending service via Swift_Mailer
 *
 * @author Maxime Bardot <maxime.bardot@covivo.eu>
 */
class SendEmailManager
{
    private $mailer;
    private $templating;
    private $emailSenderDefault;
    private $emailReplyToDefault;
    private $logger;

    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $templating, LoggerInterface $logger, string $emailSender, string $emailReplyTo)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->emailSenderDefault = $emailSender;
        $this->emailReplyToDefault = $emailReplyTo;
        $this->logger = $logger;
    }



    /**
     * Send an email
     * @param Email $mail the email to send
     * @param string $template the email's template
     * @param array $varOpt optionnal informations that can be included in the template
     * @return string
     */
    public function sendEmail(Email $mail, $template, $varOpt=[])
    {
        $failures = "";

        // Traitement de l'expéditeur
        if (is_null($mail->getSenderEmail()) || trim($mail->getSenderEmail()) === "") {
            $senderEmail = $this->emailSenderDefault;
        } else {
            $senderEmail = $mail->getSenderEmail();
        }

        // Traitement du reply
        if (is_null($mail->getReturnEmail()) || trim($mail->getReturnEmail()) === "") {
            $replyToEmail = $this->emailReplyToDefault;
        } else {
            $replyToEmail = $mail->getReturnEmail();
        }

        $message = (new \Swift_Message($mail->getObject()))
            ->setFrom($senderEmail)
            ->setTo($mail->getRecipientEmail())
            ->setReplyTo($replyToEmail)
            ->setBody(
                $this->templating->render(
                    'Emails/'.$template,
                    array(
                        'varOpt' => $varOpt,
                        'message' => str_replace(array("\r\n", "\r", "\n"), "<br />", $mail->getMessage()),
                    )
                ),
                'text/html'
            );

        $failures = $this->mailer->send($message, $failures);


        return $failures;
    }
}
