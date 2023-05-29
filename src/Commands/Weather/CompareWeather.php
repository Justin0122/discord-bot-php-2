<?php

namespace Bot\Commands\Weather;

use Bot\Builders\MessageBuilder;
use Bot\Events\Error;
use Discord\Parts\Interactions\Interaction;
use Discord\Discord;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class CompareWeather
{
    public function getName(): string
    {
        return 'compareweather';
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
                'name' => 'country2',
                'description' => 'The country you want to get the weather from',
                'type' => 3,
                'required' => true
            ],
            [
                'name' => 'city',
                'description' => 'The city you want to get the weather from (defaults to capital city)',
                'type' => 3,
                'required' => false
            ],

            [
                'name' => 'city2',
                'description' => 'The city you want to get the weather from (defaults to capital city)',
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

    }
}