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

namespace App\DataProvider\Entity;

use App\DataProvider\Interfaces\ProviderInterface;
use App\DataProvider\Service\DataProvider;
use App\PublicTransport\Entity\PTJourney;
use App\PublicTransport\Entity\PTArrival;
use App\PublicTransport\Entity\PTDeparture;
use App\PublicTransport\Entity\PTSection;
use App\PublicTransport\Entity\PTMode;
use App\Address\Entity\Address;
use App\PublicTransport\Entity\PTStep;
use App\PublicTransport\Entity\PTLine;
use App\PublicTransport\Service\PTDataProvider;

/**
 * Cityway data provider.
 *
 * Implements all the methods needed to retrieve data from CityWay :
 * - get collection and item
 * - deserialize to populate Public Transport entities
 *
 * With CityWay :
 * - the Journey consists in one or more Trips
 * - each Trip is an alternative to reach the destination from the departure
 * - a Trip has a departure and arrival time, a duration, a distance...
 * - a Trip consists in one or more Sections
 * - a Section consists in a Leg or a PTRide
 * - a Leg is the name for a walk
 * - a Leg consists in one or more PathLinks
 * - a PathLink consists in a departure and arrival point (and distance, duration, direction, eventually a site like a bus stop etc...)
 * - typically, a PathLink is a path between two points on the same road; when one needs to turn or change direction it is another PathLink
 * - a PTRide is the name for a public transport ride, it can be by bus, train, bike...
 * - a PTRide consists in one or more Steps
 * - typically, a step is a path between two consecutive stops, for example two consecutive stops of the same bus line
 *
 * Eg :
 *
 * if a trip consist in :
 * - a walk,
 * - then a bus ride,
 * - then another bus ride on another line,
 * - then a walk,
 *
 * we will have :
 * - one Leg,
 * - a first PTRide,
 * - a second PTRide,
 * - and finally a second Leg
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 *
 */
class CitywayProvider implements ProviderInterface
{
    private const CW_PT_MODE_BUS = "BUS";
    private const CW_PT_MODE_TRAIN = "TRAIN";
    private const CW_PT_MODE_BIKE = "BIKE";
    private const CW_PT_MODE_WALK = "WALK";
    
    private const CW_COUNTRY = "France";
    private const CW_NC = "NC";
    
    private const URI = "http://preprod.tsvc.grandest.cityway.fr/api/";
    private const COLLECTION_RESOURCE = "journeyplanner/opt/PlanTrips/json";
    
    private const DATETIME_OUTPUT_FORMAT = "d/m/Y H:i:s";
    private const DATETIME_INPUT_FORMAT = "Y-m-d_H-i";
    
    private const DATETYPES = [
            PTDataProvider::DATETYPE_DEPARTURE => "DEPARTURE",
            PTDataProvider::DATETYPE_ARRIVAL => "ARRIVAL"
    ];
    
    private $collection;
    
    public function __construct()
    {
        $this->collection = [];
    }
    
    public function getCollection(string $class, string $apikey, array $params)
    {
        switch ($class) {
            case PTJourney::class:
                $dataProvider = new DataProvider(self::URI, self::COLLECTION_RESOURCE);
                $getParams = [
                        "DepartureType" => "COORDINATES",
                        "ArrivalType" => "COORDINATES",
                        "DateType" => "DEPARTURE",
                        "TripModes" => "PT",
                        "Algorithm" => "FASTEST",
                        "Date" => $params["date"]->format(self::DATETIME_INPUT_FORMAT),
                        "DateType" => self::DATETYPES[$params["dateType"]],
                        "DepartureLatitude" => $params["origin_latitude"],
                        "DepartureLongitude" => $params["origin_longitude"],
                        "ArrivalLatitude" => $params["destination_latitude"],
                        "ArrivalLongitude" => $params["destination_longitude"]
                ];
                $response = $dataProvider->getCollection($getParams);
                if ($response->getCode() == 200) {
                    $data = json_decode($response->getValue(), true);
                    if (!isset($data["StatusCode"])) {
                        return $this->collection;
                    }
                    if ($data["StatusCode"] <> 200) {
                        return $this->collection;
                    }
                    if (!isset($data["Data"])) {
                        return $this->collection;
                    }
                    if (!isset($data["Data"][0]["response"])) {
                        break;
                    }
                    if (!isset($data["Data"][0]["response"]["trips"]["Trip"])) {
                        break;
                    }
                    foreach ($data["Data"][0]["response"]["trips"]["Trip"] as $trip) {
                        $this->collection[] = self::deserialize($class, $trip);
                    }
                    return $this->collection;
                }
                break;
            default:
                break;
        }
    }

