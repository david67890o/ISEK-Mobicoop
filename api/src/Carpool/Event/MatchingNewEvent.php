<?php

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

namespace App\Carpool\Event;

use Symfony\Component\EventDispatcher\Event;
use App\Carpool\Entity\Matching;

/**
 * Event sent when a new matching is created.
 */
class MatchingNewEvent extends Event
{
    public const NAME = 'carpool_matching_new';

    protected $matching;

    public function __construct(Matching $matching)
    {
        $this->matching = $matching;
    }

    public function getMatching()
    {
        return $this->matching;
    }
}
