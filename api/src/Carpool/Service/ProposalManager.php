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

namespace App\Carpool\Service;

use App\Carpool\Entity\Proposal;
use Doctrine\ORM\EntityManagerInterface;
use App\Carpool\Entity\Criteria;
use App\Geography\Entity\Address;
use App\Carpool\Entity\Waypoint;
use App\Carpool\Repository\ProposalRepository;
use App\Geography\Service\GeoRouter;
use App\Geography\Entity\Direction;
use App\DataProvider\Entity\GeoRouterProvider;
use App\Geography\Service\ZoneManager;

/**
 * Proposal manager service.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class ProposalManager
{
    private $entityManager;
    private $proposalMatcher;
    private $proposalRepository;
    private $geoRouter;
    private $zoneManager;

    public function __construct(EntityManagerInterface $entityManager, ProposalMatcher $proposalMatcher, ProposalRepository $proposalRepository, GeoRouter $geoRouter, ZoneManager $zoneManager)
    {
        $this->entityManager = $entityManager;
        $this->proposalMatcher = $proposalMatcher;
        $this->proposalRepository = $proposalRepository;
        $this->geoRouter = $geoRouter;
        $this->zoneManager = $zoneManager;
    }
    
    /**
     * Create a proposal.
     *
     * @param Proposal $proposal
     */
    public function createProposal(Proposal $proposal)
    {
        // temporary initialisation, will be dumped when implementation of these fields will be done
        $proposal->getCriteria()->setSeats(1);
        $proposal->getCriteria()->setAnyRouteAsPassenger(true);
        $proposal->getCriteria()->setIsStrictDate(true);

        // calculation of the min and max times
        if ($proposal->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
            list($minTime, $maxTime) = self::getMinMaxTime($proposal->getCriteria()->getFromTime(), $proposal->getCriteria()->getMarginTime());
            $proposal->getCriteria()->setMinTime($minTime);
            $proposal->getCriteria()->setMaxTime($maxTime);
        } else {
            if ($proposal->getCriteria()->getMonCheck()) {
                list($minTime, $maxTime) = self::getMinMaxTime($proposal->getCriteria()->getMonTime(), $proposal->getCriteria()->getMonMarginTime());
                $proposal->getCriteria()->setMonMinTime($minTime);
                $proposal->getCriteria()->setMonMaxTime($maxTime);
            }
            if ($proposal->getCriteria()->getTueCheck()) {
                list($minTime, $maxTime) = self::getMinMaxTime($proposal->getCriteria()->getTueTime(), $proposal->getCriteria()->getTueMarginTime());
                $proposal->getCriteria()->setTueMinTime($minTime);
                $proposal->getCriteria()->setTueMaxTime($maxTime);
            }
            if ($proposal->getCriteria()->getWedCheck()) {
                list($minTime, $maxTime) = self::getMinMaxTime($proposal->getCriteria()->getWedTime(), $proposal->getCriteria()->getWedMarginTime());
                $proposal->getCriteria()->setWedMinTime($minTime);
                $proposal->getCriteria()->setWedMaxTime($maxTime);
            }
            if ($proposal->getCriteria()->getThuCheck()) {
                list($minTime, $maxTime) = self::getMinMaxTime($proposal->getCriteria()->getThuTime(), $proposal->getCriteria()->getThuMarginTime());
                $proposal->getCriteria()->setThuMinTime($minTime);
                $proposal->getCriteria()->setThuMaxTime($maxTime);
            }
            if ($proposal->getCriteria()->getFriCheck()) {
                list($minTime, $maxTime) = self::getMinMaxTime($proposal->getCriteria()->getFriTime(), $proposal->getCriteria()->getFriMarginTime());
                $proposal->getCriteria()->setFriMinTime($minTime);
                $proposal->getCriteria()->setFriMaxTime($maxTime);
            }
            if ($proposal->getCriteria()->getSatCheck()) {
                list($minTime, $maxTime) = self::getMinMaxTime($proposal->getCriteria()->getSatTime(), $proposal->getCriteria()->getSatMarginTime());
                $proposal->getCriteria()->setSatMinTime($minTime);
                $proposal->getCriteria()->setSatMaxTime($maxTime);
            }
            if ($proposal->getCriteria()->getSunCheck()) {
                list($minTime, $maxTime) = self::getMinMaxTime($proposal->getCriteria()->getSunTime(), $proposal->getCriteria()->getSunMarginTime());
                $proposal->getCriteria()->setSunMinTime($minTime);
                $proposal->getCriteria()->setSunMaxTime($maxTime);
            }
        }

        // creation of the directions
        $addresses = [];
        foreach ($proposal->getWaypoints() as $waypoint) {
            $addresses[] = $waypoint->getAddress();
        }
        if ($routes = $this->geoRouter->getRoutes($addresses)) {
            if ($proposal->getCriteria()->isDriver()) {
                $proposal->getCriteria()->setDirectionDriver($routes[0]);
            }
            if ($proposal->getCriteria()->isPassenger()) {
                $proposal->getCriteria()->setDirectionPassenger($routes[0]);
            }
        }

        $this->entityManager->persist($proposal);
        
        // matching analyze
        //$this->proposalMatcher->createMatchingsForProposal($proposal);
        
        return $proposal;
    }
    
    /**
     * Returns all proposals matching the parameters.
     * Used for RDEX export.
     *
     * @param bool $offer
     * @param bool $request
     * @param float $from_longitude
     * @param float $from_latitude
     * @param float $to_longitude
     * @param float $to_latitude
     * @param string $frequency
     * @param \DateTime $outward_mindate
     * @param \DateTime $outward_maxdate
     * @param string $outward_monday_mintime
     * @param string $outward_monday_maxtime
     * @param string $outward_tuesday_mintime
     * @param string $outward_tuesday_maxtime
     * @param string $outward_wednesday_mintime
     * @param string $outward_wednesday_maxtime
     * @param string $outward_thursday_mintime
     * @param string $outward_thursday_maxtime
     * @param string $outward_friday_mintime
     * @param string $outward_friday_maxtime
     * @param string $outward_saturday_mintime
     * @param string $outward_saturday_maxtime
     * @param string $outward_sunday_mintime
     * @param string $outward_sunday_maxtime
     */
    public function getProposalsForRdex(
        bool $offer,
        bool $request,
        float $from_longitude,
        float $from_latitude,
        float $to_longitude,
        float $to_latitude,
        string $frequency = null,
        \DateTime $outward_mindate = null,
        \DateTime $outward_maxdate = null,
        string $outward_monday_mintime = null,
        string $outward_monday_maxtime = null,
        string $outward_tuesday_mintime = null,
        string $outward_tuesday_maxtime = null,
        string $outward_wednesday_mintime = null,
        string $outward_wednesday_maxtime = null,
        string $outward_thursday_mintime = null,
        string $outward_thursday_maxtime = null,
        string $outward_friday_mintime = null,
        string $outward_friday_maxtime = null,
        string $outward_saturday_mintime = null,
        string $outward_saturday_maxtime = null,
        string $outward_sunday_mintime = null,
        string $outward_sunday_maxtime = null
        ) {
        // test : we return all proposals
        // we create a proposal with the parameters
        $proposal = new Proposal();
        $proposal->setType(Proposal::TYPE_ONE_WAY);
        $addressFrom = new Address();
        $addressFrom->setLongitude((string)$from_longitude);
        $addressFrom->setLatitude((string)$from_latitude);
        // for now we don't search with coordinates, we force the localities for testing purpose
        // @todo delete the locality search only
        $addressFrom->setAddressLocality("Nancy");
        $addressTo = new Address();
        $addressTo->setLongitude((string)$to_longitude);
        $addressTo->setLatitude((string)$to_latitude);
        $addressTo->setAddressLocality("Metz");
        $waypointFrom = new Waypoint();
        $waypointFrom->setAddress($addressFrom);
        $waypointFrom->setPosition(0);
        $waypointFrom->setIsDestination(false);
        $waypointTo = new Waypoint();
        $waypointTo->setAddress($addressTo);
        $waypointTo->setPosition(1);
        $waypointTo->setIsDestination(true);
        $criteria = new Criteria();
        $criteria->setIsDriver(!$offer);
        $criteria->setIsPassenger(!$request);
        if (!is_null($outward_mindate)) {
            $criteria->setFromDate($outward_mindate);
        } else {
            $criteria->setFromDate(new \DateTime());
        }
        if (!is_null($outward_maxdate)) {
            $criteria->setToDate($outward_maxdate);
        }
        $proposal->setCriteria($criteria);
        $proposal->addWaypoint($waypointFrom);
        $proposal->addWaypoint($waypointTo);
        // for now we don't use the time parameters
        // @todo add the time parameters
        return $this->proposalRepository->findMatchingProposals($proposal, false);
    }

    // returns the min and max time from a time and a margin
    private static function getMinMaxTime($time, $margin)
    {
        $minTime = clone $time;
        $maxTime = clone $time;
        $minTime->sub(new \DateInterval('PT' . $margin . 'S'));
        if ($minTime->format('j') <> $time->format('j')) {
            // the day has changed => we keep '00:00' as min time
            $minTime = new \Datetime('00:00:00');
        }
        $maxTime->add(new \DateInterval('PT' . $margin . 'S'));
        if ($maxTime->format('j') <> $time->format('j')) {
            // the day has changed => we keep '23:59:00' as max time
            $maxTime = new \Datetime('23:59:00');
        }
        return [
            $minTime,
            $maxTime
        ];
    }
}
