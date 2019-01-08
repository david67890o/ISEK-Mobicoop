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
use Mobicoop\Bundle\MobicoopBundle\Api\Entity\Resource;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\Travel\Entity\TravelMode;

/**
 * Carpooling : proposal (offer from a driver / request from a passenger).
 */
class Proposal implements Resource
{
    const TYPE_ONE_WAY = 1;
    const TYPE_OUTWARD = 2;
    const TYPE_RETURN = 3;
    
    const TYPE = [
            "one_way"=>self::TYPE_ONE_WAY,
            "return"=>self::TYPE_OUTWARD
    ];
    
    /**
     * @var int The id of this proposal.
     */
    private $id;
    
    /**
     * @var string|null The iri of this proposal.
     */
    private $iri;

    /**
     * @var Proposal|null Linked proposal for a round trip (return or outward journey).
     */
    private $proposalLinked;
    
    /**
     * @var User|null User who submits the proposal.
     */
    private $user;

    /**
     * @var Waypoint[] The waypoints of the proposal.
     *
     * @Assert\NotBlank
     */
    private $waypoints;
    
    /**
     * @var TravelMode[]|null The travel modes accepted if the proposal is a request.
     */
    private $travelModes;

    /**
     * @var Matching[]|null The matching of the proposal (if proposal is an offer).
     */
    private $matchingRequests;

    /**
     * @var Matching[]|null The matching of the proposal (if proposal is a request).
     */
    private $matchingOffers;

    /**
     * @var Criteria The criteria applied to the proposal.
     *
     * @Assert\NotBlank
     */
    private $criteria;
    
    /**
     * @var IndividualStop[] The individual stops of the proposal.
     */
    private $individualStops;
    
    // these fields are only for testing purpose,
    // in the future we will need dynamic fields that will populate the points.
    private $start;
    private $destination;

    public function __construct($id=null)
    {
        if ($id) {
            $this->setId($id);
            $this->setIri("/proposals/".$id);
        }
        $this->waypoints = new ArrayCollection();
        $this->travelModes = new ArrayCollection();
        $this->matchingRequests = new ArrayCollection();
        $this->matchingOffers = new ArrayCollection();
        $this->individualStops = new ArrayCollection();
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
    
    public function getType(): ?int
    {
        return $this->type;
    }
    
    public function setType(int $type): self
    {
        $this->type = $type;
        
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
    
    public function getProposalLinked(): ?self
    {
        return $this->proposalLinked;
    }
    
    public function setProposalLinked(?self $proposalLinked): self
    {
        $this->proposalLinked = $proposalLinked;
        
        // set (or unset) the owning side of the relation if necessary
        $newProposalLinked = $proposalLinked === null ? null : $this;
        if ($newProposalLinked !== $proposalLinked->getProposalLinked()) {
            $proposalLinked->setProposalLinked($newProposalLinked);
        }
        
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
    
    /**
     * @return Collection|Waypoint[]
     */
    public function getWaypoints(): Collection
    {
        return $this->waypoints;
    }
    
    public function addWaypoint(Waypoint $waypoint): self
    {
        if (!$this->waypoints->contains($waypoint)) {
            $this->waypoints[] = $waypoint;
            $waypoint->setProposal($this);
        }
        
        return $this;
    }
    
    public function removeWaypoint(Waypoint $waypoint): self
    {
        if ($this->waypoints->contains($waypoint)) {
            $this->waypoints->removeElement($waypoint);
            // set the owning side to null (unless already changed)
            if ($waypoint->getProposal() === $this) {
                $waypoint->setProposal(null);
            }
        }
        
        return $this;
    }
    
    /**
     * @return Collection|TravelMode[]
     */
    public function getTravelModes(): Collection
    {
        return $this->travelModes;
    }
    
    public function addTravelMode(TravelMode $travelMode): self
    {
        if (!$this->travelModes->contains($travelMode)) {
            $this->travelModes[] = $travelMode;
        }
        
        return $this;
    }
    
    public function removeTravelMode(TravelMode $travelMode): self
    {
        if ($this->travelModes->contains($travelMode)) {
            $this->travelModes->removeElement($travelMode);
        }
        
        return $this;
    }
    
    /**
     * @return Collection|Matching[]
     */
    public function getMatchingRequests(): Collection
    {
        return $this->matchingRequests;
    }
    
    public function addMatchingRequest(Matching $matchingRequest): self
    {
        if (!$this->matchingRequests->contains($matchingRequest)) {
            $this->matchingRequests[] = $matchingRequest;
            $matchingRequest->setProposalOffer($this);
        }
        
        return $this;
    }
    
    public function removeMatchingRequest(Matching $matchingRequest): self
    {
        if ($this->matchingRequests->contains($matchingRequest)) {
            $this->matchingRequests->removeElement($matchingRequest);
            // set the owning side to null (unless already changed)
            if ($matchingRequest->getProposalOffer() === $this) {
                $matchingRequest->setProposalOffer(null);
            }
        }
        
        return $this;
    }
    
    /**
     * @return Collection|Matching[]
     */
    public function getMatchingOffers(): Collection
    {
        return $this->matchingOffers;
    }
    
    public function addMatchingOffer(Matching $matchingOffer): self
    {
        if (!$this->matchingOffers->contains($matchingOffer)) {
            $this->matchingOffers[] = $matchingOffer;
            $matchingOffer->setProposalRequest($this);
        }
        
        return $this;
    }
    
    public function removeMatchingOffer(Matching $matchingOffer): self
    {
        if ($this->matchingOffers->contains($matchingOffer)) {
            $this->matchingOffers->removeElement($matchingOffer);
            // set the owning side to null (unless already changed)
            if ($matchingOffer->getProposalRequest() === $this) {
                $matchingOffer->setProposalRequest(null);
            }
        }
        
        return $this;
    }
    
    public function getCriteria(): ?Criteria
    {
        return $this->criteria;
    }
    
    public function setCriteria(Criteria $criteria): self
    {
        $this->criteria = $criteria;
        
        return $this;
    }
    
    /**
     * @return Collection|IndividualStop[]
     */
    public function getIndividualStops(): Collection
    {
        return $this->individualStops;
    }
    
    public function addIndividualStop(IndividualStop $individualStop): self
    {
        if (!$this->individualStops->contains($individualStop)) {
            $this->individualStops[] = $individualStop;
            $individualStop->setProposal($this);
        }
        
        return $this;
    }
    
    public function removeIndividualStop(IndividualStop $individualStop): self
    {
        if ($this->individualStops->contains($individualStop)) {
            $this->individualStops->removeElement($individualStop);
            // set the owning side to null (unless already changed)
            if ($individualStop->getProposal() === $this) {
                $individualStop->setProposal(null);
            }
        }
        
        return $this;
    }
    
    public function getStart()
    {
        return $this->start;
    }

    public function getDestination()
    {
        return $this->destination;
    }

    public function setStart($start)
    {
        $this->start = $start;
    }

    public function setDestination($destination)
    {
        $this->destination = $destination;
    }
}