<?php

namespace Bot\Commands\Spotify;

use Discord\Parts\Interactions\Interaction;
use Bot\Builders\MessageBuilder;
use Bot\Builders\ButtonBuilder;
use Bot\Builders\InitialEmbed;
use Bot\Models\Spotify;
use Bot\Events\Success;
use Bot\Events\Error;
use Discord\Discord;
use DateTime;

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

    /**
     * @throws \Exception
     */
    private function generatePlaylist($user_id, $startDate, $endDate, $public, $discord, $interaction, $playlistTitle): void
    {
        $spotify = new Spotify();
        $playlist = $spotify->generatePlaylist($user_id, $startDate, $endDate, $public);

        if ($playlist) {
            echo $playlist[0] . PHP_EOL;
            $builder = Success::sendSuccess($discord, 'Playlist generated', 'Playlist generated with title: ' . $playlistTitle);
            $builder->setUrl($playlist[0]);
            $button = ButtonBuilder::addLinkButton('Open playlist', $playlist[0]);

            $messageBuilder = MessageBuilder::buildMessage($builder, [$button[0]]);
            $interaction->updateOriginalResponse($messageBuilder);
        }
        else{
            Error::sendError($interaction, $discord, 'Something went wrong while generating the playlist');
        }
    }

    /**
     * @throws \Exception
     */
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