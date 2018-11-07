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
     * @Route("/tes")
     *
     */
    public function guzzleRequestExternalJourney(Request $request)
    {
        $driver = 1;
        $passenger = 1;
        $from_latitude = 48.69278;
        $from_longitude = 6.18361;
        $to_latitude = 49.11972;
        $to_longitude = 6.17694;

        //initialize client API
        $client = new Client([
            'base_uri' => $_ENV['API_URI'],
            //10s because i'm working on a long request but you can change it
            'timeout'  => 10.0,
        ]);
        //example of the request url to api
        //http://localhost:8080/external_journeys?driver=1&passenger=1&from_latitude=48.69278&from_longitude=6.18361&to_latitude=49.11972&to_longitude=6.17694

        //request to API
        $dataexternaljourney = $client->request(
            'GET',
    'external_journeys?driver='.$driver.'&passenger='.$passenger.'&from_latitude='.$from_latitude.'&from_longitude='.$from_longitude.'&to_latitude='.$to_latitude.'&to_longitude='.$to_longitude
        );
        //pass  data to string
        $externaljourney = $dataexternaljourney->getBody()->getContents();
        //pass string to json
        $externaljourney = json_decode($externaljourney, true);
        return $this->render('@Mobicoop/default/externalAsync.html.twig', [
            'externaljourney' => $externaljourney
        ]);
    }

    /**
     *
     * @Route("/external_journeys/driver={driver}&passenger={passenger}&from_latitude={from_latitude}&from_longitude={from_longitude}&to_latitude={to_latitude}&to_longitude={to_longitude}")
     *
     */
    public function guzzleCustomRequestExternalJourney($driver, $passenger, $from_latitude, $from_longitude, $to_latitude, $to_longitude, Request $request)
    {
        //url example : http://localhost:8081/external_journeys/driver=1&passenger=1&from_latitude=48.69278&from_longitude=6.18361&to_latitude=49.11972&to_longitude=6.17694
                //initialize client API
        $client = new Client([
            'base_uri' => $_ENV['API_URI'],
            //10s because i'm working on a long request but you can change it
            'timeout'  => 10.0,
        ]);

        //request to API
        $dataexternaljourney = $client->request(
            'GET',
            'external_journeys?driver='.$driver.'&passenger='.$passenger.'&from_latitude='.$from_latitude.'&from_longitude='.$from_longitude.'&to_latitude='.$to_latitude.'&to_longitude='.$to_longitude
        );
        //pass  data to string
        $externaljourney = $dataexternaljourney->getBody()->getContents();
        //pass string to json
        $externaljourney = json_decode($externaljourney, true);
        //var_dump($externaljourney);
        return $this->render('@Mobicoop/default/externalAsync.html.twig', [
            'externaljourney' => $externaljourney
        ]);
    }
}
