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

namespace Mobicoop\Bundle\MobicoopBundle\Api\Service;

use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\GeoSearch;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;
use Mobicoop\Bundle\MobicoopBundle\ExternalJourney\Entity\ExternalJourney;
use Mobicoop\Bundle\MobicoopBundle\Match\Entity\Mass;
use Mobicoop\Bundle\MobicoopBundle\Match\Entity\MassPerson;
use Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity\PTJourney;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Proposal;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\Event\Entity\Event;

use TypeError;

use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Criteria;
use Mobicoop\Bundle\MobicoopBundle\Travel\Entity\TravelMode;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Matching;
use Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity\PTDeparture;
use Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity\PTArrival;
use Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity\PTCompany;
use Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity\PTLine;
use Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity\PTStep;
use Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity\PTLeg;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Waypoint;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\IndividualStop;
use Mobicoop\Bundle\MobicoopBundle\Image\Entity\Image;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Direction;
use Mobicoop\Bundle\MobicoopBundle\ExternalJourney\Entity\ExternalJourneyProvider;
use Mobicoop\Bundle\MobicoopBundle\Community\Entity\Community;
use Mobicoop\Bundle\MobicoopBundle\Community\Entity\CommunityUser;
use App\Article\Entity\Article;
use Mobicoop\Bundle\MobicoopBundle\Article\Entity\Section;
use Mobicoop\Bundle\MobicoopBundle\Article\Entity\Paragraph;

/**
 * Custom deserializer service.
 * Used because deserialization of nested array of objects doesn't work yet...
 * Should be dumped when deserialization will work !
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 *
 */
class Deserializer
{
    const DATETIME_FORMAT = \DateTime::ISO8601;
    const SETTER_PREFIX = "set";

    /**
     * Deserialize an object.
     *
     * @param string $class The expected class of the object
     * @param array $data   The array to deserialize
     * @return User|Address|Proposal|Matching|GeoSearch|PTJourney|ExternalJourney|null
     */
    public function deserialize(string $class, array $data)
    {
        switch ($class) {
            case User::class:
                return self::deserializeUser($data);
                break;
            case Address::class:
                return self::deserializeAddress($data);
                break;
            case Event::class:
                return self::deserializeEvent($data);
                break;
            case Image::class:
                return self::deserializeImage($data);
                break;
            case Proposal::class:
                return self::deserializeProposal($data);
                break;
            case Matching::class:
                return self::deserializeMatching($data);
                break;
            case GeoSearch::class:
                return self::deserializeGeoSearch($data);
                break;
            case PTJourney::class:
                return self::deserializePTJourney($data);
                break;
            case ExternalJourneyProvider::class:
                return self::deserializeExternalJourneyProvider($data);
                break;
            case ExternalJourney::class:
                return $data;
                break;
            case Mass::class:
                return self::deserializeMass($data);
                break;
            case Community::class:
                return self::deserializeCommunity($data);
                break;
            case Article::class:
                return self::deserializeArticle($data);
                break;
            default:
                break;
        }
        return null;
    }
    
    private function deserializeUser(array $data): ?User
    {
        $user = new User();
        $user = self::autoSet($user, $data);
        if (isset($data["@id"])) {
            $user->setIri($data["@id"]);
        }
        if (isset($data["addresses"])) {
            foreach ($data["addresses"] as $address) {
                $user->addAddress(self::deserializeAddress($address));
            }
        }
        if (isset($data["masses"])) {
            foreach ($data["masses"] as $mass) {
                $user->addMass(self::deserializeMass($mass));
            }
        }
        return $user;
    }
    
    private function deserializeAddress(array $data): ?Address
    {
        $address = new Address();
        $address = self::autoSet($address, $data);
        if (isset($data["@id"])) {
            $address->setIri($data["@id"]);
        }
        return $address;
    }
    
    private function deserializeEvent(array $data): ?Event
    {
        $event = new Event();
        $event = self::autoSet($event, $data);
        if (isset($data["@id"])) {
            $event->setIri($data["@id"]);
        }
        if (isset($data["address"])) {
            $event->setAddress(self::deserializeAddress($data['address']));
        }
        if (isset($data["images"])) {
            foreach ($data["images"] as $image) {
                $event->addImage(self::deserializeImage($image));
            }
        }
        return $event;
    }
    
    private function deserializeImage(array $data): ?Image
    {
        $image = new Image();
        $image = self::autoSet($image, $data);
        if (isset($data["@id"])) {
            $image->setIri($data["@id"]);
        }
        return $image;
    }
    
