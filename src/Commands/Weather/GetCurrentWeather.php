<?php

namespace Bot\Commands\Weather;

use Bot\Builders\MessageBuilder;
use Bot\Events\Error;
use Bot\Events\Success;
use Bot\Models\Weather;
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

    public function handle(Interaction $interaction, Discord $discord): void
    {

        $optionRepository = $interaction->data->options;
        $country = $optionRepository['country'];
        $city = $optionRepository['city'];

        $country = $country->value;
        $country = ucfirst($country);
        $city = $city->value;

        $currentWeather = Weather::getWeather($country, $city);

        print_r($currentWeather);

        $location = $currentWeather['location']['name'];
        $currentWeather = $currentWeather['current'];
        $image = $currentWeather['condition']['icon'];

        $builder = Success::sendSuccess($discord, 'Current weather', 'Here is the current weather for ' . $location . ', ' . $country);
        $builder->addField('Condition', $currentWeather['condition']['text'], true);
        $builder->addField('Temperature', $currentWeather['temp_c'] . '°C', true);
        $builder->addField('Feels like', $currentWeather['feelslike_c'] . '°C', true);
        $builder->addField('Wind', $currentWeather['wind_kph'] . 'km/h', true);
        $builder->addField('Humidity', $currentWeather['humidity'] . '%', true);

        $builder->setThumbnail('https:' . $image);

        $messageBuilder = MessageBuilder::buildMessage($builder);

        $interaction->respondWithMessage($messageBuilder);
    }
}