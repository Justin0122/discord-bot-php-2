<?php

namespace Bot\Models;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class Weather
{

    private string $apiUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->apiUrl = $_ENV['WEATHER_API_URL'];
        $this->apiKey = $_ENV['WEATHER_API_KEY'];
    }

    public function getWeather($country, $city = null)
    {
        $apiUrl = $this->apiUrl;
        $apiKey = $this->apiKey;

        $link = "$apiUrl/current.json?key=$apiKey&q=$city,$country";

        $client = new Client();

        try {
            $response = $client->request('GET', $link);
            $response = json_decode($response->getBody(), true);
        } catch (ClientException|GuzzleException $e) {
            return null;
        }
        return $response;
    }

    public function getForecast($country, $city = null)
    {
        $apiUrl = $this->apiUrl;
        $apiKey = $this->apiKey;

        $link = "$apiUrl/forecast.json?key=$apiKey&q=$city,$country&days=3";

        $client = new Client();

        try {
            $response = $client->request('GET', $link);
            $response = json_decode($response->getBody(), true);
        } catch (ClientException|GuzzleException $e) {
            return null;
        }
        return $response;
    }

    public function getAstro(string $country, ?string $city)
    {
        $apiUrl = $this->apiUrl;
        $apiKey = $this->apiKey;

        $link = "$apiUrl/astronomy.json?key=$apiKey&q=$city,$country";

        $client = new Client();

        try {
            $response = $client->request('GET', $link);
            $response = json_decode($response->getBody(), true);
        } catch (ClientException|GuzzleException $e) {
            return null;
        }
        return $response;
    }

    public function getLocation(array $response)
    {
        $location = $response['location'];
        $country = $location['country'];
        $city = $location['name'];
        $region = $location['region'];

        return [
            'country' => $country,
            'city' => $city,
            'region' => $region
        ];

    }


}