    public function getItem(string $class, string $apikey, array $params)
    {
    }
    
    public function deserialize(string $class, array $data)
    {
        switch ($class) {
            case PTJourney::class:
                return self::deserializeJourney($data);
                break;
            default:
                break;
        }
    }
    
    private function deserializeJourney($data)
    {
        $journey = new PTJourney(count($this->collection)+1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
        if (isset($data["Distance"])) {
            $journey->setDistance($data["Distance"]);
        }
        if (isset($data["Duration"])) {
            $journey->setDuration($data["Duration"]);
        }
        if (isset($data["DepartureTime"])) {
            $departure = new PTDeparture(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
            $departure->setDate(\DateTime::createFromFormat(self::DATETIME_OUTPUT_FORMAT, $data["DepartureTime"]));
            if (isset($data["Departure"]["Site"])) {
                $departureAddress = new Address();
                $departureAddress->setId(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                $departureAddress->setAddressCountry(self::CW_COUNTRY);
                $departureAddress->setAddressLocality(self::CW_NC);
                $departureAddress->setStreetAddress(self::CW_NC);
                if (isset($data["Departure"]["Site"]["CityName"]) && !is_null($data["Departure"]["Site"]["CityName"])) {
                    $departureAddress->setAddressLocality($data["Departure"]["Site"]["CityName"]);
                }
                if (isset($data["Departure"]["Site"]["Name"]) && !is_null($data["Departure"]["Site"]["Name"])) {
                    $departureAddress->setStreetAddress($data["Departure"]["Site"]["Name"]);
                }
                if (isset($data["Departure"]["Site"]["Position"]["Lat"]) && !is_null($data["Departure"]["Site"]["Position"]["Lat"])) {
                    $departureAddress->setLatitude($data["Departure"]["Site"]["Position"]["Lat"]);
                }
                if (isset($data["Departure"]["Site"]["Position"]["Long"]) && !is_null($data["Departure"]["Site"]["Position"]["Long"])) {
                    $departureAddress->setLongitude($data["Departure"]["Site"]["Position"]["Long"]);
                }
                $departure->setAddress($departureAddress);
            }
            $journey->setPTDeparture($departure);
        }
        if (isset($data["ArrivalTime"])) {
            $arrival = new PTArrival(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
            $arrival->setDate(\DateTime::createFromFormat(self::DATETIME_OUTPUT_FORMAT, $data["ArrivalTime"]));
            if (isset($data["Arrival"]["Site"])) {
                $arrivalAddress = new Address();
                $arrivalAddress->setId(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                $arrivalAddress->setAddressCountry(self::CW_COUNTRY);
                $arrivalAddress->setAddressLocality(self::CW_NC);
                $arrivalAddress->setStreetAddress(self::CW_NC);
                if (isset($data["Arrival"]["Site"]["CityName"]) && !is_null($data["Arrival"]["Site"]["CityName"])) {
                    $arrivalAddress->setAddressLocality($data["Arrival"]["Site"]["CityName"]);
                }
                if (isset($data["Arrival"]["Site"]["Name"]) && !is_null($data["Arrival"]["Site"]["Name"])) {
                    $arrivalAddress->setStreetAddress($data["Arrival"]["Site"]["Name"]);
                }
                if (isset($data["Arrival"]["Site"]["Position"]["Lat"]) && !is_null($data["Arrival"]["Site"]["Position"]["Lat"])) {
                    $arrivalAddress->setLatitude($data["Arrival"]["Site"]["Position"]["Lat"]);
                }
                if (isset($data["Arrival"]["Site"]["Position"]["Long"]) && !is_null($data["Arrival"]["Site"]["Position"]["Long"])) {
                    $arrivalAddress->setLongitude($data["Arrival"]["Site"]["Position"]["Long"]);
                }
                $arrival->setAddress($arrivalAddress);
            }
            $journey->setPTArrival($arrival);
        }
        if (isset($data["sections"]["Section"])) {
            $sections = [];
            foreach ($data["sections"]["Section"] as $section) {
                $sections[] = self::deserializeSection($section, count($sections)+1);
            }
            $journey->setPTSections($sections);
        }
        if (isset($data["CarbonFootprint"]["TripCO2"])) {
            $journey->setCo2($data["CarbonFootprint"]["TripCO2"]);
        }
        return $journey;
    }
        
    private function deserializeSection($data, $num)
    {
        $section = new PTSection($num);
        if (isset($data["Leg"]) && !is_null($data["Leg"])) {
            // walk mode
            $ptmode = new PTMode(PTMode::PT_MODE_WALK);
            $section->setPTmode($ptmode);
            if (isset($data["Leg"]["Duration"]) && !is_null($data["Leg"]["Duration"])) {
                $section->setDuration($data["Leg"]["Duration"]);
            }
            if (isset($data["Leg"]["pathLinks"]["PathLink"])) {
                $ptsteps = [];
                foreach ($data["Leg"]["pathLinks"]["PathLink"] as $pathLink) {
                    $ptsteps[] = self::deserializePTStep($pathLink, count($ptsteps)+1);
                }
                $section->setPTSteps($ptsteps);
            }
        }
        if (isset($data["PTRide"]) && !is_null($data["PTRide"])) {
            if ($data["PTRide"]["TransportMode"] == self::CW_PT_MODE_BUS) {
                // bus mode
                $ptmode = new PTMode(PTMode::PT_MODE_BUS);
                $section->setPTMode($ptmode);
            }
            if ($data["PTRide"]["TransportMode"] == self::CW_PT_MODE_TRAIN) {
                // train mode
                $ptmode = new PTMode(PTMode::PT_MODE_TRAIN);
                $section->setPTMode($ptmode);
            }
            if (isset($data["PTRide"]["Departure"])) {
                $departure = new PTDeparture(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                if (isset($data["PTRide"]["Departure"]["Time"])) {
                    $departure->setDate(\DateTime::createFromFormat(self::DATETIME_OUTPUT_FORMAT, $data["PTRide"]["Departure"]["Time"]));
                }
                if (isset($data["PTRide"]["Departure"]["StopPlace"])) {
                    $departureAddress = new Address();
                    $departureAddress->setId(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                    $departureAddress->setAddressCountry(self::CW_COUNTRY);
                    $departureAddress->setAddressLocality(self::CW_NC);
                    $departureAddress->setStreetAddress(self::CW_NC);
                    if (isset($data["PTRide"]["Departure"]["StopPlace"]["CityName"]) && !is_null($data["PTRide"]["Departure"]["StopPlace"]["CityName"])) {
                        $departureAddress->setAddressLocality($data["PTRide"]["Departure"]["StopPlace"]["CityName"]);
                    }
                    if (isset($data["PTRide"]["Departure"]["StopPlace"]["Name"]) && !is_null($data["PTRide"]["Departure"]["StopPlace"]["Name"])) {
                        $departure->setName($data["PTRide"]["Departure"]["StopPlace"]["Name"]);
                    }
                    if (isset($data["PTRide"]["Departure"]["StopPlace"]["Position"]["Lat"]) && !is_null($data["PTRide"]["Departure"]["StopPlace"]["Position"]["Lat"])) {
                        $departureAddress->setLatitude($data["PTRide"]["Departure"]["StopPlace"]["Position"]["Lat"]);
                    }
                    if (isset($data["PTRide"]["Departure"]["StopPlace"]["Position"]["Long"]) && !is_null($data["PTRide"]["Departure"]["StopPlace"]["Position"]["Long"])) {
                        $departureAddress->setLongitude($data["PTRide"]["Departure"]["StopPlace"]["Position"]["Long"]);
                    }
                    $departure->setAddress($departureAddress);
                }
                $section->setPTDeparture($departure);
            }
            if (isset($data["PTRide"]["Arrival"])) {
                $arrival = new PTArrival(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                if (isset($data["PTRide"]["Arrival"]["Time"])) {
                    $arrival->setDate(\DateTime::createFromFormat(self::DATETIME_OUTPUT_FORMAT, $data["PTRide"]["Arrival"]["Time"]));
                }
                if (isset($data["PTRide"]["Arrival"]["StopPlace"])) {
                    $arrivalAddress = new Address();
                    $arrivalAddress->setId(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                    $arrivalAddress->setAddressCountry(self::CW_COUNTRY);
                    $arrivalAddress->setAddressLocality(self::CW_NC);
                    $arrivalAddress->setStreetAddress(self::CW_NC);
                    if (isset($data["PTRide"]["Arrival"]["StopPlace"]["CityName"]) && !is_null($data["PTRide"]["Arrival"]["StopPlace"]["CityName"])) {
                        $arrivalAddress->setAddressLocality($data["PTRide"]["Arrival"]["StopPlace"]["CityName"]);
                    }
                    if (isset($data["PTRide"]["Arrival"]["StopPlace"]["Name"]) && !is_null($data["PTRide"]["Arrival"]["StopPlace"]["Name"])) {
                        $arrival->setName($data["PTRide"]["Arrival"]["StopPlace"]["Name"]);
                    }
                    if (isset($data["PTRide"]["Arrival"]["StopPlace"]["Position"]["Lat"]) && !is_null($data["PTRide"]["Arrival"]["StopPlace"]["Position"]["Lat"])) {
                        $arrivalAddress->setLatitude($data["PTRide"]["Arrival"]["StopPlace"]["Position"]["Lat"]);
                    }
                    if (isset($data["PTRide"]["Arrival"]["StopPlace"]["Position"]["Long"]) && !is_null($data["PTRide"]["Arrival"]["StopPlace"]["Position"]["Long"])) {
                        $arrivalAddress->setLongitude($data["PTRide"]["Arrival"]["StopPlace"]["Position"]["Long"]);
                    }
                    $arrival->setAddress($arrivalAddress);
                }
                $section->setPTArrival($arrival);
            }
            if (isset($data["PTRide"]["Distance"]) && !is_null($data["PTRide"]["Distance"])) {
                $section->setDistance($data["PTRide"]["Distance"]);
            }
            if (isset($data["PTRide"]["Duration"]) && !is_null($data["PTRide"]["Duration"])) {
                $section->setDuration($data["PTRide"]["Duration"]);
            }
            if (isset($data["PTRide"]["Line"])) {
                $ptline = new PTLine(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                if (isset($data["PTRide"]["Line"]["Name"])) {
                    $ptline->setName($data["PTRide"]["Line"]["Name"]);
                }
                if (isset($data["PTRide"]["Line"]["Number"])) {
                    $ptline->setNumber($data["PTRide"]["Line"]["Number"]);
                }
                if (isset($data["PTRide"]["Line"]["Direction"]["Name"])) {
                    $section->setDirection($data["PTRide"]["Line"]["Direction"]["Name"]);
                }
                $section->setPTLine($ptline);
            }
            if (isset($data["PTRide"]["Direction"]["Name"])) {
                $section->setDirection($data["PTRide"]["Direction"]["Name"]);
            }
            if (isset($data["PTRide"]["steps"]["Step"])) {
                $ptsteps = [];
                foreach ($data["PTRide"]["steps"]["Step"] as $step) {
                    $ptsteps[] = self::deserializePTStep($step, count($ptsteps)+1);
                }
                $section->setPTSteps($ptsteps);
            }
        }
        return $section;
    }
    
    private function deserializePTStep($data, $num)
    {
        $ptstep = new PTStep($num);
        if (isset($data["Departure"])) {
            $departure = new PTDeparture(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
            if (isset($data["Departure"]["Time"])) {
                $departure->setDate(\DateTime::createFromFormat(self::DATETIME_OUTPUT_FORMAT, $data["Departure"]["Time"]));
            }
            if (isset($data["Departure"]["Site"])) {
                $departureAddress = new Address();
                $departureAddress->setId(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                $departureAddress->setAddressCountry(self::CW_COUNTRY);
                $departureAddress->setAddressLocality(self::CW_NC);
                $departureAddress->setStreetAddress(self::CW_NC);
                if (isset($data["Departure"]["Site"]["CityName"]) && !is_null($data["Departure"]["Site"]["CityName"])) {
                    $departureAddress->setAddressLocality($data["Departure"]["Site"]["CityName"]);
                }
                if (isset($data["Departure"]["Site"]["Name"]) && !is_null($data["Departure"]["Site"]["Name"])) {
                    $departureAddress->setStreetAddress($data["Departure"]["Site"]["Name"]);
                }
                if (isset($data["Departure"]["Site"]["Position"]["Lat"]) && !is_null($data["Departure"]["Site"]["Position"]["Lat"])) {
                    $departureAddress->setLatitude($data["Departure"]["Site"]["Position"]["Lat"]);
                }
                if (isset($data["Departure"]["Site"]["Position"]["Long"]) && !is_null($data["Departure"]["Site"]["Position"]["Long"])) {
                    $departureAddress->setLongitude($data["Departure"]["Site"]["Position"]["Long"]);
                }
                $departure->setAddress($departureAddress);
            }
            $ptstep->setPTDeparture($departure);
        }
        if (isset($data["Arrival"])) {
            $arrival = new PTArrival(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
            if (isset($data["Arrival"]["Time"])) {
                $arrival->setDate(\DateTime::createFromFormat(self::DATETIME_OUTPUT_FORMAT, $data["Arrival"]["Time"]));
            }
            if (isset($data["Arrival"]["Site"])) {
                $arrivalAddress = new Address();
                $arrivalAddress->setId(1); // we have to set an id as it's mandatory when using a custom data provider (see https://api-platform.com/docs/core/data-providers)
                $arrivalAddress->setAddressCountry(self::CW_COUNTRY);
                $arrivalAddress->setAddressLocality(self::CW_NC);
                $arrivalAddress->setStreetAddress(self::CW_NC);
                if (isset($data["Arrival"]["Site"]["CityName"]) && !is_null($data["Arrival"]["Site"]["CityName"])) {
                    $arrivalAddress->setAddressLocality($data["Arrival"]["Site"]["CityName"]);
                }
                if (isset($data["Arrival"]["Site"]["Name"]) && !is_null($data["Arrival"]["Site"]["Name"])) {
                    $arrivalAddress->setStreetAddress($data["Arrival"]["Site"]["Name"]);
                }
                if (isset($data["Arrival"]["Site"]["Position"]["Lat"]) && !is_null($data["Arrival"]["Site"]["Position"]["Lat"])) {
                    $arrivalAddress->setLatitude($data["Arrival"]["Site"]["Position"]["Lat"]);
                }
                if (isset($data["Arrival"]["Site"]["Position"]["Long"]) && !is_null($data["Arrival"]["Site"]["Position"]["Long"])) {
                    $arrivalAddress->setLongitude($data["Arrival"]["Site"]["Position"]["Long"]);
                }
                $arrival->setAddress($arrivalAddress);
            }
            $ptstep->setPTArrival($arrival);
        }
        if (isset($data["Distance"]) && !is_null($data["Distance"])) {
            $ptstep->setDistance($data["Distance"]);
        }
        if (isset($data["Duration"]) && !is_null($data["Duration"])) {
            $ptstep->setDuration($data["Duration"]);
        }
        if (isset($data["MagneticDirection"]) && !is_null($data["MagneticDirection"])) {
            $ptstep->setMagneticDirection($data["MagneticDirection"]);
        }
        if (isset($data["RelativeDirection"]) && !is_null($data["RelativeDirection"])) {
            $ptstep->setRelativeDirection($data["RelativeDirection"]);
        }
        return $ptstep;
    }
}