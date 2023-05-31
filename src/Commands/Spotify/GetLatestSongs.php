<?php

namespace Bot\Commands\Spotify;

use Discord\Parts\Interactions\Interaction;
use Bot\Builders\MessageBuilder;
use Bot\Models\Spotify;
use Bot\Events\Success;
use Bot\Events\Error;
use Discord\Discord;
use Bot\SlashIndex;

class GetLatestSongs
{
    public function getName(): string
    {
        return 'latestsongs';
    }

    public function getDescription(): string
    {
        return 'Get the latest song from your liked songs';
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
        $optionRepository = $interaction->data->options;
        $amount = $optionRepository['amount']->value ?? 24;

        $spotify = new Spotify();
        $tracks = $spotify->getLatestSongs($user_id, $amount);

        if ($tracks === null) {
            Error::sendError($interaction, $discord, 'You have no liked songs');
        }

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

        $builder = Success::sendSuccess($discord, 'Your latest songs', 'Your latest songs from ' . $me->display_name . PHP_EOL . 'Amount: ' . $amount);

        $messageBuilder = MessageBuilder::buildMessage($builder);
        $slashIndex = new SlashIndex($embedFields);
        $slashIndex->handlePagination(count($embedFields), $messageBuilder, $discord, $interaction, $builder);
    }


}