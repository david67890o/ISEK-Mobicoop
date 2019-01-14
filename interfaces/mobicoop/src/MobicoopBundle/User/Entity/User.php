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

namespace Mobicoop\Bundle\MobicoopBundle\User\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Mobicoop\Bundle\MobicoopBundle\Api\Entity\Resource;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Proposal;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Ask;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

/**
 * A user.
 */
class User implements Resource, UserInterface, EquatableInterface
{
    const MAX_DEVIATION_TIME = 600;
    const MAX_DEVIATION_DISTANCE = 10000;
    
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = 2;
    const STATUS_ANONYMIZED = 3;
    
    /**
     * @var int The id of this user.
     */
    private $id;
    
    /**
     * @var string|null The iri of this user.
     *
     * @Groups({"post","put"})
     */
    private $iri;
    
    /**
     * @var int User status (1 = active; 2 = disabled; 3 = anonymized).
     */
    private $status;
    
    /**
     * @var string|null The first name of the user.
     *
     * @Groups({"post","put"})
     */
    private $givenName;
    
    /**
     * @var string|null The family name of the user.
     *
     * @Groups({"post","put"})
     */
    private $familyName;
    
    /**
     * @var string The email of the user.
     *
     * @Groups({"post","put"})
     *
     * @Assert\NotBlank
     * @Assert\Email()
     */
    private $email;
    
    /**
     * @var string|null The encoded password of the user.
     *
     * @Groups({"post","put"})
     *
     * @Assert\NotBlank(groups={"signUp","updatePassword"})
     */
    private $password;
    
    /**
     * @var string|null The gender of the user.
     *
     * @Groups({"post","put"})
     */
    private $gender;
    
    /**
     * @var string|null The nationality of the user.
     *
     * @Groups({"post","put"})
     */
    private $nationality;
    
    /**
     * @var \DateTimeInterface|null The birth date of the user.
     *
     * @Groups({"post","put"})
     *
     * @Assert\Date()
     */
    private $birthDate;
    
    /**
     * @var string|null The telephone number of the user.
     *
     * @Groups({"post","put"})
     */
    private $telephone;
    
    /**
     * @var int|null The maximum deviation time (in seconds) as a driver to accept a request proposal.
     *
     * @Groups({"post","put"})
     */
    private $maxDeviationTime;
    
    /**
     * @var int|null The maximum deviation distance (in metres) as a driver to accept a request proposal.
     *
     * @Groups({"post","put"})
     */
    private $maxDeviationDistance;
    
    /**
     * @var boolean|null The user accepts any route as a passenger from its origin to the destination.
     */
    private $anyRouteAsPassenger;
    
    /**
     * @var boolean|null The user accepts any transportation mode.
     */
    private $multiTransportMode;
    
    /**
     * @var Address[]|null A user may have many addresses.
     */
    private $addresses;
    
    /**
     * @var Car[]|null A user may have many cars.
     */
    private $cars;

    /**
     * @var Proposal[]|null The proposals made by this user.
     */
    private $proposals;

    /**
     * @var Ask[]|null The asks made by this user.
     */
    private $asks;
        
    public function __construct($id=null, $status=null)
    {
        if ($id) {
            $this->setId($id);
            $this->setIri("/users/".$id);
        }
        $this->addresses = new ArrayCollection();
        $this->cars = new ArrayCollection();
        $this->proposals = new ArrayCollection();
        $this->asks = new ArrayCollection();
        if (is_null($status)) {
            $status = self::STATUS_ACTIVE;
        }
        $this->setStatus($status);
    }
        
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId(int $id)
    {
        $this->id = $id;
    }
    
    public function getIri()
    {
        return $this->iri;
    }

    public function setIri($iri)
    {
        $this->iri = $iri;
    }
    
    public function getStatus(): int
    {
        return $this->status;
    }
    
    public function setStatus(int $status): self
    {
        $this->status = $status;
        
        return $this;
    }
    
    public function getGivenName(): ?string
    {
        return $this->givenName;
    }
    
