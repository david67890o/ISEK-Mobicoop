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

namespace Mobicoop\Bundle\MobicoopBundle\Community\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Mobicoop\Bundle\MobicoopBundle\Community\Entity\Community;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\Permission\Service\PermissionManager;

class CommunityVoter extends Voter
{
    const LIST = 'list';
    const CREATE = 'create';
    const SHOW = 'show';
    
    private $permissionManager;

    public function __construct(PermissionManager $permissionManager)
    {
        $this->permissionManager = $permissionManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::LIST,
            self::CREATE,
            self::SHOW
            ])) {
            return false;
        }

        // only vote on Ad objects inside this voter
        if (!$subject instanceof Community) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            $user = null;
        }

        $community = $subject;

        switch ($attribute) {
            case self::LIST:
                return $this->canList($user);            
            case self::CREATE:
                return $this->canCreate($user);            
            case self::SHOW:
                return $this->canShow($user);            
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canList(?User $user=null)
    {
        return $this->permissionManager->checkPermission('community_list', $user);
    }

    private function canCreate(?User $user=null)
    {
        return $this->permissionManager->checkPermission('community_create', $user);
    }

    private function canShow(?User $user=null)
    {
        return $this->permissionManager->checkPermission('community_read', $user);
    }

}
