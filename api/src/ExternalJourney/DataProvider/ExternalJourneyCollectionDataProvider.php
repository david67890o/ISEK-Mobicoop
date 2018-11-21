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

namespace App\ExternalJourney\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use Symfony\Component\HttpFoundation\RequestStack;
use GuzzleHttp\Client;

use App\ExternalJourney\Entity\ExternalJourney;

/**
 * Collection data provider for External Journey entity.
 *
 * Automatically associated to External Journey entity thanks to autowiring (see 'supports' method).
 *
 * @author Sofiane Belaribi <sofiane.belaribi@covivo.eu>
 *
 */
final class ExternalJourneyCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private const EXTERNAL_JOURNEY_CONFIG_FILE = "../config.json";
    private const EXTERNAL_JOURNEY_API_KEY = "rdexApi";
    private const EXTERNAL_JOURNEY_HASH = "sha256";         // hash algorithm
    
    protected $request;

    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return ExternalJourney::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null): array
    {
        // initialize client API for any request
        $client = new Client([
            //10s because i'm working on long requests but you can change it
            'timeout'  => 10.0,
        ]);
        // we collect search parameters here
        $providerName = $this->request->get("provider_name");
        $driver = $this->request->get("driver");
        $passenger = $this->request->get("passenger");
        $fromLatitude = $this->request->get("from_latitude");
        $fromLongitude = $this->request->get("from_longitude");
        $toLatitude = $this->request->get("to_latitude");
        $toLongitude = $this->request->get("to_longitude");
        // then we set these parameters
        $searchParameters  = [
            'driver'  => [
                'state'   => $driver //1
            ],
            'passenger' => [
                'state'   => $passenger //1
            ],
            'from'    => [
                'latitude'  => $fromLatitude, //Nancy=48.69278
                'longitude' => $fromLongitude //6.18361
            ],
            'to'    => [
                'latitude'  => $toLatitude,//Metz=49.11972
                'longitude' => $toLongitude//6.17694
            ]
        ];

        // @todo error management (api not responding, bad parameters...)
        // if config.json exists we collect its parameters and request all apis
        if (file_exists(self::EXTERNAL_JOURNEY_CONFIG_FILE)) {
            // read config.json
            $providerList = json_decode(file_get_contents(self::EXTERNAL_JOURNEY_CONFIG_FILE), true);
            if (isset($providerList[self::EXTERNAL_JOURNEY_API_KEY][$providerName])) {
                $provider = $providerList[self::EXTERNAL_JOURNEY_API_KEY][$providerName];
                $apiUrl = $provider["apiUrl"];
                $apiResource = $provider["apiResource"];
                $apiKey = $provider["apiKey"];
                $privateKey = $provider["privateKey"];
                
                $query = array(
                    'timestamp' => time(),
                    'apikey'    => $apiKey,
                    'p'         => $searchParameters //optional if POST
                );
                
                // construct the requested url
                $url = $apiUrl.$apiResource.'?'.http_build_query($query);
                $signature = hash_hmac(self::EXTERNAL_JOURNEY_HASH, $url, $privateKey);
                $signedUrl = $url.'&signature='.$signature;
                
                // request url
                $data = $client->request('GET', $signedUrl);
                $data = $data->getBody()->getContents();
                return json_decode($data, true);
            }  
            return [];
        }
        return ["no config.json found"];
    }
}
