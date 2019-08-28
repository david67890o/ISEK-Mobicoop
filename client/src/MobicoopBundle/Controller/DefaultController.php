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

namespace Mobicoop\Bundle\MobicoopBundle\Controller;

use GuzzleHttp\Client;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Proposal;
use Mobicoop\Bundle\MobicoopBundle\Filter\ExcelFilter;
use Mobicoop\Bundle\MobicoopBundle\JsonLD\Entity\Hydra;
use Mobicoop\Bundle\MobicoopBundle\Traits\HydraControllerTrait;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xls;

class DefaultController extends AbstractController
{
    use HydraControllerTrait;

    /**
     * HomePage
     * @Route("/", name="home")
     *
     */
    public function index()
    {
        return $this->render(
            '@Mobicoop/default/index.html.twig',
            [
                'baseUri' => $_ENV['API_URI'],
                'searchRoute' => 'covoiturage/recherche',
                'metaDescription' => 'Homepage of Mobicoop'
            ]
        );
    }
    
    /**
     * Error Page.
     * @Route("/provider/errors", name="api_hydra_errors")
     *
     */
    public function showErrorsAction()
    {
        $session= $this->get('session');
        $hydra = $session->get('hydra');
        if ($hydra instanceof Hydra) {
            return $this->render('@Mobicoop/hydra/error.html.twig', ['hydra'=> $hydra]);
        }
        return null;
    }

    /**
    * Search page.
    * @Route("/addresses/search", name="api_geo_search")
    */
    public function geoSearchAction(Request $request)
    {
        $client= new Client();
        $gresponse= $client->request('GET', 'http://api.mobicoop.loc/addresses/search?q='.$request->get('q'));
        return new JsonResponse(json_decode($gresponse->getBody()));
    }

    /**
     * publie les annonces automatiquements.
     * @Route("/annonces/publish", name="publish_annonce")
     *
     * @param ParameterBagInterface $params
     * @param DataProvider $dataProvider
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \ReflectionException
     */
    public function publishAnnonce(ParameterBagInterface $params, DataProvider $dataProvider)
    {
        $client= new Client();
        $publicDirectory= $params->get('kernel.project_dir');
        $chemin= $publicDirectory.'/src/MobicoopBundle/Resources/public/data/annonces.xls';
        $cheminjson= $publicDirectory.'/src/MobicoopBundle/Resources/public/data/annonce.json.dist';
        $json = json_decode(file_get_contents($cheminjson), true);
        $dataProvider->setClass(User::class);
        /** @var User[] $users */
        $usersCollection=  $dataProvider->getCollection();
        $users= $usersCollection->getValue()->getMember();
        $reader = new Xls();
        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly(["annonces"]);
        $reader->setReadFilter(new ExcelFilter());
        $spreadsheet = $reader->load($chemin);
        $datas=$spreadsheet->getActiveSheet()->toArray();
        $title= array_shift($datas);
        $userIndex=0;
        $numUser= count($users);
        $sprintDays= 12;
        $startdate= date('Y-m-d');
        $nextdate= strtotime($startdate);
        $maxtimestring= ('+'.$sprintDays.' day');
        $maxdate= strtotime($maxtimestring);
        $proposals=[];
        foreach ($datas as $loopIndex => $data) {
            $date= date('Y-m-d', $nextdate).'T08:00:00.000Z';
            $proposal= $json;
            $proposal['type']=1;
            $proposal['user']= '/users/'.$users[$userIndex]->getId();
            $proposal['waypoints'][0]['address']['latitude']=$data[16];
            $proposal['waypoints'][0]['address']['longitude']=$data[17];
            $proposal['waypoints'][1]['address']['latitude']=$data[21];
            $proposal['waypoints'][1]['address']['longitude']=$data[22];
            $proposal['criteria']['driver']= ($data[23]!="Passager");
            $proposal['criteria']['passenger']= ($data[23]=="Passager");
            $proposal['criteria']['frequency']= (($data[6]=="Régulière")?10:1);
            $proposal['criteria']['seats']= (($data[6]=="Régulière")?10:1);
            $proposal['criteria']['fromDate']= $date;
            $proposal['criteria']['fromTime']= $date;
            $proposal['criteria']['minTime']= $date;
            $proposal['criteria']['maxTime']= $date;
            $proposal['criteria']['marginDuration']= 600;
            $proposal['criteria']['strictDate']= true;
            $proposal['criteria']['toDate']= $date;
            $proposal['criteria']['anyRouteAsPassenger']= true;
            $proposal['criteria']['multiTransportMode']= true;
            $proposal['criteria']['priceKm']= rand(20, 50)."";
            $proposals[]=$proposal;
            $userIndex= ($userIndex+1)%$numUser;
            $nextdate = strtotime('+1 day', $nextdate);
            if ($nextdate > $maxdate) {
                $nextDate = strtotime($startdate);
            }
        }
        
        $dataProvider->setClass(Proposal::class);
        $databaseProposals= $dataProvider->getCollection()->getValue()->getMember();
        /** @var Proposal $aproposal */
        $aproposal= array_pop($databaseProposals);
        $startCountProposal=$aproposal?$aproposal->getId():0;
        foreach ($proposals as $proposal) {
            $data= $dataProvider->postArray($proposal);
            $reponseofmanager= $this->handleManagerReturnValue($data);
            if (!empty($reponseofmanager)) {
                return $reponseofmanager;
            }
        }
        return new Response(202, "La création des annonces s'est bien déroulé, le jeu de données est prêt pour les tests");
    }
}
