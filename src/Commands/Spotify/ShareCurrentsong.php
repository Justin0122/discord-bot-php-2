<?php

namespace Bot\Commands\Spotify;

use Discord\Parts\Interactions\Interaction;
use Bot\Builders\ButtonBuilder;
use Bot\Builders\MessageBuilder;
use Bot\Events\ButtonListener;
use Bot\Builders\InitialEmbed;
use Bot\Events\Success;
use Bot\Models\Spotify;
use Bot\Events\Error;
use Discord\Discord;

class ShareCurrentsong
{
    public function getName(): string
    {
        return 'currentsong';
    }

    public function getDescription(): string
    {
        return 'Share the song you are currently listening to';
    }

    public function getOptions(): array
    {
        return [
            [
                'name' => 'ephemeral',
                'description' => 'Send the message only to you',
                'type' => 5,
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

    private function getCurrentSong($user_id, $discord, Interaction $interaction): void
    {
        $optionRepository = $interaction->data->options;
        $ephemeral = $optionRepository['ephemeral']->value ?? false;
        $spotify = new Spotify();
        $tracks = $spotify->getCurrentSong($user_id);
        $me = $spotify->getMe($user_id);

        if (!isset($tracks->item)) {
            Error::sendError($interaction, $discord, 'You are not listening to any song', true);
            return;
        }

        $builder = Success::sendSuccess($discord, $me->display_name . ' is listening to:');

        $builder->addField('Song', $tracks->item->name, true);
        $builder->addField('Artist', $tracks->item->artists[0]->name, true);
        $builder->addField('Album', $tracks->item->album->name, true);
        $builder->addField('Duration', gmdate("i:s", $tracks->item->duration_ms / 1000), true);

        $button = ButtonBuilder::addPrimaryButton('Like', 'spotify:track:' . $tracks->item->id);

        $builder->setThumbnail($tracks->item->album->images[0]->url);

        $builder->setUrl($tracks->item->external_urls->spotify);

        $messageBuilder = new \Discord\Builders\MessageBuilder();
        $messageBuilder->addEmbed($builder->build());
        $messageBuilder = MessageBuilder::buildMessage($builder, [$button[0]]);

        $interaction->sendFollowUpMessage($messageBuilder, $ephemeral);
        $interaction->deleteOriginalResponse();

//        ButtonListener::listener($discord, $button[1], 'Pong!', 'Button Clicked!');

    }


}