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

namespace App\Carpool\Repository;

use App\Carpool\Entity\Proposal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Carpool\Entity\Criteria;

/**
 * @method Proposal|null find($id, $lockMode = null, $lockVersion = null)
 * @method Proposal|null findOneBy(array $criteria, array $orderBy = null)
 * @method Proposal[]    findAll()
 * @method Proposal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProposalRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Proposal::class);
    }
    
    /**
     * Find proposals matching the proposal passed as an argument.
     *
     * @param Proposal $proposal        The proposal to match
     * @param bool $excludeProposalUser Exclude the matching proposals made by the proposal user
     * @return mixed|\Doctrine\DBAL\Driver\Statement|array|NULL
     */
    public function findMatchingProposals(Proposal $proposal, bool $excludeProposalUser=true)
    {
        
        // LIMITATIONS :
        // - only punctual journeys
        // - only 2 points : starting point and destination
        // - only for the same day
        
        switch ($proposal->getCriteria()->getFrequency()) {
            case Criteria::FREQUENCY_PUNCTUAL:
                return $this->findMatchingForPunctualProposal($proposal, $excludeProposalUser);
                break;
            case Criteria::FREQUENCY_REGULAR:
                return $this->findMatchingForRegularProposal($proposal);
                break;
        }
        
        return null;
    }
    
    /**
     * Search matchings for a punctual proposal.
     *
     * @param Proposal $proposal        The proposal to match
     * @param bool $excludeProposalUser Exclude the matching proposals made by the proposal user
     * @return mixed|\Doctrine\DBAL\Driver\Statement|array|NULL
     */
    private function findMatchingForPunctualProposal(Proposal $proposal, bool $excludeProposalUser=true)
    {
        // LIMITATIONS :
        // - only 2 points : starting point and destination
        // - the matching is made only on the locality, for the same day
        
        // we search for the starting and ending point of the proposal
        $startLocality = null;
        $endLocality = null;
        foreach ($proposal->getPoints() as $point) {
            if ($point->getPosition() == 0) {
                $startLocality = $point->getAddress()->getAddressLocality();
            }
            if ($point->getLastPoint()) {
                $endLocality = $point->getAddress()->getAddressLocality();
            }
            if (!is_null($startLocality) && !is_null($endLocality)) {
                break;
            }
        }
        
        // we search the matchings in the proposal entity
        $query = $this->createQueryBuilder('p')
        // we also need the criteria (for the dates, number of seats...) and the starting/ending points/addresses for the location
        ->join('p.criteria', 'c')
        ->join('p.points', 'startPoint')
        ->join('p.points', 'endPoint')
        ->join('startPoint.address', 'startAddress')
        ->join('endPoint.address', 'endAddress');
        
        // do we exclude the user itself ?
        if ($excludeProposalUser) {
            $query->andWhere('p.user != :user')
            ->setParameter('user', $proposal->getUser());
        }
        
        // we search for the opposite proposal type (offer => requests // request => offers)
        $query->andWhere('p.proposalType = :proposalType')
        ->setParameter('proposalType', ($proposal->getProposalType() == Proposal::PROPOSAL_TYPE_OFFER ? Proposal::PROPOSAL_TYPE_REQUEST : Proposal::PROPOSAL_TYPE_OFFER));
        
        // dates
        // we limit the search to the days after the fromDate and before toDate if it's defined
        // @todo limit automatically the search to the x next days if toDate is not defined ?
        $query->andWhere('c.fromDate >= :fromDate')
        ->setParameter('fromDate', $proposal->getCriteria()->getFromDate()->format('Y-m-d'));
        if (!is_null($proposal->getCriteria()->getToDate())) {
            $query->andWhere('c.fromDate <= :toDate')
            ->setParameter('toDate', $proposal->getCriteria()->getToDate()->format('Y-m-d'));
        }
        
        // we limit the search to the starting and ending point locality
        // @todo use the coordinates
        $query->andWhere('startPoint.position = 0')
        ->andWhere('endPoint.lastPoint = 1');
        $query->andWhere('startAddress.addressLocality = :startLocality')
        ->andWhere('endAddress.addressLocality = :endLocality')
        ->setParameter('startLocality', $startLocality)
        ->setParameter('endLocality', $endLocality);
        
        // we launch the request and return the result
        return $query->getQuery()->getResult();
    }
    
    /**
     * Search matchings for a regular proposal.
     *
     * @param Proposal $proposal
     * @return mixed|\Doctrine\DBAL\Driver\Statement|array|NULL
     */
    private function findMatchingForRegularProposal(Proposal $proposal)
    {
        return null;
    }
}
