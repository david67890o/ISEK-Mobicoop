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

namespace Mobicoop\Bundle\MobicoopBundle\User\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\User\Form\UserForm;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Service\ProposalManager;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Proposal;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Form\ProposalForm;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\Form\Login;
use Mobicoop\Bundle\MobicoopBundle\User\Form\UserLoginForm;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Mobicoop\Bundle\MobicoopBundle\User\Form\UserDeleteForm;

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
    public function login()
    {
        $login = new Login();

        $form = $this->createForm(UserLoginForm::class, $login);

        return $this->render('@Mobicoop/user/login.html.twig', ["form"=>$form->createView()]);
    }

    /**
     * User registration.
     */
    public function userSignUp(UserManager $userManager, Request $request)
    {
        $user = new User();

        $form = $this->createForm(
            UserForm::class,
            $user,
            ['validation_groups'=>['signUp']]
        );

        $form->handleRequest($request);
        $error = false;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($user = $userManager->createUser($user)) {
                // after successful registering, we log the user
                $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                $this->get('security.token_storage')->setToken($token);
                $this->get('session')->set('_security_main', serialize($token));
                // redirection to the user profile page
                return $this->redirectToRoute('home');
            }
            $error = true;
        }

        return $this->render('@Mobicoop/user/signup.html.twig', [
            'form' => $form->createView(),
            'error' => $error
        ]);
    }

    /**
     * User profile (get the current user).
     */
    public function userProfile(UserManager $userManager)
    {
        return $this->render('@Mobicoop/user/detail.html.twig', [
            'user' => $userManager->getLoggedUser()
        ]);
    }

    /**
     * User profile update.
     */
    public function userProfileUpdate(UserManager $userManager, Request $request)
    {
        // we clone the logged user to avoid getting logged out in case of error in the form
        $user = $userManager->getLoggedUser();

        $form = $this->createForm(
            UserForm::class,
            $user,
            ['validation_groups'=>['update']]
        );

        $form->handleRequest($request);
        $error = false;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($user = $userManager->updateUser($user)) {
                // after successful update, we re-log the user
                $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                $this->get('security.token_storage')->setToken($token);
                $this->get('session')->set('_security_main', serialize($token));
                return $this->redirectToRoute('user_profile');
            }
            $error = true;
        }

        return $this->render('@Mobicoop/user/update.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'error' => $error
        ]);
    }

    /**
     * User password update.
     */
    public function userPasswordUpdate(UserManager $userManager, Request $request)
    {
        // we clone the logged user to avoid getting logged out in case of error in the form
        $user = clone $userManager->getLoggedUser();

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
     * Retrieve all proposals for a user.
     *
     * @Route("/user/{id}/proposals", name="user_proposals", requirements={"id"="\d+"})
     *
     */
    public function userProposals($id, ProposalManager $proposalManager)
    {
        $user = new User($id);
        return $this->render('@Mobicoop/proposal/index.html.twig', [
            'user' => $user,
            'hydra' => $proposalManager->getProposals($user)
        ]);
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
