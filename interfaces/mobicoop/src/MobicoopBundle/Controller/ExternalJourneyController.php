<?php
/**
 * Created by PhpStorm.
 * User: Sofiane Belaribi
 * Date: 31/10/2018
 * Time: 15:42
 */

namespace Mobicoop\Bundle\MobicoopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Mobicoop\Bundle\MobicoopBundle\Service\ExternalJourneyManager;

use GuzzleHttp\Client;

class ExternalJourneyController extends AbstractController
{

    /**
     *
     * @Route("/ext")
     *
     */
    public function externalJourney(ExternalJourneyManager $externalJourneyManager)
    {
        $hydra = $externalJourneyManager->getExternalJourney();

        return $this->render('@Mobicoop/default/external.html.twig', [
            'hydra' => $hydra
        ]);
    }

    /**
     *
     * @Route("/tes")
     *
     */
    public function testApi(Request $request)
    {

        //initialize client API
        $client = new Client([
            'base_uri' => $_ENV['API_URI'],
            //20s because i'm working on a long request but you can change it
            'timeout'  => 20.0,
        ]);


        //request to API
        $dataexternaljourney = $client->request('GET', 'external_journeys');
        $externaljourney = $dataexternaljourney->getBody()->getContents();
        $externaljourney = json_decode($externaljourney,true);
        //var_dump($externaljourney);
        return $this->render('@Mobicoop/default/external2.html.twig', [
            'externaljourney' => $externaljourney
        ]);
    }
}