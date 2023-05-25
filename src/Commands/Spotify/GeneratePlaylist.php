<?php

namespace Bot\Commands\Spotify;

use Bot\Events\Error;
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
            ],
            [
                'name' => 'public',
                'description' => 'Make playlist public',
                'type' => 5,
                'required' => false
            ]
        ];
    }

    public function getGuildId(): ?string
    {
        return null;
    }

    /**
     * @throws \Exception
     */

    public function handle(Interaction $interaction, Discord $discord, $user_id): void
    {
        $optionRepository = $interaction->data->options;
        $startDate = $optionRepository['startdate']->value;
        $public = $optionRepository['public']->value ?? false;

        if ($startDate && new DateTime($startDate) > new DateTime()) {
            Error::sendError($interaction, $discord, 'Start date cannot be in the future');
            return;
        }

        $startDate = $startDate ? new DateTime($startDate) : (new DateTime())->modify('-1 month');
        $endDate = clone $startDate;
        $endDate->modify('+1 month');

        $builder = new EmbedBuilder($discord);
        $builder->setTitle('Generating playlist');
        $playlistTitle = 'Liked Songs of ' . $startDate->format('M Y') .'.';
        $builder->setDescription('Generating playlist with title: ' . $playlistTitle);
        $builder->setInfo();

        $messageBuilder = new MessageBuilder();
        $messageBuilder->addEmbed($builder->build());
        $interaction->respondWithMessage($messageBuilder, false);

        $pid = pcntl_fork();
        if ($pid == -1) {
            die('could not fork');
        } else if ($pid) {
            //parent
        } else {
            //child
            $spotify = new Spotify();
            $spotify->generatePlaylist($user_id, $startDate, $endDate, $public);

            $builder = new EmbedBuilder($discord);
            $builder->setTitle($playlistTitle . ' generated');
            $builder->setDescription('[Click here to open the playlist](https://open.spotify.com/playlist/' . $spotify->getPlaylistIdByName($user_id, $playlistTitle) . ')' );
            $builder->setSuccess();

            $messageBuilder = new MessageBuilder();
            $messageBuilder->addEmbed($builder->build());
            $interaction->updateOriginalResponse($messageBuilder);
        }

    }
}