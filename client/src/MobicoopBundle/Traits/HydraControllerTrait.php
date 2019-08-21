<?php

namespace Mobicoop\Bundle\MobicoopBundle\Traits;

use Mobicoop\Bundle\MobicoopBundle\JsonLD\Entity\Hydra;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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

trait HydraControllerTrait
{
    /**
     * @param Hydra|array|object $hydra
     */
    public function handleManagerReturnValue($hydra)
    {
    
        /** @var SessionInterface $session */
        $session= $this->get('session');
        if ($hydra instanceof Hydra) {
            if ($hydra->getType() == "hydra:Error") {
                $session->set('hydra', $hydra);
                return $this->redirectToRoute('api_hydra_errors');
            }
        }
    }
}