    public function setGivenName(?string $givenName): self
    {
        $this->givenName = $givenName;
        
        return $this;
    }
    
    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }
    
    public function setFamilyName(?string $familyName): self
    {
        $this->familyName = $familyName;
        
        return $this;
    }
    
    public function getEmail(): ?string
    {
        return $this->email;
    }
    
    public function setEmail(string $email): self
    {
        $this->email = $email;
        
        return $this;
    }
    
    public function getPassword(): ?string
    {
        return $this->password;
    }
    
    public function setPassword(?string $password): self
    {
        $this->password = $password;
        
        return $this;
    }
    
    public function getGender(): ?string
    {
        return $this->gender;
    }
    
    public function setGender(?string $gender): self
    {
        $this->gender = $gender;
        
        return $this;
    }
    
    public function getNationality(): ?string
    {
        return $this->nationality;
    }
    
    public function setNationality(?string $nationality): self
    {
        $this->nationality = $nationality;
        
        return $this;
    }
    
    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }
    
    public function setBirthDate(?\DateTimeInterface $birthDate): self
    {
        $this->birthDate = $birthDate;
        
        return $this;
    }
    
    public function getTelephone(): ?string
    {
        return $this->telephone;
    }
    
    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;
        
        return $this;
    }
    
    public function getMaxDeviationTime(): int
    {
        return (!is_null($this->maxDeviationTime) ? $this->maxDeviationTime : self::MAX_DEVIATION_TIME);
    }
    
    public function setMaxDeviationTime(?int $maxDeviationTime): self
    {
        $this->maxDeviationTime = $maxDeviationTime;
        
        return $this;
    }
    
    public function getMaxDeviationDistance(): int
    {
        return (!is_null($this->maxDeviationDistance) ? $this->maxDeviationDistance : self::MAX_DEVIATION_DISTANCE);
    }
    
    public function setMaxDeviationDistance(?int $maxDeviationDistance): self
    {
        $this->maxDeviationDistance = $maxDeviationDistance;
        
        return $this;
    }
    
    public function getAnyRouteAsPassenger(): bool
    {
        return (!is_null($this->anyRouteAsPassenger) ? $this->anyRouteAsPassenger : true);
    }
    
    public function setAnyRouteAsPassenger(?bool $anyRouteAsPassenger): self
    {
        $this->anyRouteAsPassenger = $anyRouteAsPassenger;
        
        return $this;
    }
    
    public function getMultiTransportMode(): bool
    {
        return (!is_null($this->multiTransportMode) ? $this->multiTransportMode : true);
    }
    
    public function setMultiTransportMode(?bool $multiTransportMode): self
    {
        $this->multiTransportMode = $multiTransportMode;
        
        return $this;
    }
    
    public function getAddresses(): Collection
    {
        return $this->addresses;
    }
    
    public function addAddress(Address $address): self
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses->add($address);
            $address->setUser($this);
        }
        
        return $this;
    }
    
    public function removeAddress(Address $address): self
    {
        if ($this->addresses->contains($address)) {
            $this->addresses->removeElement($address);
            // set the owning side to null (unless already changed)
            if ($address->getUser() === $this) {
                $address->setUser(null);
            }
        }
        
        return $this;
    }
    
    public function getCars(): Collection
    {
        return $this->cars;
    }
    
    public function addCar(Car $car): self
    {
        if (!$this->cars->contains($car)) {
            $this->cars->add($car);
            $car->setUser($this);
        }
        
        return $this;
    }
    
    public function removeCar(Car $car): self
    {
        if ($this->cars->contains($car)) {
            $this->cars->removeElement($car);
            // set the owning side to null (unless already changed)
            if ($car->getUser() === $this) {
                $car->setUser(null);
            }
        }
        
        return $this;
    }
    
    public function getProposals(): Collection
    {
        return $this->proposals;
    }
    
    public function addProposal(Proposal $proposal): self
    {
        if (!$this->proposals->contains($proposal)) {
            $this->proposals->add($proposal);
            $proposal->setUser($this);
        }
        
        return $this;
    }
    
    public function removeProposal(Proposal $proposal): self
    {
        if ($this->proposals->contains($proposal)) {
            $this->proposals->removeElement($proposal);
            // set the owning side to null (unless already changed)
            if ($proposal->getUser() === $this) {
                $proposal->setUser(null);
            }
        }
        
        return $this;
    }
    
    public function getAsks(): Collection
    {
        return $this->asks;
    }
    
    public function addAsk(Ask $ask): self
    {
        if (!$this->asks->contains($ask)) {
            $this->asks->add($ask);
            $ask->setUser($this);
        }
        
        return $this;
    }
    
    public function removeAsk(Ask $ask): self
    {
        if ($this->asks->contains($ask)) {
            $this->asks->removeElement($ask);
            // set the owning side to null (unless already changed)
            if ($ask->getUser() === $this) {
                $ask->setUser(null);
            }
        }
        
        return $this;
    }


    public function getRoles()
    {
        return array('ROLE_USER');
    }


    public function getSalt()
    {
        return  null;
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function eraseCredentials()
    {
    }

    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof User) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->email !== $user->getUsername()) {
            return false;
        }

        return true;
    }
}
