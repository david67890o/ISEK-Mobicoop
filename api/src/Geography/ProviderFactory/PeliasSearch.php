<?php

declare(strict_types=1);

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

namespace App\Geography\ProviderFactory;

use Geocoder\Collection;
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Provider\Provider;
use Http\Client\HttpClient;

/**
 * @author Sylvain Briat
 */
final class PeliasSearch extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'search?text=%s&size=%d&lang=%s';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'reverse?point.lat=%f&point.lon=%f&size=%d&lang=%s';

    /**
     * @var string
     */
    private $uri;

    /**
     * @param HttpClient $client an HTTP adapter
     * @param string     $uri the api uri
     */
    public function __construct(HttpClient $client, string $uri=null)
    {
        $this->uri = $uri;
        parent::__construct($client);
    }

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $address = $query->getText();
        $url = sprintf($this->uri.self::GEOCODE_ENDPOINT_URL, urlencode($address), $query->getLimit(), $query->getLocale());
        return $this->executeQuery($url);
    }
    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        $coordinates = $query->getCoordinates();
        $longitude = $coordinates->getLongitude();
        $latitude = $coordinates->getLatitude();
        $url = sprintf($this->uri.self::REVERSE_ENDPOINT_URL, $latitude, $longitude, $query->getLimit(), $query->getLocale());
        return $this->executeQuery($url);
    }
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'pelias_search';
    }
    /**
     * @param $url
     *
     * @return Collection
     */
    private function executeQuery(string $url): AddressCollection
    {
        $content = $this->getUrlContents($url);
        $json = json_decode($content, true);
        if (!isset($json['type']) || 'FeatureCollection' !== $json['type'] || !isset($json['features']) || 0 === count($json['features'])) {
            return new AddressCollection([]);
        }
        $locations = $json['features'];
        if (empty($locations)) {
            return new AddressCollection([]);
        }
        $results = [];
        foreach ($locations as $location) {
            $bounds = [
                'south' => null,
                'west' => null,
                'north' => null,
                'east' => null,
            ];
            if (isset($location['bbox'])) {
                $bounds = [
                    'south' => $location['bbox'][3],
                    'west' => $location['bbox'][2],
                    'north' => $location['bbox'][1],
                    'east' => $location['bbox'][0],
                ];
            }
            $props = $location['properties'];
            $adminLevels = [];
            foreach (['localadmin', 'county', 'macrocounty', 'region', 'macroregion'] as $i => $component) {
                if (isset($props[$component])) {
                    $adminLevels[] = ['name' => $props[$component], 'level' => $i + 1];
                }
            }
            $results[] = Address::createFromArray([
                'providedBy' => $this->getName(),
                'latitude' => $location['geometry']['coordinates'][1],
                'longitude' => $location['geometry']['coordinates'][0],
                'bounds' => $bounds,
                'streetNumber' => isset($props['housenumber']) ? $props['housenumber'] : null,
                'streetName' => isset($props['street']) ? $props['street'] : null,
                'subLocality' => isset($props['neighbourhood']) ? $props['neighbourhood'] : null,
                'locality' => isset($props['locality']) ? $props['locality'] : null,
                'postalCode' => isset($props['postalcode']) ? $props['postalcode'] : null,
                'adminLevels' => $adminLevels,
                'country' => isset($props['country']) ? $props['country'] : null,
                'countryCode' => isset($props['country_a']) ? strtoupper($props['country_a']) : null,
            ]);
        }
        return new AddressCollection($results);
    }
    /**
     * @param array $components
     *
     * @return null|string
     */
    protected function guessLocality(array $components)
    {
        $localityKeys = ['city', 'town', 'village', 'hamlet'];
        return $this->guessBestComponent($components, $localityKeys);
    }
    /**
     * @param array $components
     *
     * @return null|string
     */
    protected function guessStreetName(array $components)
    {
        $streetNameKeys = ['road', 'street', 'street_name', 'residential'];
        return $this->guessBestComponent($components, $streetNameKeys);
    }
    /**
     * @param array $components
     *
     * @return null|string
     */
    protected function guessSubLocality(array $components)
    {
        $subLocalityKeys = ['neighbourhood', 'city_district'];
        return $this->guessBestComponent($components, $subLocalityKeys);
    }
    /**
     * @param array $components
     * @param array $keys
     *
     * @return null|string
     */
    protected function guessBestComponent(array $components, array $keys)
    {
        foreach ($keys as $key) {
            if (isset($components[$key]) && !empty($components[$key])) {
                return $components[$key];
            }
        }
        return null;
    }
}