    private function deserializeProposal(array $data): ?Proposal
    {
        $proposal = new Proposal();
        $proposal = self::autoSet($proposal, $data);
        if (isset($data["@id"])) {
            $proposal->setIri($data["@id"]);
        }
        if (isset($data["user"])) {
            $proposal->setUser(self::deserializeUser($data['user']));
        }
        if (isset($data["waypoints"])) {
            foreach ($data["waypoints"] as $waypoint) {
                $proposal->addWaypoint(self::deserializeWaypoint($waypoint));
            }
        }
        if (isset($data["matchingOffers"]) && is_array($data["matchingOffers"])) {
            foreach ($data["matchingOffers"] as $matching) {
                if (!is_null($matching) && is_array($matching)) {
                    $proposal->addMatchingOffer(self::deserializeMatching($matching));
                }
            }
        }
        if (isset($data["matchingRequests"]) && is_array($data["matchingRequests"])) {
            foreach ($data["matchingRequests"] as $matching) {
                if (!is_null($matching) && is_array($matching)) {
                    $proposal->addMatchingRequest(self::deserializeMatching($matching));
                }
            }
        }
        if (isset($data["travelModes"])) {
            foreach ($data["travelModes"] as $travelMode) {
                $proposal->addTravelMode(self::deserializeTravelMode($travelMode));
            }
        }
        if (isset($data["criteria"])) {
            $proposal->setCriteria(self::deserializeCriteria($data['criteria']));
        }
        if (isset($data["individualStops"])) {
            foreach ($data["individualStops"] as $individualStop) {
                $proposal->addIndividualStop(self::deserializeIndividualStop($individualStop));
            }
        }
        //echo "<pre>" . print_r($proposal,true) . "</pre>";exit;
        return $proposal;
    }
    
    private function deserializeWaypoint(array $data): ?Waypoint
    {
        $waypoint = new Waypoint();
        $waypoint = self::autoSet($waypoint, $data);
        if (isset($data["@id"])) {
            $waypoint->setIri($data["@id"]);
        }
        if (isset($data["address"])) {
            $waypoint->setAddress(self::deserializeAddress($data['address']));
        }
        return $waypoint;
    }
    
    private function deserializeTravelMode(array $data): ?TravelMode
    {
        $travelMode = new TravelMode();
        $travelMode = self::autoSet($travelMode, $data);
        if (isset($data["@id"])) {
            $travelMode->setIri($data["@id"]);
        }
        return $travelMode;
    }
    
    private function deserializeCriteria(array $data): ?Criteria
    {
        $criteria = new Criteria();
        $criteria = self::autoSet($criteria, $data);
        if (isset($data["@id"])) {
            $criteria->setIri($data["@id"]);
        }
        if (isset($data["directionDriver"])) {
            $criteria->setDirectionDriver(self::deserializeDirection($data['directionDriver']));
        }
        if (isset($data["directionPassenger"])) {
            $criteria->setDirectionPassenger(self::deserializeDirection($data['directionPassenger']));
        }
        return $criteria;
    }

    private function deserializeDirection(array $data): ?Direction
    {
        $direction = new Direction();
        $direction = self::autoSet($direction, $data);
        if (isset($data["@id"])) {
            $direction->setIri($data["@id"]);
        }
        if (isset($data["points"])) {
            $points = [];
            foreach ($data["points"] as $address) {
                $points[] = self::deserializeAddress($address);
            }
            $direction->setPoints($points);
        }
        return $direction;
    }
    
    private function deserializeIndividualStop(array $data): ?IndividualStop
    {
        $individualStop = new IndividualStop();
        $individualStop = self::autoSet($individualStop, $data);
        if (isset($data["@id"])) {
            $individualStop->setIri($data["@id"]);
        }
        if (isset($data["proposal"])) {
            $individualStop->setProposal(self::deserializeProposal($data['proposal']));
        }
        if (isset($data["address"])) {
            $individualStop->setAddress(self::deserializeAddress($data['address']));
        }
        return $individualStop;
    }
    
    private function deserializeMatching(array $data): ?Matching
    {
        $matching = new Matching();
        $matching = self::autoSet($matching, $data);
        if (isset($data["@id"])) {
            $matching->setIri($data["@id"]);
        }
        if (isset($data["proposalOffer"]) && is_array($data["proposalOffer"])) {
            $matching->setProposalOffer(self::deserializeProposal($data['proposalOffer']));
        }
        if (isset($data["proposalRequest"]) && is_array($data["proposalRequest"])) {
            $matching->setProposalRequest(self::deserializeProposal($data['proposalRequest']));
        }
        if (isset($data["criteria"])) {
            $matching->setCriteria(self::deserializeCriteria($data['criteria']));
        }
        if (isset($data["waypoints"])) {
            foreach ($data["waypoints"] as $waypoint) {
                $matching->addWaypoint(self::deserializeWaypoint($waypoint));
            }
        }
        if (isset($data["filters"])) {
            $matching->setFilters($data["filters"]);
        }
        return $matching;
    }
    
