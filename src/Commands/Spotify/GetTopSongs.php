<?php

namespace Bot\Commands\Spotify;

use Bot\SlashIndex;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;
use Bot\Builders\EmbedBuilder;
use Bot\Models\Spotify;
use Bot\Events\Error;

class GetTopSongs
{
    public function getName(): string
    {
        return 'gettopsongs';
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
                'description' => 'amount of songs (max 24)',
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

        $builder = new EmbedBuilder($discord);
        $builder->setTitle('The last ' . $amount . ' songs ' . $me->display_name . ' liked');
        $builder->setDescription("Here are your top songs");
        $builder->setSuccess();

        $messageBuilder = new MessageBuilder();
        $messageBuilder->addEmbed($builder->build());

        $slashIndex = new SlashIndex($embedFields);
        $slashIndex->handlePagination(count($embedFields), $messageBuilder, $discord, $interaction, $builder);
    }


}