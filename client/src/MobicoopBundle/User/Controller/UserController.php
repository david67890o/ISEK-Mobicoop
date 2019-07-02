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

namespace Mobicoop\Bundle\MobicoopBundle\User\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\User\Form\UserForm;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Service\ProposalManager;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Proposal;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Form\ProposalForm;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\Form\Login;
use Mobicoop\Bundle\MobicoopBundle\User\Form\UserLoginForm;
use Mobicoop\Bundle\MobicoopBundle\User\Form\UserDeleteForm;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;
use Mobicoop\Bundle\MobicoopBundle\Geography\Service\AddressManager;
use Symfony\Component\HttpFoundation\Response;
use DateTime;

/**
 * Controller class for user related actions.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 *
 */
class UserController extends AbstractController
{

    /**
     * User login.
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        $this->denyAccessUnlessGranted('login');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        
        $login = new Login();

        $form = $this->createForm(UserLoginForm::class, $login);

        return $this->render('@Mobicoop/user/login.html.twig', [
            "form"=>$form->createView(),
            "error"=>$error
            ]);
    }

    /**
     * User registration.
     */
    public function userSignUp(UserManager $userManager, Request $request)
    {
        $this->denyAccessUnlessGranted('register');

        $user = new User();
        $address = new Address();
        $form = $this->createForm(UserForm::class, $user, ['validation_groups'=>['signUp']]);
        $error = false;
        $success = false;
        
        if ($request->isMethod('POST')) {
            $createToken = $request->request->get('createToken');
            if (!$this->isCsrfTokenValid('user-signup', $createToken)) {
                return  new Response('Broken Token CSRF ', 403);
            }

            //get all data from form (user + homeAddress)
            $data = $request->request->get($form->getName());
            
            // pass homeAddress info into address entity
            $address->setAddressCountry($data['addressCountry']);
            $address->setAddressLocality($data['addressLocality']);
            $address->setCountryCode($data['countryCode']);
            $address->setCounty($data['county']);
            $address->setLatitude($data['latitude']);
            $address->setLocalAdmin($data['localAdmin']);
            $address->setLongitude($data['longitude']);
            $address->setMacroCounty($data['macroCounty']);
            $address->setMacroRegion($data['macroRegion']);
            $address->setName($data['name']);
            $address->setPostalCode($data['postalCode']);
            $address->setRegion($data['region']);
            $address->setStreet($data['street']);
            $address->setStreetAddress($data['streetAddress']);
            $address->setSubLocality($data['subLocality']);

            // pass front info into user form
            $user->setEmail($data['email']);
            $user->setTelephone($data['telephone']);
            $user->setPassword($data['password']);
            $user->setGivenName($data['givenName']);
            $user->setFamilyName($data['familyName']);
            $user->setGender($data['gender']);

            $user->setBirthYear($data['birthYear']);


            // add the home address to the user
            $user->addAddress($address);

            // Not Valid populate error
            // if (!$form->isValid()) {
            //     $error = [];
            //     // Fields
            //     foreach ($form as $child) {
            //         if (!$child->isValid()) {
            //             foreach ($child->getErrors(true) as $err) {
            //                 $error[$child->getName()][] = $err->getMessage();
            //             }
            //         }
            //     }
            //     return $this->json(['error' => $error, 'success' => $success]);
            // }

            // create user in database
            $userManager->createUser($user);
        }

        if (!$form->isSubmitted()) {
            return $this->render('@Mobicoop/user/signup.html.twig', [
                'error' => $error
            ]);
        }
        return $this->json(['error' => $error, 'success' => $success]);
    }

    /**
     * User profile (get the current user).
     */
    public function userProfile(UserManager $userManager)
    {
        $user = $userManager->getLoggedUser();
        $this->denyAccessUnlessGranted('profile', $user);

        return $this->render('@Mobicoop/user/detail.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * User profile update.
     */
    public function userProfileUpdate(UserManager $userManager, Request $request, AddressManager $addressManager)
    {
        // we clone the logged user to avoid getting logged out in case of error in the form
        $user = clone $userManager->getLoggedUser();
        $this->denyAccessUnlessGranted('update', $user);

        // get addresses of the logged user
        $addresses = $user->getAddresses();
        $homeAddress = [];
        // get the homeAddress
        foreach ($addresses as $address) {
            $homeAddress = null;
            $name = $address->getName();
            if ($name == "homeAddress") {
                $homeAddress = $address;
            }
        }
         
        $form = $this->createForm(UserForm::class, $user, ['validation_groups'=>['update']]);
        $error = false;
           
        
        if ($request->isMethod('POST')) {

            //get all data from form (user + homeAddress)
            $data = $request->request->get($form->getName());

            //pass homeAddress info into address entity
            $homeAddress->setAddressCountry($data['addressCountry']);
            $homeAddress->setAddressLocality($data['addressLocality']);
            $homeAddress->setCountryCode($data['countryCode']);
            $homeAddress->setCounty($data['county']);
            $homeAddress->setLatitude($data['latitude']);
            $homeAddress->setLocalAdmin($data['localAdmin']);
            $homeAddress->setLongitude($data['longitude']);
            $homeAddress->setMacroCounty($data['macroCounty']);
            $homeAddress->setMacroRegion($data['macroRegion']);
            $homeAddress->setPostalCode($data['postalCode']);
            $homeAddress->setRegion($data['region']);
            $homeAddress->setStreet($data['street']);
            $homeAddress->setStreetAddress($data['streetAddress']);
            $homeAddress->setSubLocality($data['subLocality']);

            // pass front info into user form
            $user->setEmail($data['email']);
            $user->setTelephone($data['telephone']);
            $user->setGivenName($data['givenName']);
            $user->setFamilyName($data['familyName']);
            $user->setGender($data['gender']);
            $user->setBirthYear($data['birthYear']);
            
            $addressManager->updateAddress($homeAddress);
            $userManager->updateUser($user);
            
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_main', serialize($token));
            
            return $this->redirectToRoute('user_profile');
        }
      
        return $this->render('@Mobicoop/user/updateProfile.html.twig', [
                'error' => $error,
                'user' => $user
            ]);
    }

  

    /**
     * User password update.
     */
    public function userPasswordUpdate(UserManager $userManager, Request $request)
    {
        // we clone the logged user to avoid getting logged out in case of error in the form
        $user = clone $userManager->getLoggedUser();
        $this->denyAccessUnlessGranted('password', $user);
        $form = $this->createForm(
            UserForm::class,
            $user,
            ['validation_groups'=>['password']]
        );

        $form->handleRequest($request);
        $error = false;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($user = $userManager->updateUserPassword($user)) {
                // after successful update, we re-log the user
                $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                $this->get('security.token_storage')->setToken($token);
                $this->get('session')->set('_security_main', serialize($token));
                return $this->redirectToRoute('user_profile');
            }
            $error = true;
        }

        return $this->render('@Mobicoop/user/password.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'error' => $error
        ]);
    }

