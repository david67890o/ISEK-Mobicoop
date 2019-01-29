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

namespace Mobicoop\Bundle\MobicoopBundle\Image\Service;

use Mobicoop\Bundle\MobicoopBundle\Image\Entity\Image;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;

/**
 * Image management service.
 */
class ImageManager
{
    private $dataProvider;
    
    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(Image::class);
    }
    
    /**
     * Create an event
     *
     * @param Image $image The image to create
     *
     * @return Image|null The image created or null if error.
     */
    public function createImage(Image $image)
    {
        $response = $this->dataProvider->postMultiPart($image);
        if ($response->getCode() == 201) {
            return $response->getValue();
        }
        return null;
    }
}
