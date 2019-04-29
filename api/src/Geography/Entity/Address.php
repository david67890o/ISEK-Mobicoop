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

namespace App\Geography\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use App\Carpool\Entity\WayPoint;
use App\User\Entity\User;

/**
 * A postal address.
 *
 * @ORM\Entity
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read","pt"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={},
 *      itemOperations={"get"}
 * )
 * @ApiFilter(OrderFilter::class, properties={"id", "streetAddress", "postalCode", "addressLocality", "addressCountry"}, arguments={"orderParameterName"="order"})
 */
class Address
{
    const DEFAULT_ID = 999999999999;

    /**
     * @var int The id of this address.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var string The house number.
     *
     * @ORM\Column(type="string", length=45, nullable=true)
     * @Groups({"read","write","pt"})
     */
    private $houseNumber;

    /**
     * @var string The street.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read","write","pt"})
     * @Assert\NotBlank(groups={"mass"})
     */
    private $street;

    /**
     * @var string The full street address.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read","write","pt"})
     * @Assert\NotBlank(groups={"mass"})
     */
    private $streetAddress;

    /**
     * @var string|null The postal code of the address.
     *
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Groups({"read","write","pt"})
     * @Assert\NotBlank(groups={"mass"})
     */
    private $postalCode;

    /**
     * @var string|null The sublocality of the address.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","write","pt"})
     */
    private $subLocality;

    /**
     * @var string|null The locality of the address.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","write","pt"})
     * @Assert\NotBlank(groups={"mass"})
     */
    private $addressLocality;

    /**
     * @var string|null The locality admin of the address.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","write","pt"})
     */
    private $localAdmin;

    /**
     * @var string|null The county of the address.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","write","pt"})
     */
    private $county;

    /**
     * @var string|null The macro county of the address.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","write","pt"})
     */
    private $macroCounty;

    /**
     * @var string|null The region of the address.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","write","pt"})
     */
    private $region;

    /**
     * @var string|null The macro region of the address.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","write","pt"})
     */
    private $macroRegion;

    /**
     * @var string|null The country of the address.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","write","pt"})
     */
    private $addressCountry;

    /**
     * @var string|null The country code of the address.
     *
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Groups({"read","write","pt"})
     */
    private $countryCode;

    /**
     * @var float|null The latitude of the address.
     *
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     * @Groups({"read","write","pt"})
     */
    private $latitude;

    /**
     * @var float|null The longitude of the address.
     *
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     * @Groups({"read","write","pt"})
     */
    private $longitude;

    /**
     * @var int|null The elevation of the address in metres.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","pt"})
     */
    private $elevation;

    /**
     * @var string|null The name of this address.
     *
     * @ORM\Column(type="string", length=45, nullable=true)
     * @Groups({"read","write"})
     */
    private $name;

    /**
     * @var User|null The owner of the address.
     *
     * @ORM\ManyToOne(targetEntity="App\User\Entity\User", inversedBy="addresses")
     */
    private $user;

    public function __construct($id = null)
    {
        $this->id = self::DEFAULT_ID;
        if ($id) {
            $this->id = $id;
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getHouseNumber(): ?string
    {
        return $this->houseNumber;
    }

    public function setHouseNumber(?string $houseNumber)
    {
        $this->houseNumber = $houseNumber;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street)
    {
        $this->street = $street;
    }

    public function getStreetAddress(): ?string
    {
        return $this->streetAddress;
    }

    public function setStreetAddress(?string $streetAddress)
    {
        $this->streetAddress = $streetAddress;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode)
    {
        $this->postalCode = $postalCode;
    }

    public function getSubLocality(): ?string
    {
        return $this->subLocality;
    }

    public function setSubLocality(?string $subLocality)
    {
        $this->subLocality = $subLocality;
    }

    public function getAddressLocality(): ?string
    {
        return $this->addressLocality;
    }

    public function setAddressLocality(?string $addressLocality)
    {
        $this->addressLocality = $addressLocality;
    }

    public function getLocalAdmin(): ?string
    {
        return $this->localAdmin;
    }

    public function setLocalAdmin(?string $localAdmin)
    {
        $this->localAdmin = $localAdmin;
    }

    public function getCounty(): ?string
    {
        return $this->county;
    }

    public function setCounty(?string $county)
    {
        $this->county = $county;
    }

    public function getMacroCounty(): ?string
    {
        return $this->macroCounty;
    }

    public function setMacroCounty(?string $macroCounty)
    {
        $this->macroCounty = $macroCounty;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region)
    {
        $this->region = $region;
    }

    public function getMacroRegion(): ?string
    {
        return $this->macroRegion;
    }

    public function setMacroRegion(?string $macroRegion)
    {
        $this->macroRegion = $macroRegion;
    }

    public function getAddressCountry(): ?string
    {
        return $this->addressCountry;
    }

    public function setAddressCountry(?string $addressCountry)
    {
        $this->addressCountry = $addressCountry;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(?string $countryCode)
    {
        $this->countryCode = $countryCode;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude)
    {
        $this->latitude = $latitude;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude)
    {
        $this->longitude = $longitude;
    }

    public function getElevation(): ?int
    {
        return $this->elevation;
    }

    public function setElevation(?int $elevation)
    {
        $this->elevation = $elevation;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name)
    {
        $this->name = $name;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user)
    {
        $user->setAddress($this);
        $this->user = $user;
    }
}