    /**
     * Delete the current user.
     */
    public function userProfileDelete(UserManager $userManager, Request $request)
    {
        $user = $userManager->getLoggedUser();
        $this->denyAccessUnlessGranted('delete', $user);

        $form = $this->createForm(
            UserDeleteForm::class,
            $user,
            ['validation_groups'=>['delete']]
        );

        $form->handleRequest($request);
        $error = false;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($userManager->deleteUser($user->getId())) {
                return $this->redirectToRoute('home');
            }
            $error = true;
        }

        return $this->render('@Mobicoop/user/delete.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'error' => $error
        ]);
    }

    /**
     * Retrieve all proposals for the current user.
     */
    public function userProposals(UserManager $userManager, ProposalManager $proposalManager)
    {
        $user = $userManager->getLoggedUser();
        $this->denyAccessUnlessGranted('proposals_self', $user);

        return $this->render('@Mobicoop/proposal/index.html.twig', [
            'hydra' => $proposalManager->getProposals($user)
        ]);
    }

    // ADMIN

    /**
     * Retrieve a user.
     *
     * @Route("/user/{id}", name="user", requirements={"id"="\d+"})
     *
     */
    public function user($id, UserManager $userManager)
    {
        return $this->render('@Mobicoop/user/detail.html.twig', [
            'user' => $userManager->getUser($id)
        ]);
    }

    /**
     * Retrieve all users.
     *
     * @Route("/users", name="users")
     *
     */
    public function users(UserManager $userManager)
    {
        return $this->render('@Mobicoop/user/index.html.twig', [
            'hydra' => $userManager->getUsers()
        ]);
    }

    /**
     * Delete a user.
     *
     * @Route("/user/{id}/delete", name="user_delete", requirements={"id"="\d+"})
     *
     */
    public function userDelete($id, UserManager $userManager)
    {
        if ($userManager->deleteUser($id)) {
            return $this->redirectToRoute('users');
        } else {
            return $this->render('@Mobicoop/user/index.html.twig', [
                    'hydra' => $userManager->getUsers(),
                    'error' => 'An error occured'
            ]);
        }
    }
    
    /**
     * Retrieve all matchings for a proposal.
     *
     * @Route("/user/{id}/proposal/{idProposal}/matchings", name="user_proposal_matchings", requirements={"id"="\d+","idProposal"="\d+"})
     *
     */
    public function userProposalMatchings($id, $idProposal, ProposalManager $proposalManager)
    {
        $user = new User($id);
        $proposal = $proposalManager->getProposal($idProposal);
        return $this->render('@Mobicoop/proposal/matchings.html.twig', [
            'user' => $user,
            'proposal' => $proposal,
            'hydra' => $proposalManager->getMatchings($proposal)
        ]);
    }

    /**
     * Delete a proposal of a user.
     *
     * @Route("/user/{id}/proposal/{idProposal}/delete", name="user_proposal_delete", requirements={"id"="\d+","idProposal"="\d+"})
     *
     */
    public function userProposalDelete($id, $idProposal, ProposalManager $proposalManager)
    {
        if ($proposalManager->deleteProposal($idProposal)) {
            return $this->redirectToRoute('user_proposals', ['id'=>$id]);
        } else {
            $user = new User($id);
            return $this->render('@Mobicoop/proposal/index.html.twig', [
                'user' => $user,
                'hydra' => $proposalManager->getProposals($user),
                'error' => 'An error occured'
            ]);
        }
    }





    /**
     * Create a proposal for a user.
     */
    public function userProposalCreate($id=null, ProposalManager $proposalManager, Request $request)
    {
        $proposal = new Proposal();
        if ($id) {
            $proposal->setUser(new User($id));
        } else {
            $proposal->setUser(new User());
        }

        $form = $this->createForm(ProposalForm::class, $proposal);
        $form->handleRequest($request);
        $error = false;

        if ($form->isSubmitted() && $form->isValid()) {
            // for now we add the starting end ending points,
            // in the future we will need to have dynamic fields
            $proposal->addPoint($proposal->getStart());
            $proposal->addPoint($proposal->getDestination());
            if ($proposal = $proposalManager->createProposal($proposal)) {
                return $this->redirectToRoute('user_proposal_matchings', ['id'=>$id,'idProposal'=>$proposal->getId()]);
            }
            $error = true;
        }

        return $this->render('@Mobicoop/proposal/create.html.twig', [
            'form' => $form->createView(),
            'error' => $error
        ]);
    }
}
