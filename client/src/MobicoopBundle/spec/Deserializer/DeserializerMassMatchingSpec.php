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

namespace Mobicoop\Bundle\MobicoopBundle\Spec\Service;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\Deserializer;
use Mobicoop\Bundle\MobicoopBundle\Match\Entity\MassMatching;

/**
 * DeserializerMassMatchingSpec.php
 * Tests for Deserializer - MassMatching
 * @author Sylvain briat <sylvain.briat@mobicoop.org>
 * Date: 24/06/2019
 * Time: 14:00
 *
 */

describe('deserializeMassMatching', function () {
    it('deserialize MassMatching should return a MassMatching object', function () {
        $jsonMassMatching = <<<JSON
{
  "massPerson1Id": 1,
  "massPerson2Id": 1,
  "distance": 10,
  "duration": 20
}
JSON;

        $deserializer = new Deserializer();
        $massMatching = $deserializer->deserialize(MassMatching::class, json_decode($jsonMassMatching, true));
        expect($massMatching)->toBeAnInstanceOf(MassMatching::class);
    });
});
