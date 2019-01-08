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

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity\PTJourney;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\Car;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Direction;

/**
 * Carpooling : criteria (restriction for an offer / selection for a request).
 */
class Criteria
{
    const FREQUENCY_PUNCTUAL = 1;
    const FREQUENCY_REGULAR = 2;
    
    const FREQUENCY = [
            "punctual"=>self::FREQUENCY_PUNCTUAL,
            "regular"=>self::FREQUENCY_REGULAR
    ];
    
    /**
     * @var int The id of this criteria.
     */
    private $id;
    
    /**
     * @var string|null The iri of this criteria.
     */
    private $iri;
    
    /**
     * @var boolean The user can be a driver.
     *
     * @Groups({"post","put"})
     */
    private $isDriver;
    
    /**
     * @var boolean The user can be a passenger.
     *
     * @Groups({"post","put"})
     */
    private $isPassenger;

    /**
     * @var int The proposal frequency (1 = punctual; 2 = regular).
     * @Assert\NotBlank
     *
     * @Groups({"post","put"})
     */
    private $frequency;

    /**
     * @var int The number of available seats.
     * @Assert\NotBlank
     *
     * @Groups({"post","put"})
     */
    private $seats;

    /**
     * @var \DateTimeInterface The starting date (= proposal date if punctual).
     * @Assert\NotBlank
     * @Assert\Date()
     *
     * @Groups({"post","put"})
     */
    private $fromDate;

    /**
     * @var \DateTimeInterface|null The starting time.
     * @Assert\Time()
     *
     * @Groups({"post","put"})
     */
    private $fromTime;

    /**
     * @var \DateTimeInterface|null The end date if regular proposal.
     * @Assert\Date()
     */
    private $toDate;

    /**
     * @var boolean|null The proposal is available on mondays (if regular).
     */
    private $monCheck;

    /**
     * @var boolean|null The proposal is available on tuesdays (if regular).
     */
    private $tueCheck;

    /**
     * @var boolean|null The proposal is available on wednesdays (if regular).
     */
    private $wedCheck;

    /**
     * @var boolean|null The proposal is available on thursdays (if regular).
     */
    private $thuCheck;

    /**
     * @var boolean|null The proposal is available on fridays (if regular).
     */
    private $friCheck;

    /**
     * @var boolean|null The proposal is available on saturdays (if regular).
     */
    private $satCheck;

    /**
     * @var boolean|null The proposal is available on sundays (if regular).
     */
    private $sunCheck;

    /**
     * @var \DateTimeInterface|null Mondays starting time (if regular).
     * @Assert\Time()
     */
    private $monTime;

    /**
     * @var \DateTimeInterface|null Tuesdays starting time (if regular).
     * @Assert\Time()
     */
    private $tueTime;

    /**
     * @var \DateTimeInterface|null Wednesdays starting time (if regular).
     * @Assert\Time()
     */
    private $wedTime;

    /**
     * @var \DateTimeInterface|null Thursdays starting time (if regular).
     * @Assert\Time()
     */
    private $thuTime;

    /**
     * @var \DateTimeInterface|null Fridays starting time (if regular).
     * @Assert\Time()
     */
    private $friTime;

    /**
     * @var \DateTimeInterface|null Saturdays starting time (if regular).
     * @Assert\Time()
     */
    private $satTime;

    /**
     * @var \DateTimeInterface|null Sunadays starting time (if regular).
     * @Assert\Time()
     */
    private $sunTime;

    /**
     * @var int Accepted margin for starting time in seconds.
     */
    private $marginTime;
    
    /**
     * @var int|null The maximum deviation time (in seconds) as a driver to accept a request proposal.
     */
    private $maxDeviationTime;
    
    /**
     * @var int|null The maximum deviation distance (in metres) as a driver to accept a request proposal.
     */
    private $maxDeviationDistance;
    
    /**
     * @var boolean The user accepts any route as a passenger from its origin to the destination.
     */
    private $anyRouteAsPassenger;
    
    /**
     * @var boolean|null The user accepts any transportation mode.
     */
    private $multiTransportMode;
    
    /**
     * @var Car|null The car used in the journey.
     */
    private $car;
    
    /**
     * @var Direction|null The direction used in the journey as a driver.
     */
    private $directionDriver;
    
    /**
     * @var Direction|null The direction used in the journey as a passenger.
     */
    private $directionPassenger;
    
    /**
     * @var PTJourney|null The public transport journey used.
     */
    private $ptjourney;
        
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
    
    public function isDriver(): bool
    {
        return $this->isDriver;
    }
    
    public function setIsDriver(bool $isDriver): self
    {
        $this->isDriver = $isDriver;
        
        return $this;
    }
    
    public function isPassenger(): bool
    {
        return $this->isPassenger;
    }
    
