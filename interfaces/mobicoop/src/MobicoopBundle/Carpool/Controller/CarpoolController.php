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
use Symfony\Component\HttpFoundation\Response;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Service\ProposalManager;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Proposal;
use Mobicoop\Bundle\MobicoopBundle\ExternalJourney\Service\ExternalJourneyManager;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;

/**
 * Controller class for carpooling related actions.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class CarpoolController extends AbstractController
{
    /**
     * Create a carpooling ad.
     * @IsGranted("ROLE_USER")
     */
    public function ad(AdManager $adManager, UserManager $userManager, Request $request)
    {
        $ad = new Ad();
        $ad->setRole(Ad::ROLE_BOTH);
        $ad->setType(Ad::TYPE_ONE_WAY);
        $ad->setFrequency(Ad::FREQUENCY_PUNCTUAL);
        $ad->setPrice(Ad::PRICE);
        $ad->setUser($userManager->getLoggedUser());

        $form = $this->createForm(AdForm::class, $ad, ['csrf_protection' => false]);
        $error = false;
        $success = false;
        
        if ($request->isMethod('POST')) {
            $createToken = $request->request->get('createToken');
            if (!$this->isCsrfTokenValid('ad-create', $createToken)) {
                return  new Response('Broken Token CSRF ', 403);
            }
            $form->submit($request->request->get($form->getName()));
            // $form->submit($request->request->all());
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
            foreach ($form as $child) {
                if (!$child->isValid()) {
                    foreach ($child->getErrors(true) as $err) {
                        $error[$child->getName()][] = $err->getMessage();
                    }
                }
            }
            return $this->json(['error' => $error, 'success'=> $success]);
        }

        // Error happen durring proposol creation
        try {
            $proposal = $adManager->createProposalFromAd($ad);
            $success = true;
        } catch (Error $err) {
            $error = $err;
        }

        return $this->json(['error' => $error, 'success'=> $success, 'proposal' => $proposal->getId()]);
    }

    /**
     * Simple search results.
     */
    public function simpleSearchResults($origin, $destination, $origin_latitude, $origin_longitude, $destination_latitude, $destination_longitude, $date, ProposalManager $proposalManager)
    {
        return $this->render('@Mobicoop/search/simple_results.html.twig', [
            'origin' => urldecode($origin),
            'destination' => urldecode($destination),
            'origin_latitude' => urldecode($origin_latitude),
            'origin_longitude' => urldecode($origin_longitude),
            'destination_latitude' => urldecode($destination_latitude),
            'destination_longitude' => urldecode($destination_longitude),
            'date' =>  \Datetime::createFromFormat("YmdHis", $date)->format('d/m/Y à H:i'),
            'hydra' => $proposalManager->getMatchingsForSearch($origin_latitude, $origin_longitude, $destination_latitude, $destination_longitude, \Datetime::createFromFormat("YmdHis", $date)),
        ]);
    }

    /**
     * Provider rdex
     */
    public function rdexProvider(ExternalJourneyManager $externalJourneyManager)
    {   
        $test = $externalJourneyManager->getExternalJourneyProviders(DataProvider::RETURN_JSON);
        return $this->json($test);
    }

    /**
     * Journey rdex
     */
    public function rdexJourney(ExternalJourneyManager $externalJourneyManager, Request $request)
     {   
         //var_dump($request->query->get('provider'));
    //     exit;
        $params = [
            'provider' => $request->query->get('provider'),
            'driver'=> $request->query->get('driver'),
            'passenger'=> $request->query->get('pssenger'),
            'from_latitude'=> $request->query->get('from_latitude'),
            'from_longitude'=> $request->query->get('from_longitude'),
            'to_latitude'=> $request->query->get('to_latitude'),
            'to_longitude'=> $request->query->get('to_longitude')
        ];
        return $this->json($externalJourneyManager->getExternalJourney($params,DataProvider::RETURN_JSON));
    }

    /**
     * Ad post results.
     */
    public function adPostResults($id, ProposalManager $proposalManager)
    {
        $proposal = $proposalManager->getProposal($id);

        // foreach ($proposal->getMatchingOffers() as $matching) {
        //     if ($matching->getProposalOffer() instanceof Proposal) {
        //         if (!$matching->getProposalOffer()->getUser() instanceof User) {
        //             $proposalOffer = $proposalManager->getProposal($matching->getProposalOffer()->getId());
        //             $matching->getProposalOffer()->setUser($proposalOffer->getUser());
        //         }
        //     }
        //     if ($matching->getProposalRequest() instanceof Proposal) {
        //         if (!$matching->getProposalRequest()->getUser() instanceof User) {
        //             $proposalRequest = $proposalManager->getProposal($matching->getProposalRequest()->getId());
        //             $matching->getProposalRequest()->setUser($proposalRequest->getUser());
        //         }
        //     }
        // }
        // foreach ($proposal->getMatchingRequests() as $matching) {
        //     if ($matching->getProposalOffer() instanceof Proposal) {
        //         if (!$matching->getProposalOffer()->getUser() instanceof User) {
        //             $proposalOffer = $proposalManager->getProposal($matching->getProposalOffer()->getId());
        //             $matching->getProposalOffer()->setUser($proposalOffer->getUser());
        //         }
        //     }
        //     if ($matching->getProposalRequest() instanceof Proposal) {
        //         if (!$matching->getProposalRequest()->getUser() instanceof User) {
        //             $proposalRequest = $proposalManager->getProposal($matching->getProposalRequest()->getId());
        //             $matching->getProposalRequest()->setUser($proposalRequest->getUser());
        //         }
        //     }
        // }

        return $this->render('@Mobicoop/proposal/ad_results.html.twig', [
            'proposal' => $proposal
        ]);
    }
}
