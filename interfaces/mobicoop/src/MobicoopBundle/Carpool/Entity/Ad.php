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

namespace Mobicoop\Bundle\MobicoopBundle\Carpool\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\Travel\Entity\TravelMode;

/**
 * Carpooling : an ad on the platform (offer from a driver / request from a passenger).
 * This entity is used to simplify the process and give all the requested fields on one entity, instead of creating nested forms.
 */
class Ad
{
    const ROLE_DRIVER = 1;
    const ROLE_PASSENGER = 2;
    const ROLE_BOTH = 3;

    const ROLES = [
        "driver"=>self::ROLE_DRIVER,
        "passenger"=>self::ROLE_PASSENGER,
        "both"=>self::ROLE_BOTH
    ];

    const TYPE_ONE_WAY = 1;
    const TYPE_RETURN_TRIP = 2;

    const TYPES = [
            "one_way"=>self::TYPE_ONE_WAY,
            "return_trip"=>self::TYPE_RETURN_TRIP
    ];

    const FREQUENCY_PUNCTUAL = 1;
    const FREQUENCY_REGULAR = 2;

    const FREQUENCIES = [
        "punctual"=>self::FREQUENCY_PUNCTUAL,
        "regular"=>self::FREQUENCY_REGULAR
    ];

    /**
     * @var string The origin of the travel.
     *
     * @Assert\NotBlank
     */
    private $origin;

    /**
     * @var string The destination of the travel.
     *
     * @Assert\NotBlank
     */
    private $destination;

    /**
     * @var int The ad role (driver / passenger / both).
     *
     * @Assert\NotBlank
     */
    private $role;

    /**
    * @var int The ad type (one way / return trip).
     *
     * @Assert\NotBlank
    */
    private $type;

    /**
     * @var int The frequency of the ad (punctual / regular).
     *
     * @Assert\NotBlank
     */
    private $frequency;

    /**
     * @var User The user who submits the ad.
     */
    private $user;

    /**
     * @var string The latitude of the origin of the travel.
     */
    private $originLatitude;

    /**
     * @var string The longitude of the origin of the travel.
     */
    private $originLongitude;

    /**
     * @var string The latitude of the destination of the travel.
     */
    private $destinationLatitude;

    /**
     * @var string The longitude of the destination of the travel.
     */
    private $destinationLongitude;

    public function getOrigin(): ?string
    {
        return $this->origin;
    }

    public function setOrigin(?string $origin): self
    {
        $this->origin = $origin;

        return $this;
    }

    public function getDestination(): ?string
    {
        return $this->destination;
    }

    public function setDestination(?string $destination): self
    {
        $this->destination = $destination;

        return $this;
    }

    public function getRole(): ?int
    {
        return $this->role;
    }

    public function setRole(int $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }
    
    public function setType(int $type): self
    {
        $this->type = $type;
        
        return $this;
    }

    public function getFrequency(): ?int
    {
        return $this->frequency;
    }

    public function setFrequency(int $frequency): self
    {
        $this->frequency = $frequency;

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

    public function getOriginLatitude(): ?float
    {
        return $this->originLatitude;
    }

    public function setOriginLatitude(float $originLatitude): self
    {
        $this->originLatitude = $originLatitude;

        return $this;
    }

    public function getOriginLongitude(): ?float
    {
        return $this->originLongitude;
    }

    public function setOriginLongitude(float $originLongitude): self
    {
        $this->originLongitude = $originLongitude;

        return $this;
    }

    public function getDestinationLatitude(): ?float
    {
        return $this->destinationLatitude;
    }

    public function setDestinationLatitude(float $destinationLatitude): self
    {
        $this->destinationLatitude = $destinationLatitude;

        return $this;
    }

    public function getDestinationLongitude(): ?float
    {
        return $this->destinationLongitude;
    }

    public function setDestinationLongitude(float $destinationLongitude): self
    {
        $this->destinationLongitude = $destinationLongitude;

        return $this;
    }

}
