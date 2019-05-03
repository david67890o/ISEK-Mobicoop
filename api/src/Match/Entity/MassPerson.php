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

namespace App\Match\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Geography\Entity\Address;
use App\Geography\Entity\Direction;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A mass matching person.
 *
 * @ORM\Entity
 */
class MassPerson
{
    /**
     * @var int The id of this person.
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string|null The given id of the person.
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"mass"})
     */
    private $givenId;

    /**
     * @var string|null The first name of the person.
     * @ORM\Column(type="string", length=255)
     */
    private $givenName;

    /**
     * @var string|null The family name of the person.
     * @ORM\Column(type="string", length=255)
     */
    private $familyName;

    /**
     * @var Address The personal address of the person.
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Address", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Assert\NotBlank(groups={"mass"})
     * @Assert\Valid
     */
    private $personalAddress;

    /**
     * @var Address The work address of the person.
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Address", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Assert\NotBlank(groups={"mass"})
     * @Assert\Valid
     */
    private $workAddress;

    /**
     * @var Mass The original mass file of the person.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="\App\Match\Entity\Mass", cascade={"persist","remove"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $mass;

    /**
     * @var Direction|null The direction between the personal address and the work address.
     *
     * @ORM\ManyToOne(targetEntity="\App\Geography\Entity\Direction", cascade={"persist", "remove"})
     */
    private $direction;

    /**
     * @var \DateTimeInterface|null The outward time.
     *
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     */
    private $outwardTime;

    /**
     * @var \DateTimeInterface|null The return time.
     *
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     */
    private $returnTime;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGivenId(): string
    {
        return $this->givenId;
    }

    public function setGivenId(string $givenId): self
    {
        $this->givenId = $givenId;

        return $this;
    }

    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    public function setGivenName(?string $givenName): self
    {
        $this->givenName = $givenName;
        if ($this->givenName == '') {
            $this->givenName = null;
        }
        return $this;
    }

    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }

    public function setFamilyName(?string $familyName): self
    {
        $this->familyName = $familyName;
        if ($this->familyName == '') {
            $this->familyName = null;
        }
        return $this;
    }

    public function getPersonalAddress(): Address
    {
        return $this->personalAddress;
    }

    public function setPersonalAddress(Address $address): self
    {
        $this->personalAddress = $address;

        return $this;
    }

    public function getWorkAddress(): Address
    {
        return $this->workAddress;
    }

    public function setWorkAddress(Address $address): self
    {
        $this->workAddress = $address;

        return $this;
    }

    public function getMass(): Mass
    {
        return $this->mass;
    }

    public function setMass(?Mass $mass): self
    {
        $this->mass = $mass;

        return $this;
    }

    public function getDirection(): ?Direction
    {
        return $this->direction;
    }

    public function setDirection(?Direction $direction): self
    {
        $this->direction = $direction;

        return $this;
    }

    public function getOutwardTime(): ?\DateTimeInterface
    {
        return $this->outwardTime;
    }

    public function setOutwardTime(?string $outwardTime): self
    {
        if ($outwardTime) {
            $this->outwardTime = \Datetime::createFromFormat('H:i:s', $outwardTime);
        }

        return $this;
    }

    public function getReturnTime(): ?\DateTimeInterface
    {
        return $this->returnTime;
    }

    public function setReturnTime(?string $returnTime): self
    {
        if ($returnTime) {
            $this->returnTime = \Datetime::createFromFormat('H:i:s', $returnTime);
        }

        return $this;
    }
}