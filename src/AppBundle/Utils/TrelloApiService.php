<?php

namespace AppBundle\Utils;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Trello\Client;

class TrelloApiService
{
    private $container;

    /**
     * TrelloApiService constructor.
     * @param $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function getBoard()
    {
        $token = $this->container->getParameter('trello_token');
        $secret = $this->container->getParameter('trello_secret');
        $client = new Client();
        $client->authenticate($token, $secret, Client::AUTH_URL_CLIENT_ID);

        $boards = $client->api('board')->cards()->all('2qMgPhxH');
        $returned = $this->sortByLabel($boards);
        return $returned;
    }

    public function sortByLabel(array $cards): array
    {
        $done = [];
        foreach ($cards as $card) {
            foreach ($card['labels'] as $label) {
                if ($label['name'] === "Done") {
                    $done[] = $card;
                    unset($cards[array_search($card, $cards)]);
                }
            }
        }
        $returned['done'] = $done;
        $returned['todo'] = $cards;

        return $returned;
    }
}
