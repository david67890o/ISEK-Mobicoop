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

namespace App\Match\Repository;

use App\Match\Entity\MassPerson;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use App\Match\Entity\Mass;

/**
 * @method MassPerson|null find($id, $lockMode = null, $lockVersion = null)
 * @method MassPerson|null findOneBy(array $criteria, array $orderBy = null)
 * @method MassPerson[]    findAll()
 * @method MassPerson[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MassPersonRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(MassPerson::class);
    }

    public function find(int $id): ?MassPerson
    {
        return $this->repository->find($id);
    }

    /**
     * Return all destinations for a mass.
     *
     * @return mixed|NULL|\Doctrine\DBAL\Driver\Statement|array     The destinations (Address) found
     */
    public function findAllDestinationsForMass(Mass $mass)
    {
        $query = $this->repository->createQueryBuilder('mp')
            ->select('DISTINCT wa.houseNumber, wa.street, wa.postalCode, wa.addressLocality, wa.addressCountry, wa.latitude, wa.longitude')
            ->leftJoin('mp.workAddress', 'wa')
            ->andWhere('mp.mass = :mass')
            ->setParameter('mass', $mass)
            ->getQuery();

        return $query->getResult();
    }
}
