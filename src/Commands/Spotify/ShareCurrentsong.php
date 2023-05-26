<?php

namespace Bot\Commands\Spotify;

use Bot\SlashIndex;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;
use Bot\Builders\EmbedBuilder;
use Bot\Models\Spotify;
use Bot\Events\Error;

class ShareCurrentsong
{
    public function getName(): string
    {
        return 'sharecurrentsong';
    }

    public function getDescription(): string
    {
        return 'Share the song you are currently listening to';
    }

    public function getOptions(): array
    {
        return [];
    }

    public function getGuildId(): ?string
    {
        return null;
    }

    public function handle(Interaction $interaction, Discord $discord, $user_id): void
    {
        $spotify = new Spotify();
        $tracks = $spotify->getCurrentSong($user_id);
        $me = $spotify->getMe($user_id);

        $builder = new EmbedBuilder($discord);
        $builder->setTitle('Sharing current song');
        $builder->setDescription('[ ' . $tracks->item->name . ' ](' . $tracks->item->external_urls->spotify . ') ' . PHP_EOL . 'By: ' . $tracks->item->artists[0]->name);
        $builder->setAuthor($me->display_name, '', $me->external_urls->spotify);

        $builder->setSuccess();

        $messageBuilder = new MessageBuilder();
        $messageBuilder->addEmbed($builder->build());

        $embedFields = [];
        $slashIndex = new SlashIndex($embedFields);

        if (!isset($tracks->item->name)){
            Error::sendError($interaction, $discord, 'You are not listening to any song');
            return;
        }
        $slashIndex->handlePagination(count($embedFields), $messageBuilder, $discord, $interaction, $builder);
    }


}