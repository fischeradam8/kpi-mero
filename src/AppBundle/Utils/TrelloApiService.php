<?php

namespace AppBundle\Utils;

use Trello\Client;

class TrelloApiService
{
    public function getBoard(): array
    {
        $client = new Client();
        $client->authenticate('3911de295390be89d010307f4c2482af', null, Client::AUTH_URL_CLIENT_ID);

        $boards = $client->api('member')->boards()->all('me');
        return $boards;
    }
}