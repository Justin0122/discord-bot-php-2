<?php

namespace Bot\Commands\Spotify;

use Bot\Helpers\SessionHandler;
use Bot\Helpers\TokenHandler;
use Bot\SlashIndex;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;
use Bot\Builders\EmbedBuilder;

class GetLatestSongs
{
    public function getName(): string
    {
        return 'getlatestsong';
    }

    public function getDescription(): string
    {
        return 'Get the latest song from your liked songs';
    }

    public function getOptions(): array
    {
        return [];
    }

    public function getGuildId(): ?string
    {
        return null;
    }

    public function handle(Interaction $interaction, Discord $discord, $user_id )
    {
        $tokenHandler = new TokenHandler($_ENV['API_URL'], $_ENV['SECURE_TOKEN']);
        $tokens = $tokenHandler->getTokens($user_id);

        if (!$tokens) {
            return [];
        }

        //set the api using sessionHandler
        $api = (new SessionHandler())->setSession($user_id);

        //get the latest 10 songs
        $tracks = $api->getMySavedTracks([
            'limit' => 10
        ]);

        $me = $api->me();

        foreach ($tracks->items as $item) {
            $track = $item->track;
            $embed['fields'][] = [
                'name' => $track->name,
                'value' => 'Link: [Click Here](' . $track->external_urls->spotify . ') | Artist: ' . $track->artists[0]->name,
                'inline' => false,
            ];
        }
        $builder = new EmbedBuilder($discord);
        $builder->setTitle('Latest songs added by ' . $me->display_name);
        $builder->setDescription("Here are your latest songs");
        $builder->setSuccess();

        foreach ($embed['fields'] as $field) {
            $builder->addField($field['name'], $field['value'], $field['inline']);
        }

        $messageBuilder = new MessageBuilder();
        $messageBuilder->addEmbed($builder->build());

        $interaction->respondWithMessage($messageBuilder);
    }

}