<?php

namespace Bot\Models;

use Discord\Parts\Interactions\Interaction;
use Bot\Helpers\SessionHandler;
use Bot\Helpers\TokenHandler;
use Discord\Discord;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Weather
{

    public function __construct()
    {

    }

    public static function getWeather($country, $city): object|array|null
    {
        $apiUrl = $_ENV['WEATHER_API_URL'];
        $apiKey = $_ENV['WEATHER_API_KEY'];

        $link = "$apiUrl?key=$apiKey&q=$city,$country";

        $client = new Client();

        try {
            $response = $client->request('GET', $link);
            $response = json_decode($response->getBody(), true);
        } catch (\GuzzleHttp\Exception\ClientException|GuzzleException $e) {
            return null;
        }


        return $response;
    }
}