    public function setIsPassenger(bool $isPassenger): self
    {
        $this->isPassenger = $isPassenger;
        
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
    
    public function getSeats(): ?int
    {
        return $this->seats;
    }
    
    public function setSeats(int $seats): self
    {
        $this->seats = $seats;
        
        return $this;
    }
    
    public function getFromDate(): ?\DateTimeInterface
    {
        return $this->fromDate;
    }
    
    public function setFromDate(\DateTimeInterface $fromDate): self
    {
        $this->fromDate = $fromDate;
        
        return $this;
    }
    
    public function getFromTime(): ?\DateTimeInterface
    {
        return $this->fromTime;
    }
    
    public function setFromTime(?\DateTimeInterface $fromTime): self
    {
        $this->fromTime = $fromTime;
        
        return $this;
    }
    
    public function getToDate(): ?\DateTimeInterface
    {
        return $this->toDate;
    }
    
    public function setToDate(?\DateTimeInterface $toDate): self
    {
        $this->toDate = $toDate;
        
        return $this;
    }
    
    public function getMonCheck(): ?bool
    {
        return $this->monCheck;
    }
    
    public function setMonCheck(?bool $monCheck): self
    {
        $this->monCheck = $monCheck;
        
        return $this;
    }
    
    public function getTueCheck(): ?bool
    {
        return $this->tueCheck;
    }
    
    public function setTueCheck(?bool $tueCheck): self
    {
        $this->tueCheck = $tueCheck;
        
        return $this;
    }
    
    public function getWedCheck(): ?bool
    {
        return $this->wedCheck;
    }
    
    public function setWedCheck(?bool $wedCheck): self
    {
        $this->wedCheck = $wedCheck;
        
        return $this;
    }
    
    public function getThuCheck(): ?bool
    {
        return $this->thuCheck;
    }
    
    public function setThuCheck(?bool $thuCheck): self
    {
        $this->thuCheck = $thuCheck;
        
        return $this;
    }
    
    public function getFriCheck(): ?bool
    {
        return $this->friCheck;
    }
    
    public function setFriCheck(?bool $friCheck): self
    {
        $this->friCheck = $friCheck;
        
        return $this;
    }
    
    public function getSatCheck(): ?bool
    {
        return $this->satCheck;
    }
    
    public function setSatCheck(?bool $satCheck): self
    {
        $this->satCheck = $satCheck;
        
        return $this;
    }
    
    public function getSunCheck(): ?bool
    {
        return $this->sunCheck;
    }
    
    public function setSunCheck(?bool $sunCheck): self
    {
        $this->sunCheck = $sunCheck;
        
        return $this;
    }
    
    public function getMonTime(): ?\DateTimeInterface
    {
        return $this->monTime;
    }
    
    public function setMonTime(?\DateTimeInterface $monTime): self
    {
        $this->monTime = $monTime;
        
        return $this;
    }
    
    public function getTueTime(): ?\DateTimeInterface
    {
        return $this->tueTime;
    }
    
    public function setTueTime(?\DateTimeInterface $tueTime): self
    {
        $this->tueTime = $tueTime;
        
        return $this;
    }
    
    public function getWedTime(): ?\DateTimeInterface
    {
        return $this->wedTime;
    }
    
    public function setWedTime(?\DateTimeInterface $wedTime): self
    {
        $this->wedTime = $wedTime;
        
        return $this;
    }
    
    public function getThuTime(): ?\DateTimeInterface
    {
        return $this->thuTime;
    }
    
    public function setThuTime(?\DateTimeInterface $thuTime): self
    {
        $this->thuTime = $thuTime;
        
        return $this;
    }
    
    public function getFriTime(): ?\DateTimeInterface
    {
        return $this->friTime;
    }
    
    public function setFriTime(?\DateTimeInterface $friTime): self
    {
        $this->friTime = $friTime;
        
        return $this;
    }
    
    public function getSatTime(): ?\DateTimeInterface
    {
        return $this->satTime;
    }
    
    public function setSatTime(?\DateTimeInterface $satTime): self
    {
        $this->satTime = $satTime;
        
        return $this;
    }
    
    public function getSunTime(): ?\DateTimeInterface
    {
        return $this->sunTime;
    }
    
    public function setSunTime(?\DateTimeInterface $sunTime): self
    {
        $this->sunTime = $sunTime;
        
        return $this;
    }
    
    public function getMarginTime(): ?int
    {
        return $this->marginTime;
    }
    
    public function setMarginTime(?int $marginTime): self
    {
        $this->marginTime = $marginTime;
        
        return $this;
    }
    
    public function getMaxDeviationTime(): ?int
    {
        return $this->maxDeviationTime;
    }
    
    public function getMaxDeviationDistance(): ?int
    {
        return $this->maxDeviationDistance;
    }
    
    public function getAnyRouteAsPassenger(): bool
    {
        return $this->anyRouteAsPassenger;
    }
    
    public function setMaxDeviationTime(?int $maxDeviationTime): self
    {
        $this->maxDeviationTime = $maxDeviationTime;
        
        return $this;
    }
    
    public function setMaxDeviationDistance(?int $maxDeviationDistance): self
    {
        $this->maxDeviationDistance = $maxDeviationDistance;
        
        return $this;
    }
    
    public function setAnyRouteAsPassenger(bool $anyRouteAsPassenger): self
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
    
    public function getCar(): ?Car
    {
        return $this->car;
    }
    
    public function setCar(?Car $car): self
    {
        $this->car = $car;
        
        return $this;
    }
    
    public function getDirectionDriver(): ?Direction
    {
        return $this->directionDriver;
    }
    
    public function setDirectionDriver(?Direction $directionDriver): self
    {
        $this->directionDriver = $directionDriver;
        
        return $this;
    }
    
    public function getDirectionPassenger(): ?Direction
    {
        return $this->directionPassenger;
    }
    
    public function setDirectionPassenger(?Direction $directionPassenger): self
    {
        $this->directionPassenger = $directionPassenger;
        
        return $this;
    }
    
    public function getPTJourney(): ?PTJourney
    {
        return $this->ptjourney;
    }
    
    public function setPTJourney(?PTJourney $ptjourney): self
    {
        $this->ptjourney = $ptjourney;
        
        return $this;
    }
}