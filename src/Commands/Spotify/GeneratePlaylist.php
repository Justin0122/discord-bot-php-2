<?php

namespace Bot\Commands\Spotify;

use Bot\SlashIndex;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;
use Bot\Builders\EmbedBuilder;
use Bot\Models\Spotify;

class GeneratePlaylist
{
    public function getName(): string
    {
        return 'generateplaylist';
    }

    public function getDescription(): string
    {
        return 'Generate a playlist from within a time frame';
    }

    public function getOptions(): array
    {
        return [
            [
                'name' => 'startdate',
                'description' => 'Start date',
                'type' => 3,
                'required' => true
            ],
            [
                'name' => 'enddate',
                'description' => 'End date',
                'type' => 3,
                'required' => true
            ]
        ];
    }

    public function getGuildId(): ?string
    {
        return null;
    }

    public function handle(Interaction $interaction, Discord $discord, $user_id): void
    {
        $optionRepository = $interaction->data->options;
        $startDate = $optionRepository['startdate']->value;
        $endDate = $optionRepository['enddate']->value;

        $spotify = new Spotify();
        $tracks = $spotify->generatePlaylist($user_id, $startDate, $endDate);
        $me = $spotify->getMe($user_id);

        $embedFields = [];
        foreach ($tracks->items as $item) {
            $track = $item->track;
            $embedFields[] = [
                'name' => $track->name,
                'value' => '[Song link](' . $track->external_urls->spotify . ') ' . PHP_EOL . 'Artist: ' . $track->artists[0]->name,
                'inline' => true,
            ];
        }

        $builder = new EmbedBuilder($discord);
        $builder->setTitle('Playlist generated');
        $builder->setDescription('Playlist generated from ' . $startDate . ' to ' . $endDate);


        $messageBuilder = new MessageBuilder();
        $messageBuilder->addEmbed($builder->build());

        $slashIndex = new SlashIndex($embedFields);
        $slashIndex->handlePagination(count($embedFields), $messageBuilder, $discord, $interaction, $builder);
    }


}