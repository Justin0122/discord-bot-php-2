<?php

namespace Bot\Commands\Spotify;

use Bot\Builders\InitialEmbed;
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

        InitialEmbed::Send($interaction, $discord, 'Please wait while we are fetching your current song');

        $pid = pcntl_fork();
        if ($pid == -1) {
            die('could not fork');
        } else if ($pid) {
            //parent
        } else {
            //child
            $this->getCurrentSong($user_id, $discord, $interaction);
        }


    }

    private function getCurrentSong($user_id, $discord, Interaction $interaction)
    {
        $spotify = new Spotify();
        $tracks = $spotify->getCurrentSong($user_id);
        $me = $spotify->getMe($user_id);

        if (!isset($tracks->item->name)){
            Error::sendError($interaction, $discord, 'You are not listening to any song', true);
            return;
        }

        $builder = new EmbedBuilder($discord);
        $builder->setTitle('Sharing current song');
        $builder->setDescription('Sharing the song you are currently listening to');
        $builder->setSuccess();
        $builder->addField('Song', $tracks->item->name, true);



        $builder->addField('Artist', $tracks->item->artists[0]->name, true);
        $builder->addField('Album', $tracks->item->album->name, true);
        $builder->addField('Duration', gmdate("i:s", $tracks->item->duration_ms / 1000), true);
        $builder->setSuccess();

        $builder->setThumbnail($tracks->item->album->images[0]->url);

        $builder->setUrl($tracks->item->external_urls->spotify);

        $interaction->updateOriginalResponse(\Bot\Builders\MessageBuilder::buildMessage($builder));

    }


}