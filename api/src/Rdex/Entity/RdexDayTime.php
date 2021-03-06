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

namespace App\Rdex\Entity;

/**
 * An RDEX Day time.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class RdexDayTime implements \JsonSerializable
{
    /**
     * @var string The min time.
     */
    private $mintime;
    
    /**
     * @var string The max time.
     */
    private $maxtime;
    
    /**
     * @return string
     */
    public function getMintime()
    {
        return $this->mintime;
    }

    /**
     * @return string
     */
    public function getMaxtime()
    {
        return $this->maxtime;
    }

    /**
     * @param string $mintime
     */
    public function setMintime($mintime)
    {
        $this->mintime = $mintime;
    }

    /**
     * @param string $maxtime
     */
    public function setMaxtime($maxtime)
    {
        $this->maxtime = $maxtime;
    }
    
    public function jsonSerialize()
    {
        return
        [
            'mintime'   => $this->getMintime(),
            'maxtime'   => $this->getMaxtime()
        ];
    }
}
