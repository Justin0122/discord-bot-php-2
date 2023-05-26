<?php

namespace Bot\Commands\Spotify;

use Bot\Builders\InitialEmbed;
use Bot\Events\Error;
use DateTime;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;
use Bot\Builders\EmbedBuilder;
use Bot\Models\Spotify;
use Bot\Builders\ButtonBuilder;
use Bot\Builders\MessageBuilder;

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

        if ($startDate && new DateTime($startDate) < new DateTime('2015-01-01')) {
            Error::sendError($interaction, $discord, 'Start date cannot be before 2015');
            return;
        }

        $startDateString = $startDate ?? null;
        $dates = $this->calculateMonthRange($startDateString);
        $startDate = $dates['startDate'];
        $endDate = $dates['endDate'];

        $playlistTitle = 'Liked Songs of ' . $startDate->format('M Y') .'.';


        InitialEmbed::Send($interaction, $discord, 'Generating playlist with title: ' . $playlistTitle);

        $pid = pcntl_fork();
        if ($pid == -1) {
            die('could not fork');
        } else if ($pid) {
            //parent
        } else {
            //child
            $this->generatePlaylist($user_id, $startDate, $endDate, $public, $discord, $interaction, $playlistTitle);
        }
    }

    private function generatePlaylist($user_id, $startDate, $endDate, $public, $discord, $interaction, $playlistTitle): void
    {
        $spotify = new Spotify();
        $playlist = $spotify->generatePlaylist($user_id, $startDate, $endDate, $public, $discord, $interaction);

        if ($playlist) {
            echo $playlist[0] . PHP_EOL;
            $builder = new EmbedBuilder($discord);
            $builder->setTitle('Playlist generated');
            $builder->setDescription('Playlist generated with title: ' . $playlistTitle);
            $builder->setSuccess();
            $button = ButtonBuilder::addLinkButton('Open playlist', $playlist[0]);

            $messageBuilder = MessageBuilder::buildMessage($builder, [$button[0]]);
            $interaction->updateOriginalResponse($messageBuilder);
        }
        else{

            Error::sendError($interaction, $discord, 'Something went wrong while generating the playlist');
        }
    }

    function calculateMonthRange($startDateString = null): array
    {
        $startDate = $startDateString ? new DateTime($startDateString) : new DateTime();
        $currentMonth = $startDate->format('m');
        $currentYear = $startDate->format('Y');
        $startDate->setDate($currentYear, $currentMonth, 1);
        $endDate = clone $startDate;
        $endDate->modify('+1 month');
        $endDate->modify('-1 day');

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }

}