<?php

namespace Bot\Commands\Spotify;

use DateTime;
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
                'required' => false
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

        $startDate = $startDate ? new DateTime($startDate) : (new DateTime())->modify('-1 month');
        $endDate = clone $startDate;
        $endDate->modify('+1 month');

        echo $startDate->format('Y-m-d') . PHP_EOL;
        echo $endDate->format('Y-m-d') . PHP_EOL;

        $builder = new EmbedBuilder($discord);
        $builder->setTitle('Generating playlist');
        $playlistTitle = 'Liked Songs of ' . $startDate->format('M Y') .'.';
        $builder->setDescription('Generating playlist with title: ' . $playlistTitle);
        $builder->setInfo();

        $messageBuilder = new MessageBuilder();
        $messageBuilder->addEmbed($builder->build());
        $interaction->respondWithMessage($messageBuilder, true);

        //run makePlaylist in the background as fork process
        $pid = pcntl_fork();
        if ($pid == -1) {
            die('could not fork');
        } else if ($pid) {
            //parent
        } else {
            //child
            $spotify = new Spotify();
            $spotify->generatePlaylist($user_id, $startDate, $endDate);
        }

    }
}