<?php

namespace AppBundle\Utils;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ConfluenceApiService
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getAllDocumentsByCurrentUser(string $author)
    {
        $loginCredentials = $this->getContainer()->getParameter('credentials');
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://confluence.virgo.hu/rest/api/content/search?cql=creator=' . $author);
        curl_setopt($curl, CURLOPT_USERPWD, $loginCredentials);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);
        curl_close($curl);
        return $this->parseRespone($result);
    }

    private function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    private function parseRespone(string $result)
    {
        $json = json_decode($result, true);
        $result= [];
        foreach ($json['results'] as $document) {
            if ($document['type'] !== 'attachment') {
                $result[] = $document;
            }
        }
        return $result;
    }
}
