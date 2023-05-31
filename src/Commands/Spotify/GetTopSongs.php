<?php

namespace Bot\Commands\Spotify;

use Bot\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;
use Bot\Builders\InitialEmbed;
use Bot\Models\Spotify;
use Bot\Events\Success;
use Bot\Events\Error;
use Discord\Discord;
use Bot\SlashIndex;

class GetTopSongs
{
    public function getName(): string
    {
        return 'topsongs';
    }

    public function getDescription(): string
    {
        return 'Get the top songs from your liked songs';
    }

    public function getOptions(): array
    {
        return [
            [
                'name' => 'amount',
                'description' => 'amount of songs (default 24 max 50)',
                'type' => 4,
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
        InitialEmbed::send($interaction, $discord, 'Please wait while we are fetching your top songs');

        $pid = pcntl_fork();
        if ($pid == -1) {
            die('could not fork');
        } else if ($pid) {
            //parent
        } else {
            //child
            $this->getTopSongs($user_id, $discord, $interaction);
        }

    }

    private function getTopSongs($user_id, $discord, Interaction $interaction): void
    {

        $optionRepository = $interaction->data->options;
        $amount = $optionRepository['amount']->value ?? 24;

        $spotify = new Spotify();
        $tracks = $spotify->getTopSongs($user_id, $amount);
        $me = $spotify->getMe($user_id);

        if ($tracks === null) {
            Error::sendError($interaction, $discord, 'You have no liked songs');
        }

        $embedFields = [];
        foreach ($tracks->items as $item) {
            $track = $item;
            $embedFields[] = [
                'name' => $track->name,
                'value' => '[Song link](' . $track->external_urls->spotify . ') ' . PHP_EOL . 'Artist: ' . $track->artists[0]->name,
                'inline' => true,
            ];
        }

        $builder = Success::sendSuccess($discord, 'Your top songs', 'Your top songs from ' . $me->display_name . PHP_EOL . 'Amount: ' . $amount);

        $messageBuilder = MessageBuilder::buildMessage($builder);
        $slashIndex = new SlashIndex($embedFields);
        $slashIndex->handlePagination(count($embedFields), $messageBuilder, $discord, $interaction, $builder, true);

    }


}