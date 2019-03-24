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
 *    along with this program.  If not, see <gnu.oruse Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;g/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 **************************/

namespace Mobicoop\Bundle\MobicoopBundle\Carpool\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Ad;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Form\AdForm;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Service\AdManager;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Controller class for carpooling related actions.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class CarpoolController extends AbstractController
{
    /**
     * Create a carpooling ad.
     */
    public function ad(AdManager $adManager, UserManager $userManager, Request $request)
    {

        $date = new \DateTime();
        $ad = new Ad();
        $ad->setRole(Ad::ROLE_BOTH);
        $ad->setType(Ad::TYPE_ONE_WAY);
        $ad->setFrequency(Ad::FREQUENCY_PUNCTUAL);
        $ad->setPrice(Ad::PRICE);
        $ad->setOutwardDate($date->format('Y-m-d H:i'));
        $ad->setUser($userManager->getLoggedUser());

        $form = $this->createForm(AdForm::class, $ad);
        $error = false;
        $sucess = false;
        
        if ($request->isMethod('POST')) {
            $form->submit($request->request->all());
            $form->handleRequest($request);
        }

        // If it's a get, just render the form !
        if (!$form->isSubmitted()) {
            return $this->render('@Mobicoop/ad/create.html.twig', [
                'form' => $form->createView(),
                'error' => $error
            ]);
        }

        // Not Valid populate error
        if (!$form->isValid()) {
            $error = [];
            // Fields
            foreach ($form as $child /** @var Form $child */) {
                if (!$child->isValid()) {
                    foreach ($child->getErrors() as $err) {
                        $error[$child->getName()][] = $err->getMessage();
                    }
                }
            }
            // $error = $form->getErrors(true);
            // $jsonContent = $serializer->serialize($error, 'json');
            // $this->json();
            return $this->json(['error' => $error, 'sucess'=> $sucess]);
        }

        // Error happen durring proposol creation
        try {
            $ad = $adManager->createProposalFromAd($ad);
            $sucess = true;
        } catch (Error $err) {
            $error = $err;
        }

        return $this->json(['error' => $error, 'sucess'=> $sucess]);
    }
}
