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

namespace App\App\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\ArrayCollection;
use App\Right\Entity\Role;

/**
 * An app which can access to the api (front, mobile app...).
 *
 * @ORM\Entity
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"}
 *      },
 *      collectionOperations={"get"},
 *      itemOperations={"get"}
 * )
 */
class App
{
    // default role
    const DEFAULT_ROLE = 4;
    
    /**
     * @var int The id of this app.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;
            
    /**
     * @var string|null The name of the app.
     *
     * @ORM\Column(type="string", length=45)
     * @Groups("read")
     */
    private $name;

    /**
     * @var Role[]|null The roles granted to the app.
     *
     * @ORM\ManyToMany(targetEntity="\App\Right\Entity\Role")
     * @Groups({"read","write"})
     */
    private $roles;
    
    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
        
    public function getName(): ?string
    {
        return $this->name;
    }
    
    public function setName(?string $name): self
    {
        $this->name = $name;
        
        return $this;
    }

    /**
     * @return Collection|Role[]
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }
    
    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
        }
        
        return $this;
    }
    
    public function removeRole(Role $role): self
    {
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);
        }
        
        return $this;
    }
}
