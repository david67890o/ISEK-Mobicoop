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


namespace Mobicoop\Bundle\MobicoopBundle\Community\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Mobicoop\Bundle\MobicoopBundle\Image\Entity\Image;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Proposal;

/**
 *
 */
class Community
{
    /**
     * @var int The id of this community.
     * @Groups("post")
     */
    private $id;
    
    /**
     * @var string The name of the community.
     *
     * @Groups({"post","put"})
     */
    private $name;

    /**
     * @var boolean|null The community is private.
     *
     * @Groups({"post","put"})
     */
    private $private;
    
    /**
     * @var string The short description of the community.
     *
     * @Groups({"post","put"})
     */
    private $description;
    
    /**
     * @var string The full description of the community.
     *
     * @Groups({"post","put"})
     */
    private $fullDescription;
    
    /**
    * @var \DateTimeInterface Creation date of the event.
    *
    * @Assert\Date()
    * @Groups("post")
    */
    private $createdDate;
    
    /**
     * @var User The creator of the community.
     *
     * @Assert\NotBlank
     * @Groups({"post","put"})
     */
    private $user;
    
    /**
     * @var Image[]|null The images of the community.
     *
     * @Groups({"post","put"})
     */
    private $images;

    /**
     * @var Proposal[]|null The proposals in this community.
     *
     * @Groups({"post","put"})
     */
    private $proposals;

    /**
     * @var CommunityUser[]|null The members of the community.
     *
     * @Groups({"post","put"})
     */
    private $communityUsers;
    
    public function __construct($id=null)
    {
        if ($id) {
            $this->setId($id);
        }
        $this->images = new ArrayCollection();
        $this->proposals = new ArrayCollection();
        $this->communityUsers = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function isPrivate(): ?bool
    {
        return $this->private;
    }
    
    public function setPrivate(bool $isPrivate): self
    {
        $this->private = $isPrivate;
        
        return $this;
    }
    
    public function getDescription(): string
    {
        return $this->description;
    }
    
    public function setDescription(string $description)
    {
        $this->description = $description;
    }
    
    public function getFullDescription(): string
    {
        return $this->fullDescription;
    }
    
    public function setFullDescription(string $fullDescription)
    {
        $this->fullDescription = $fullDescription;
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
    
    public function getUser(): User
    {
        return $this->user;
    }
    
    public function setUser(User $user): self
    {
        $this->user = $user;
        
        return $this;
    }
    
    /**
     *
     * @return Collection|Image[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }
    
    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setCommunity($this);
        }
        
        return $this;
    }
    
    public function removeImage(Image $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getCommunity() === $this) {
                $image->setCommunity(null);
            }
        }
        
        return $this;
    }

    /**
     * @return Collection|Proposal[]
     */
    public function getProposals(): Collection
    {
        return $this->proposals;
    }
    
    public function addProposal(Proposal $proposal): self
    {
        if (!$this->proposals->contains($proposal)) {
            $this->proposals[] = $proposal;
        }
        
        return $this;
    }
    
    public function removeProposal(Proposal $proposal): self
    {
        if ($this->proposals->contains($proposal)) {
            $this->proposals->removeElement($proposal);
        }
        
        return $this;
    }

    /**
     *
     * @return Collection|CommunityUser[]
     */
    public function getCommunityUsers(): Collection
    {
        return $this->communityUsers;
    }
    
    public function addCommunityUser(CommunityUser $communityUser): self
    {
        if (!$this->communityUsers->contains($communityUser)) {
            $this->communityUsers[] = $communityUser;
            $communityUser->setCommunity($this);
        }
        
        return $this;
    }
    
    public function removeCommunityUser(CommunityUser $communityUser): self
    {
        if ($this->communityUsers->contains($communityUser)) {
            $this->communityUsers->removeElement($communityUser);
            // set the owning side to null (unless already changed)
            if ($communityUser->getCommunity() === $this) {
                $communityUser->setCommunity(null);
            }
        }
        
        return $this;
    }
}
