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

namespace Mobicoop\Bundle\MobicoopBundle\Spec\Controller;

use Symfony\Component\DomCrawler\Crawler;

/* This is a sample functionnal Test */
describe('DefaultController', function () {
    describe('/', function () {
        it('Index page should return status code 200 & contains "hello" in a h1', function () {
            $request = $this->request->create('/', 'GET');
            $response = $this->kernel->handle($request);

            $status = $response->getStatusCode();
            $crawler = new Crawler($response->getContent());
            $h1 = trim($crawler->filter('body h1')->text());
            $h2 = trim($crawler->filter('body h2')->text());
            $splitedH2 = explode('The random number send form controller is ', $h2);
            $nb = $splitedH2[1];


            expect($status)->toEqual(200);
            expect($h1)->toContain('Coviride');
            expect($splitedH2)->toHaveLength(2);
            expect($nb)->toBeGreaterThan(0);
            expect($nb)->toBeLessThan(26);
            expect($h1)->not->toContain('gloups');
        });
    });
});