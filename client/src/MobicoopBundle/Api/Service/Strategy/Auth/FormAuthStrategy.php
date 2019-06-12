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

namespace Mobicoop\Bundle\MobicoopBundle\Api\Service\Strategy\Auth;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FormAuthStrategy
 * based on https://github.com/eljam/guzzle-jwt-middleware
 */
class FormAuthStrategy extends AbstractBaseAuthStrategy
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'form_fields' => ['_username', '_password'],
        ]);

        $resolver->setRequired(['form_fields']);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestOptions()
    {
        return [
            \GuzzleHttp\RequestOptions::FORM_PARAMS => [
                $this->options['form_fields'][0] => $this->options['username'],
                $this->options['form_fields'][1] => $this->options['password'],
            ],
        ];
    }
}