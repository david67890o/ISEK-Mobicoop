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

namespace App\Geography\Controller;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

use Geocoder\Plugin\PluginProvider;
use Geocoder\Query\GeocodeQuery;

/**
 * CompletionController.php
 *
 * @author Sofiane Belaribi <sofiane.belaribi@mobicoop.org>
 * Date: 16/11/2018
 * Time: 9:25
 *
 */
class CompletionController
{
    protected $request;
    protected $container;

    /**
     * CompletionController constructor.
     * @param RequestStack $requestStack
     * @param Container $container
     * @param PluginProvider $chain
     */
    public function __construct(RequestStack $requestStack, Container $container, PluginProvider $chain)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->container = $container;
        $this->container = $chain;
    }

    /**
     * This method is invoked when autocomplete function is called.
     * @param array $data
     * @return array
     * @throws \Geocoder\Exception\Exception
     */
    public function __invoke(array $data): array
    {
        $input = $this->request->get("input");
        $result= $this->container
            ->geocodeQuery(GeocodeQuery::create($input))->all();

        return $result;
    }
}
