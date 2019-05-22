<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

namespace Mobicoop\Bundle\MobicoopBundle\Community\Entity;

use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\Collection;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;

/**
 * A user related to a community.
 */
class CommunityUser
{
    const STATUS_PENDING = 0;
    const STATUS_ACCEPTED = 1;
    const STATUS_REFUSED = 2;

    /**
     * @var int The id of this community user.
     *
     * @Groups("post")
     */
    private $id;
        
    /**
     * @var Community The community.
     *
     * @Groups({"post","put"})
     */
    private $community;

    /**
     * @var User The user.
     *
     * @Groups({"post","put"})
     */
    private $user;

    /**
     * @var int The status of the event (active/inactive).
     *
     * @Groups({"post","put"})
     */
    private $status;
    
    /**
     * @var User The user that validates/invalidates the registration.
     *
     * @Groups({"post","put"})
     */
    private $admin;

    /**
    * @var \DateTimeInterface Creation date of the community user.
    *
    */
    private $createdDate;

    /**
    * @var \DateTimeInterface Accepted date.
    *
    */
    private $acceptedDate;

    /**
    * @var \DateTimeInterface Refusal date.
    *
    */
    private $refusedDate;
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommunity(): ?Community
    {
        return $this->community;
    }

    public function setCommunity(?Community $community): self
    {
        $this->community = $community;
        
        return $this;
    }
        
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        
        return $this;
    }
    
    public function getStatus()
    {
        return $this->status;
    }
    
    public function setStatus(?int $status)
    {
        $this->status = $status;
    }

    public function getAdmin(): ?User
    {
        return $this->admin;
    }

    public function setAdmin(?User $admin): self
    {
        $this->admin = $admin;
        
        return $this;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }
    
    public function setCreatedDate(\DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;
        
        return $this;
    }

    public function getAcceptedDate(): ?\DateTimeInterface
    {
        return $this->acceptedDate;
    }
    
    public function setAcceptedDate(?\DateTimeInterface $acceptedDate): self
    {
        $this->acceptedDate = $acceptedDate;
        
        return $this;
    }

    public function getRefusedDate(): ?\DateTimeInterface
    {
        return $this->refusedDate;
    }
    
    public function setRefusedDate(?\DateTimeInterface $refusedDate): self
    {
        $this->refusedDate = $refusedDate;
        
        return $this;
    }
}