    private function deserializeGeoSearch(array $data): ?Address
    {
        $address = new Address();
        $address = self::autoSet($address, $data);
        if (isset($data["@id"])) {
            $address->setIri($data["@id"]);
        }
        return $address;
    }
    
    private function deserializePTJourney(array $data): ?PTJourney
    {
        $PTJourney = new PTJourney();
        $PTJourney = self::autoSet($PTJourney, $data);
        if (isset($data["ptdeparture"])) {
            $PTJourney->setPTDeparture(self::deserializePTDeparture($data["ptdeparture"]));
        }
        if (isset($data["ptarrival"])) {
            $PTJourney->setPTArrival(self::deserializePTArrival($data["ptarrival"]));
        }
        if (isset($data["ptlegs"])) {
            $nblegs = 0;
            foreach ($data["ptlegs"] as $ptleg) {
                $nblegs++;
                $PTJourney->addPTLeg(self::deserializePTLeg($ptleg, $nblegs));
            }
        }
        return $PTJourney;
    }
    
    private function deserializePTDeparture(array $data): ?PTDeparture
    {
        $PTDeparture = new PTDeparture();
        $PTDeparture = self::autoSet($PTDeparture, $data);
        if (isset($data["address"])) {
            $PTDeparture->setAddress(self::deserializeAddress($data["address"]));
        }
        return $PTDeparture;
    }
    
    private function deserializePTArrival(array $data): ?PTArrival
    {
        $PTArrival = new PTArrival();
        $PTArrival = self::autoSet($PTArrival, $data);
        if (isset($data["address"])) {
            $PTArrival->setAddress(self::deserializeAddress($data["address"]));
        }
        return $PTArrival;
    }
    
    private function deserializePTLeg(array $data, int $id): ?PTLeg
    {
        $PTLeg = new PTLeg($id);
        $PTLeg = self::autoSet($PTLeg, $data);
        if (isset($data["ptdeparture"])) {
            $PTLeg->setPTDeparture(self::deserializePTDeparture($data["ptdeparture"]));
        }
        if (isset($data["ptarrival"])) {
            $PTLeg->setPTArrival(self::deserializePTArrival($data["ptarrival"]));
        }
        if (isset($data["travelMode"])) {
            $PTLeg->setTravelMode(self::deserializeTravelMode($data["travelMode"]));
        }
        if (isset($data["ptline"])) {
            $PTLeg->setPTLine(self::deserializePTLine($data["ptline"]));
        }
        if (isset($data["ptsteps"])) {
            $nbsteps = 0;
            foreach ($data["ptsteps"] as $ptstep) {
                $nbsteps++;
                $PTLeg->addPTStep(self::deserializePTStep($ptstep, $nbsteps));
            }
        }
        return $PTLeg;
    }
        
    private function deserializePTLine(array $data): ?PTLine
    {
        $PTLine = new PTLine();
        $PTLine = self::autoSet($PTLine, $data);
        if (isset($data["ptcompany"])) {
            $PTLine->setPTCompany(self::deserializePTCompany($data["ptcompany"]));
        }
        return $PTLine;
    }
    
    private function deserializePTCompany(array $data): ?PTCompany
    {
        $PTCompany = new PTCompany();
        $PTCompany = self::autoSet($PTCompany, $data);
        return $PTCompany;
    }
    
    private function deserializePTStep(array $data, int $id): ?PTStep
    {
        $PTStep = new PTStep($id);
        $PTStep = self::autoSet($PTStep, $data);
        if (isset($data["ptdeparture"])) {
            $PTStep->setPTDeparture(self::deserializePTDeparture($data["ptdeparture"]));
        }
        if (isset($data["ptarrival"])) {
            $PTStep->setPTArrival(self::deserializePTArrival($data["ptarrival"]));
        }
        return $PTStep;
    }

    private function deserializeMass(array $data): ?Mass
    {
        $mass = new Mass();
        $mass = self::autoSet($mass, $data);
        if (isset($data["@id"])) {
            $mass->setIri($data["@id"]);
        }
        if (isset($data["persons"])) {
            foreach ($data["persons"] as $person) {
                $mass->addPerson(self::deserializeMassPerson($person));
            }
        }
        return $mass;
    }

    private function deserializeMassPerson(array $data): ?MassPerson
    {
        $massPerson = new MassPerson();
        $massPerson = self::autoSet($massPerson, $data);
        if (isset($data["@id"])) {
            $massPerson->setIri($data["@id"]);
        }
        if (isset($data["personalAddress"])) {
            $massPerson->setPersonalAddress(self::deserializeAddress($data["personalAddress"]));
        }
        if (isset($data["workAddress"])) {
            $massPerson->setWorkAddress(self::deserializeAddress($data["workAddress"]));
        }
        if (isset($data["direction"])) {
            $massPerson->setDirection(self::deserializeDirection($data["direction"]));
        }
        return $massPerson;
    }

