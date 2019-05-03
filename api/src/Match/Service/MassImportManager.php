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

namespace App\Match\Service;

use App\Match\Entity\Mass;
use App\Service\FileManager;
use App\User\Repository\UserRepository;
use App\Match\Repository\MassPersonRepository;
use Psr\Log\LoggerInterface;
use App\Match\Exception\MassException;
use App\Match\Entity\MassData;
use App\Match\Entity\MassPerson;
use App\Geography\Entity\Address;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Geography\Service\GeoSearcher;
use App\Geography\Service\GeoRouter;
use App\Geography\Service\ZoneManager;

/**
 * Mass import manager.
 *
 * This service contains methods related to mass matching file import.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class MassImportManager
{
    const MIMETYPE_ZIP = 'application/zip';
    const MIMETYPE_APPLICATION_XML = 'application/xml';
    const MIMETYPE_TEXT_XML = 'text/xml';
    const MIMETYPE_CSV = 'text/csv';
    const MIMETYPE_PLAIN = 'text/plain';
    const MIMETYPE_JSON = 'application/json';

    //const DELAY_BETWEEN_REQUESTS = 1000000; // 1 second delay between 2 requests
    const DELAY_BETWEEN_REQUESTS = 0; // 1 second delay between 2 requests

    private $entityManager;
    private $userRepository;
    private $massPersonRepository;
    private $fileManager;
    private $logger;
    private $params;
    private $validator;
    private $geoSearcher;
    private $geoRouter;
    private $zoneManager;

    /**
     * Constructor
     *
     * @param EntityManagerInterface $entityManager
     * @param UserRepository $userRepository
     * @param FileManager $fileManager
     * @param LoggerInterface $logger
     * @param ValidatorInterface $validator
     * @param array $params
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        MassPersonRepository $massPersonRepository,
        FileManager $fileManager,
        LoggerInterface $logger,
        ValidatorInterface $validator,
        GeoSearcher $geoSearcher,
        GeoRouter $geoRouter,
        ZoneManager $zoneManager,
        array $params
    ) {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->massPersonRepository = $massPersonRepository;
        $this->fileManager = $fileManager;
        $this->logger = $logger;
        $this->params = $params;
        $this->validator = $validator;
        $this->geoSearcher = $geoSearcher;
        $this->geoRouter = $geoRouter;
        $this->zoneManager = $zoneManager;
    }

    /**
     * Get the user of the file.
     * @param Mass $mass
     * @throws OwnerNotFoundException
     * @return object
     */
    public function getUser(Mass $mass): object
    {
        if (!is_null($mass->getUserId())) {
            return $this->userRepository->find($mass->getUserId());
        }
        throw new OwnerNotFoundException('The owner of this file cannot be found');
    }

    /**
     * Generates a filename and removes the extension.
     * @param Mass $mass
     * @return string
     */
    public function generateFilename(Mass $mass)
    {
        $date = new \Datetime();
        return $this->fileManager->sanitize($date->format('YmdHis') . "-" . substr($mass->getFile()->getClientOriginalName(), 0, strrpos($mass->getFile()->getClientOriginalName(), ".")));
    }

    /**
     * Treat a mass file.
     *
     * @param Mass $mass The mass to treat
     * @return void
     */
    public function treatMass(Mass $mass)
    {
        // we get the validated data from the file
        $data = $this->getData($mass);

        if (count($data->getErrors()) > 0) {
            // the are errors in the file
            $mass->setErrors($data->getErrors());
        } else {
            // we import the persons
            $this->importPersons($data, $mass);
        }
        // we return the mass object
        return $mass;
    }

    /**
     * Analyze mass file data.
     *
     * @param Mass $mass The mass to analyze
     * @return void
     */
    public function analyzeMass(Mass $mass)
    {
        set_time_limit(120);
        // we search all destinations
        $destinations = $this->massPersonRepository->findAllDestinationsForMass($mass);
        // we geocode the destinations
        $geocodedDestinations = [];
        foreach ($destinations as $key => $destination) {
            $address = trim($destination['houseNumber'] . " " . $destination['street'] . " " . $destination['postalCode'] . " " . $destination['addressLocality'] . " " . $destination['addressCountry']);
            if ($addresses = $this->geoSearcher->geoCode($address)) {
                if (count($addresses) > 0) {
                    // we use the first result as best result
                    $geocodedDestinations[$key] = $addresses[0];
                } else {
                    throw new MassException('Destination address <' . $address . '> not found');
                }
            } else {
                throw new MassException('Destination address <' . $address . '> not found');
            }
            usleep(self::DELAY_BETWEEN_REQUESTS);
        }

        // we geocode the personal addresses
        foreach ($mass->getPersons() as $massPerson) {
            // maybe we already have the gps points
            if (
                !is_null($massPerson->getPersonalAddress()->getLongitude()) &&
                !is_null($massPerson->getPersonalAddress()->getLongitude()) &&
                !is_null($massPerson->getWorkAddress()->getLongitude()) &&
                !is_null($massPerson->getWorkAddress()->getLongitude())
            ) {
                continue;
            }
            // no gps points
            $address = trim(
                $massPerson->getPersonalAddress()->getHouseNumber() . " " .
                $massPerson->getPersonalAddress()->getStreet() . " " .
                $massPerson->getPersonalAddress()->getPostalCode() . " " .
                $massPerson->getPersonalAddress()->getAddressLocality() . " " .
                $massPerson->getPersonalAddress()->getAddressCountry()
            );
            if ($addresses = $this->geoSearcher->geoCode($address)) {
                if (count($addresses) > 0) {
                    // we use the first result as best result
                    $massPerson->getPersonalAddress()->setLongitude($addresses[0]->getLongitude());
                    $massPerson->getPersonalAddress()->setLatitude($addresses[0]->getLatitude());
                    // we search the destination (already calculated in the previous step)
                    foreach ($destinations as $key => $destination) {
                        if (
                            $destination['houseNumber'] == $massPerson->getWorkAddress()->getHouseNumber() &&
                            $destination['street'] == $massPerson->getWorkAddress()->getStreet() &&
                            $destination['postalCode'] == $massPerson->getWorkAddress()->getPostalCode() &&
                            $destination['addressLocality'] == $massPerson->getWorkAddress()->getAddressLocality() &&
                            $destination['addressCountry'] == $massPerson->getWorkAddress()->getAddressCountry()
                        ) {
                            $massPerson->getWorkAddress()->setLongitude($geocodedDestinations[$key]->getLongitude());
                            $massPerson->getWorkAddress()->setLatitude($geocodedDestinations[$key]->getLatitude());
                            break;
                        }
                    }
                    $this->entityManager->persist($massPerson);
                } else {
                    throw new MassException('Personal address <' . $address . '> not found');
                }
            } else {
                throw new MassException('Personal address <' . $address . '> not found');
            }
            usleep(self::DELAY_BETWEEN_REQUESTS);
        }
        $this->entityManager->flush();

        // all addresses are geocoded, we can get the directions
        foreach ($mass->getPersons() as $massPerson) {
            $addresses = [];
            $addresses[] = $massPerson->getPersonalAddress();
            $addresses[] = $massPerson->getWorkAddress();
            if ($routes = $this->geoRouter->getRoutes($addresses)) {
                $direction = $routes[0];
                // creation of the crossed zones (maybe useless as most persons will obviously share at least one zone)
                $direction = $this->zoneManager->createZonesForDirection($direction);
                $massPerson->setDirection($direction);
            } else {
                $origin = trim(
                    $massPerson->getPersonalAddress()->getHouseNumber() . " " .
                    $massPerson->getPersonalAddress()->getStreet() . " " .
                    $massPerson->getPersonalAddress()->getPostalCode() . " " .
                    $massPerson->getPersonalAddress()->getAddressLocality() . " " .
                    $massPerson->getPersonalAddress()->getAddressCountry()
                );
                $destination = trim(
                    $massPerson->getWorkAddress()->getHouseNumber() . " " .
                    $massPerson->getWorkAddress()->getStreet() . " " .
                    $massPerson->getWorkAddress()->getPostalCode() . " " .
                    $massPerson->getWorkAddress()->getAddressLocality() . " " .
                    $massPerson->getWorkAddress()->getAddressCountry()
                );
                throw new MassException('No route found for <' . $origin . '> => <' . $destination . '>');
            }
            $this->entityManager->persist($massPerson);
        }
        $mass->setStatus(Mass::STATUS_ANALYZED);
        $mass->setAnalyzeDate(new \Datetime());
        $this->entityManager->persist($mass);
        $this->entityManager->flush();
    }

    /**
     * Get The validated data from the file.
     *
     * @param Mass $mass
     * @return MassData
     */
    private function getData(Mass $mass)
    {
        // if it's a plain text we try to guess the real mimetype
        if ($mass->getMimeType() == self::MIMETYPE_PLAIN) {
            $mass->setMimeType($this->guessMimeType('.' . $this->params['folder'] . $mass->getFileName()));
        }
        switch ($mass->getMimeType()) {
            case self::MIMETYPE_ZIP:
                return $this->getDataFromZip('.' . $this->params['folder'] . $mass->getFileName());
                break;
            case self::MIMETYPE_APPLICATION_XML:
            case self::MIMETYPE_TEXT_XML:
                return $this->getDataFromXml('.' . $this->params['folder'] . $mass->getFileName());
                break;
            case self::MIMETYPE_CSV:
                return $this->getDataFromCsv('.' . $this->params['folder'] . $mass->getFileName());
                break;
                // case self::MIMETYPE_JSON:
                //     return $this->getDataFromJson('.' . $this->params['folder'] . $mass->getFileName());
                //     break;
            default:
                throw new MassException('This file type is not accepted');
                break;
        }
    }

    /**
     * Try to guess the real mimetype based on the extension of the file.
     *
     * @param string $filename  The file
     * @return string|null
     */
    private function guessMimeType(string $filename)
    {
        switch (strtolower($this->fileManager->getExtension($filename))) {
            case 'csv':
                return self::MIMETYPE_CSV;
                break;
            case 'xml':
                return self::MIMETYPE_APPLICATION_XML;
                break;
            case 'json':
                return self::MIMETYPE_JSON;
                break;
            default:
                throw new MassException('This file type is not accepted');
                break;
        }
    }

    /**
     * Get data from a zip file
     *
     * @param string $zip The filename
     * @return MassData
     */
    private function getDataFromZip(string $zip)
    {
        $zipArchive = new \ZipArchive();
        if ($zipArchive->open($zip) === true) {
            if ($zipArchive->numFiles > 1) {
                throw new MassException('Zip file can contain only a single file');
            }
            $filename = $zipArchive->getNameIndex(0);
            if ($zipArchive->extractTo('.' . $this->params['temp'], $filename)) {
                $mimeType = mime_content_type('.' . $this->params['temp'] . $filename);
                if ($mimeType == self::MIMETYPE_PLAIN) {
                    $mimeType = $this->guessMimeType('.' . $this->params['temp'] . $filename);
                }
                switch ($mimeType) {
                    case self::MIMETYPE_APPLICATION_XML:
                    case self::MIMETYPE_TEXT_XML:
                        return $this->getDataFromXml('.' . $this->params['temp'] . $filename, true);
                        break;
                    case self::MIMETYPE_CSV:
                        return $this->getDataFromCsv('.' . $this->params['temp'] . $filename, true);
                        break;
                        // case self::MIMETYPE_JSON:
                        //     return $this->getDataFromJson('.' . $this->params['temp'] . $filename, true);
                        //     break;
                    default:
                        throw new MassException('The extracted file type is not accepted');
                        break;
                }
            } else {
                throw new MassException('Cannot extract file');
            }
        } else {
            throw new MassException('Cannot open file');
        }

        return "test";
    }

    /**
     * Get data from a XML file
     *
     * @param string $xml   The filename
     * @param boolean $temp If the file is temporary
     * @return MassData
     */
    private function getDataFromXml(string $xml, $temp = false)
    {
        $error = false;
        $errors = false;

        // we try to validate the xml
        $validator = new DomValidator($this->params['xml_schema'], $xml);
        if (!$validator->validate()) {
            // errors in the xml
            $error = true;
            $errors = $validator->getErrors();
        }

        // we create a new MassData object to return
        $massData = new MassData();

        if (is_array($errors) && count($errors) > 0) {
            // we have errors in xml : we stop here
            $massData->setErrors($errors);
            // the file was temporary we remove it
            if ($temp) {
                unlink($xml);
            }
            return $massData;
        } elseif ($error) {
            // other error, we stop
            // the file was temporary we remove it
            if ($temp) {
                unlink($xml);
            }
            throw new MassException('Cannot open file');
        }

        // no errors, we parse the xml to get the persons
        $data = simplexml_load_file($xml);
        $persons = [];
        foreach ($data->person as $person) {
            $massPerson = new MassPerson();
            $massPerson->setGivenId($person->givenId);
            $massPerson->setGivenName($person->givenName);
            $massPerson->setFamilyName($person->familyName);
            $personalAddress = new Address();
            $personalAddress->setHouseNumber($person->personalAddress->houseNumber);
            $personalAddress->setStreet($person->personalAddress->street);
            $personalAddress->setPostalCode($person->personalAddress->postalCode);
            $personalAddress->setAddressLocality($person->personalAddress->addressLocality);
            $massPerson->setPersonalAddress($personalAddress);
            $workAddress = new Address();
            $workAddress->setHouseNumber($person->workAddress->houseNumber);
            $workAddress->setStreet($person->workAddress->street);
            $workAddress->setPostalCode($person->workAddress->postalCode);
            $workAddress->setAddressLocality($person->workAddress->addressLocality);
            $massPerson->setWorkAddress($workAddress);
            $massPerson->setOutwardTime($person->outwardTime);
            $massPerson->setReturnTime($person->returnTime);
            $persons[] = $massPerson;
        }
        $massData->setData($persons);

        // the file was temporary we remove it
        if ($temp) {
            unlink($xml);
        }

        return $massData;
    }

    /**
     * Get data from a CSV file
     *
     * @param string $csv   The filename
     * @param boolean $temp If the file is temporary
     * @return MassData
     */
    private function getDataFromCsv(string $csv, $temp = false)
    {
        $error = false;
        $errors = [];
        $fields = [];

        // we get the schema fields
        if ($file = fopen($this->params['csv_schema'], "a+")) {
            while ($tab = fgetcsv($file, 4096, ';')) {
                for ($i = 0; $i < count($tab); $i++) {
                    $fields[] = $tab[$i];
                }
            }
        }

        // we try to validate the whole file
        $persons = [];
        $line = 0;
        if ($file = fopen($csv, "a+")) {
            while ($tab = fgetcsv($file, 4096, ';')) {
                $line++;
                if (count($tab) <> count($fields)) {
                    $error = true;
                    $errors[] = [
                        'code' => '',
                        'file' => basename($csv),
                        'line' => $line,
                        'message' => 'Wrong number of fields'
                    ];
                }
                $massPerson = new MassPerson();
                $personalAddress = new Address();
                $workAddress = new Address();
                for ($i = 0; $i < count($tab); $i++) {
                    $setter = 'set' . ucwords($fields[$i]);
                    if (method_exists($massPerson, $setter)) {
                        $massPerson->$setter($tab[$i]);
                    } elseif (substr($fields[$i], 0, 16) == "personalAddress.") {
                        $setter = 'set' . ucwords(substr($fields[$i], 16));
                        if (method_exists($personalAddress, $setter)) {
                            $personalAddress->$setter($tab[$i]);
                        }
                    } elseif (substr($fields[$i], 0, 12) == "workAddress.") {
                        $setter = 'set' . ucwords(substr($fields[$i], 12));
                        if (method_exists($workAddress, $setter)) {
                            $workAddress->$setter($tab[$i]);
                        }
                    }
                }
                $massPerson->setPersonalAddress($personalAddress);
                $massPerson->setWorkAddress($workAddress);

                $validationErrors = $this->validator->validate($massPerson, null, ['mass']);
                if (count($validationErrors) > 0) {
                    foreach ($validationErrors as $validationError) {
                        $errors[] = [
                            'code' => '',
                            'file' => basename($csv),
                            'line' => $line,
                            'message' => (string)$validationError
                        ];
                    }
                } else {
                    $persons[] = $massPerson;
                }
            }

            // we create a new MassData object to return
            $massData = new MassData();

            if (count($errors) > 0) {
                // we have errors in xml : we stop here
                $massData->setErrors($errors);
                // the file was temporary we remove it
                if ($temp) {
                    unlink($csv);
                }
                return $massData;
            } elseif ($error) {
                // other error, we stop
                // the file was temporary we remove it
                if ($temp) {
                    unlink($csv);
                }
                throw new MassException('Cannot open file');
            }

            $massData->setData($persons);

            // the file was temporary we remove it
            if ($temp) {
                unlink($csv);
            }

            return $massData;
        } else {
            $errors = true;
        }

        // the file was temporary we remove it
        if ($temp) {
            unlink($csv);
        }

        if ($errors) {
            throw new MassException('Cannot open file');
        }
    }

    /**
     * Get data from a JSON file
     *
     * @param string $json  The filename
     * @param boolean $temp If the file is temporary
     * @return MassData
     */
    private function getDataFromJson(string $json, $temp = false)
    {
        $errors = false;
        // the file was temporary we remove it
        if ($temp) {
            unlink($json);
        }

        if ($errors) {
            throw new MassException('Cannot open file');
        }
    }

    /**
     * Import persons
     *
     * @param MassData  $data    The data to import
     * @param Mass      $mass    The parent Mass object
     * @return void
     */
    private function importPersons(MassData $data, Mass $mass)
    {
        // the data property of the MassData contains the MassPerson objects
        foreach ($data->getData() as $massPerson) {
            $massPerson->setMass($mass);
            $this->entityManager->persist($massPerson);
        }
        $this->entityManager->flush();
    }
}

