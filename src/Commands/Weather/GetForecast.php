<?php

namespace Bot\Commands\Weather;

use Discord\Parts\Interactions\Interaction;
use Bot\Builders\MessageBuilder;
use Bot\Events\Success;
use Bot\Models\Weather;
use Bot\Events\Error;
use Discord\Discord;
use Bot\SlashIndex;

class GetForecast
{
    public function getName(): string
    {
        return 'forecast';
    }

    public function getDescription(): string
    {
        return 'Get the forecast for the next 3 days';
    }

    public function getOptions(): array
    {
        return [
            [
                'name' => 'country',
                'description' => 'The country you want to get the weather of',
                'type' => 3,
                'required' => true
            ],
            [
                'name' => 'city',
                'description' => 'The city you want to get the weather of',
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
        $country = ucfirst($optionRepository['country']->value);
        $city = ucfirst($optionRepository['city']->value) ?? null;

        $weather = new Weather();
        $forecast = $weather->getForecast($country, $city);
        $location = $weather->getLocation($forecast);

        if ($forecast === null) {
            Error::sendError($interaction, $discord, 'Something went wrong while getting the forecast');
        }

        $builder = Success::sendSuccess($discord, 'Forecast', 'Here is the forecast for the next 3 days for ' . $location['city'] . ', ' . $location['country'], $interaction);
        $forecast = $forecast['forecast']['forecastday'];

        $embedFields = [];
        $avgTemp = 0;
        $avgHumidity = 0;

        foreach ($forecast as $day) {
            $embedFields[] = [
                'name' => $day['date'],
                'value' => "Max temp: {$day['day']['maxtemp_c']}°C\nMin temp: {$day['day']['mintemp_c']}°C\nAverage temp: {$day['day']['avgtemp_c']}°C\nMax wind speed: {$day['day']['maxwind_kph']}kph\nTotal precipitation: {$day['day']['totalprecip_mm']}mm\nAverage humidity: {$day['day']['avghumidity']}%\nCondition: {$day['day']['condition']['text']}",
                'inline' => false
            ];

            $avgTemp += $day['day']['avgtemp_c'];
            $avgHumidity += $day['day']['avghumidity'];
        }
        $builder->addField('Average temp (3 days)', $avgTemp / 3 . '°C', true);
        $builder->addField('Average humidity (3 days)', round($avgHumidity / 3, 2) . '%', true);
        $builder->addLineBreak();

        foreach ($forecast as $day) {
            $day['date'] = date('l \t\h\e jS \o\f F', strtotime($day['date']));
            $builder->addField('Condition for: ' . $day['date'], $day['day']['condition']['text'] . " " . $day['day']['avgtemp_c'] . '°C ' . $day['day']['totalprecip_mm'] . 'mm rain', false);
        }


        $messageBuilder = MessageBuilder::buildMessage($builder);
        $slashIndex = new SlashIndex($embedFields);
        $slashIndex->setTotalPerPage(1);
        $slashIndex->handlePagination(count($embedFields), $messageBuilder, $discord, $interaction, $builder);
    }


}