    private function deserializeExternalJourneyProvider(array $data): ?ExternalJourneyProvider
    {
        $provider = new ExternalJourneyProvider();
        $provider = self::autoSet($provider, $data);
        return $provider;
    }

    private function deserializeCommunityUser(array $data): ?CommunityUser
    {
        $communityUser = new communityUser();
        $communityUser = self::autoSet($communityUser, $data);
        if (isset($data["@id"])) {
            $communityUser->setIri($data["@id"]);
        }
        if (isset($data["community"])) {
            $communityUser->set>Community(self::deserializeCommunity($data["community"]));
        }
        if (isset($data["user"])) {
            $communityUser->setUser(self::deserializeUser($data["user"]));
        }
        if (isset($data["admin"])) {
            $communityUser->setAdmin(self::deserializeUser($data["admin"]));
        }
        return $communityUser;
    }

    private function deserializeCommunity(array $data): ?Community
    {
        $community = new Community();
        $community = self::autoSet($community, $data);
        if (isset($data["@id"])) {
            $community->setIri($data["@id"]);
        }
        if (isset($data["user"])) {
            $community->setUser(self::deserializeUser($data["user"]));
        }
        if (isset($data["images"])) {
            foreach ($data["images"] as $image) {
                $community->addImage(self::deserializeImage($image));
            }
        }
        if (isset($data["proposals"])) {
            foreach ($data["proposals"] as $proposal) {
                $community->addProposal(self::deserializeProposal($proposal));
            }
        }
        if (isset($data["communityUsers"])) {
            foreach ($data["communityUsers"] as $communityUser) {
                $community->addCommunityUser(self::deserializeCommunityUser($communityUser));
            }
        }
        return $community;
    }

    private function deserializeArticle(array $data): ?Article
    {
        $article = new Article();
        $article = self::autoSet($article, $data);
        if (isset($data["@id"])) {
            $article->setIri($data["@id"]);
        }
        if (isset($data["sections"])) {
            foreach ($data["sections"] as $section) {
                $article->addSection(self::deserializeSection($section));
            }
        }
        return $article;
    }

    private function deserializeSection(array $data): ?Section
    {
        $section = new Section();
        $section = self::autoSet($section, $data);
        if (isset($data["@id"])) {
            $section->setIri($data["@id"]);
        }
        if (isset($data["paragraphs"])) {
            foreach ($data["paragraphs"] as $paragraph) {
                $section->addParagraph(self::deserializeParagraph($paragraph));
            }
        }
        return $section;
    }

    private function deserializeParagraph(array $data): ?Paragraph
    {
        $paragraph = new Paragraph();
        $paragraph = self::autoSet($paragraph, $data);
        if (isset($data["@id"])) {
            $paragraph->setIri($data["@id"]);
        }
        return $paragraph;
    }
    
    private function autoSet($object, $data)
    {
        $phpDocExtractor = new PhpDocExtractor();
        $reflectionExtractor = new ReflectionExtractor();
        $listExtractors = array($reflectionExtractor);
        $typeExtractors = array($phpDocExtractor);
        $descriptionExtractors = array($phpDocExtractor);
        $accessExtractors = array($reflectionExtractor);
        
        $propertyInfo = new PropertyInfoExtractor(
            $listExtractors,
            $typeExtractors,
            $descriptionExtractors,
            $accessExtractors
                );
        
        $properties = $propertyInfo->getProperties(get_class($object));
        foreach ($properties as $property) {
            if (isset($data[$property])) {
                $setter = self::SETTER_PREFIX.ucwords($property);
                if (method_exists($object, $setter)) {
                    // we try to set the property
                    try {
                        // it works !!!
                        $object->$setter($data[$property]);
                    } catch (TypeError $error) {
                        // fail... it must be an object or array property, we will treat it manually
                        $type = null;
                        if (!is_null($propertyInfo->getTypes(get_class($object), $property)[0])) {
                            $type = $propertyInfo->getTypes(get_class($object), $property)[0]->getClassName();
                        }
                        switch ($type) {
                            case "DateTime":
                            case "DateTimeInterface":
                                try {
                                    $catchedValue = \DateTime::createFromFormat(self::DATETIME_FORMAT, $data[$property]);
                                    $object->$setter($catchedValue);
                                } catch (\Error $e) {
                                }
                                break;
                            default: break;
                        }
                    }
                }
            }
        }
        return $object;
    }
}
