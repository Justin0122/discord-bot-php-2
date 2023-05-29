<?php

namespace Bot\Commands\Weather;

use Bot\Builders\MessageBuilder;
use Bot\Events\Error;
use Bot\Events\Success;
use Discord\Parts\Interactions\Interaction;
use Discord\Discord;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class GetCurrentWeather
{
    public function getName(): string
    {
        return 'getcurrentweather';
    }

    public function getDescription(): string
    {
        return 'Get the current weather';
    }

    public function getOptions(): array
    {
        return [
            [
                'name' => 'country',
                'description' => 'The country you want to get the weather from',
                'type' => 3,
                'required' => true
            ],
            [
                'name' => 'city',
                'description' => 'The city you want to get the weather from',
                'type' => 3,
                'required' => false
            ]
        ];
    }

    public function getGuildId(): ?string
    {
        return null;
    }

    /**
     * @throws GuzzleException
     */
    public function handle(Interaction $interaction, Discord $discord): void
    {

        //use http://api.weatherapi.com/v1/current.json?key=YOUR_API_KEY&q= for the api
        $optionRepository = $interaction->data->options;
        $country = $optionRepository['country'];
        $city = $optionRepository['city'];

        $country = $country->value;
        $city = $city->value;

        $apiUrl = $_ENV['WEATHER_API_URL'];
        $apiKey = $_ENV['WEATHER_API_KEY'];

        $link = "$apiUrl?key=$apiKey&q=$city,$country";

        $client = new Client();

        try {
            $response = $client->request('GET', $link);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Error::sendError($interaction, $discord, 'Invalid country or city');
            return;
        }

        //get the data from the response
        $response = json_decode($response->getBody(), true);

        //get the current weather
        $currentWeather = $response['current'];

        $location = $response['location'];
        $condition = $currentWeather['condition'];
        $wind = $currentWeather['wind_kph'];
        $humidity = $currentWeather['humidity'];
        $temperature = $currentWeather['temp_c'];
        $feelsLike = $currentWeather['feelslike_c'];
        $image = $condition['icon'];
        echo $image;
        //remove the "//cdn.weatherapi.com/weather/" part from the image url
        $image = str_replace('//cdn.weatherapi.com/weather/', '', $image);
        //get the image from /src/media/weather
        $image = file_get_contents(__DIR__ . '/../../media/weather/' . $image);

        $builder = Success::sendSuccess($discord, 'Current weather', 'Here is the current weather for ' . $location['name'] . ', ' . $location['country']);

        $builder->addField('Condition', $condition['text'], true);
        $builder->addField('Wind', $wind . ' kph', true);
        $builder->addField('Humidity', $humidity . '%', true);
        $builder->addField('Temperature', $temperature . '°C', true);
        $builder->addField('Feels like', $feelsLike . '°C', true);

        $builder->setImage('attachment://weather.png');

        $messageBuilder = MessageBuilder::buildMessage($builder);

        $interaction->respondWithMessage($messageBuilder, true);

    }
}