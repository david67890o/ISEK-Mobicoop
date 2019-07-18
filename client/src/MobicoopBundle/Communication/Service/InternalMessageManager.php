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

namespace Mobicoop\Bundle\MobicoopBundle\Communication\Service;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Communication\Entity\Message;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider as MobicoopDataProvider;

/**
 * Mass management service.
 */
class InternalMessageManager
{
    private $dataProvider;

    /**
    * Constructor.
    * @param DataProvider $dataProvider The data provider that provides the Mass
    */
    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(Message::class);
    }

    /**
     * Get a message
     *
     * @param int $idMessage Id of the message
     *
     */
    public function getMessage(int $idMessage)
    {
        $response = $this->dataProvider->getItem($idMessage);
        if ($response->getCode() == 200) {
            return $response->getValue();
        }
        return null;
    }
    
    /**
     * Get complete thread from a message
     *
     * @param int $id The first message id
     *
     * @return array|null The complete thread
     */
    public function getCompleteThread(int $id, $format=null)
    {
        if ($format!==null) {
            $this->dataProvider->setFormat($format);
        }
        $response = $this->dataProvider->getSubCollection($id, Message::class, "completeThread");
        if ($response->getCode() == 200) {
            return $response->getValue();
        }
        return null;
    }

    /**
     * Send an internal message
     *
     * @param Message $message The message to send
     *
     */
    public function sendInternalMessage(Message $message)
    {
        $response = $this->dataProvider->post($message);
        if ($response->getCode() == 201) {
            return $response->getValue();
        }
        return null;
    }
}