/**
 * XML validator
 */
class DOMValidator
{
    protected $schema;
    public $errors;
    protected $xml;

    /**
     * Validation Class constructor Instantiating DOMDocument
     *
     * @param \DOMDocument $handler [description]
     */
    public function __construct($schema, $xml)
    {
        $this->handler = new \DOMDocument('1.0', 'utf-8');
        $this->schema = $schema;
        $this->xml = $xml;
    }

    /**
     * @param \libXMLError object $error
     *
     * @return array
     */
    private function libxmlGetError($error)
    {
        return [
            'code' => $error->code,
            'file' => basename($this->xml),
            'line' => $error->line,
            'message' => trim($error->message)
        ];
    }

    /**
     * @return array
     */
    private function libxmlGetErrors()
    {
        $errors = libxml_get_errors();
        $result    = [];
        foreach ($errors as $error) {
            $result[] = $this->libxmlGetError($error);
        }
        libxml_clear_errors();
        return $result;
    }

    /**
     * Validate Incoming Feeds against Listing Schema
     * @return bool
     *
     * @throws \Exception
     */
    public function validate()
    {
        if (!class_exists('DOMDocument')) {
            throw new MassException("'DOMDocument' class not found!");
            return false;
        }
        if (!file_exists($this->schema)) {
            throw new MassException('Schema is missing, please add schema to schema property');
            return false;
        }
        libxml_use_internal_errors(true);
        if (!($fp = fopen($this->xml, "r"))) {
            throw new MassException('Cannot open file');
        }

        $contents = fread($fp, filesize($this->xml));
        fclose($fp);

        $this->handler->loadXML($contents, LIBXML_NOBLANKS);
        if (!$this->handler->schemaValidate($this->schema)) {
            $this->errors = $this->libxmlGetErrors();
        } else {
            return true;
        }
    }

    /**
     * Get errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}