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

namespace Mobicoop\Bundle\MobicoopBundle\Controller;

use Mobicoop\Bundle\MobicoopBundle\Service\GeoSearchManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;

/**
 * TestAutoCompleteController.php
 * Class
 * @author Sofiane Belaribi <sofiane.belaribi@mobicoop.org>
 * Date: 27/11/2018
 * Time: 13:23
 *
 */

class AutoCompleteController extends AbstractController
{

    /**
     * Retrieve all geosearch results of an input
     *
     * @Route("/aut")
     */
    public function AutoCompleteIndex(GeoSearchManager $geoSearchManager)
    {
        return $this->render(
            '@Mobicoop/autocomplete/index.html.twig',
            [
                'GeoSearch' => $geoSearchManager->getGeoSearch(['input'=>'Nancy'])
            ]
        );
    }
}