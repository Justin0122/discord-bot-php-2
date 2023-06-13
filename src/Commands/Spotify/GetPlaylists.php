<?php

namespace Bot\Commands\Spotify;

use Bot\Builders\ButtonBuilder;
use Discord\Builders\Components\ActionRow;
use Discord\Parts\Interactions\Interaction;
use Bot\Builders\MessageBuilder;
use Bot\Builders\InitialEmbed;
use Bot\Events\Success;
use Bot\Models\Spotify;
use Bot\Events\Error;
use Discord\Discord;
use Bot\SlashIndex;

class GetPlaylists
{
    public function getName(): string
    {
        return 'playlists';
    }

    public function getDescription(): string
    {
        return 'Get your playlists';
    }

    public function getOptions(): array
    {
        return [];
    }

    public function getGuildId(): ?string
    {
        return null;
    }

    public function getCooldown(): ?int
    {
        return 60;
    }

    public function handle(Interaction $interaction, Discord $discord, $user_id): void
    {
        $amount = 6;

        InitialEmbed::Send($interaction, $discord, 'Please wait while we are fetching your playlists');


        $pid = pcntl_fork();
        if ($pid == -1) {
            die('could not fork');
        } else if ($pid) {
            //parent
        } else {
            //child
            $this->getPlaylistsFromUser($user_id, $amount, $discord, $interaction);
        }

    }

    private function getPlaylistsFromUser($user_id, $amount, $discord, Interaction $interaction): void
    {
        $spotify = new Spotify();
        $playlists = $spotify->getPlaylists($user_id, $amount);

        if (!$playlists) {
            Error::sendError($interaction, $discord, 'Something went wrong', true);
            return;
        }

        $me = $spotify->getMe($user_id);

        $embedFields = [];
        foreach ($playlists as $playlist) {
            $embedFields[] = [
                'name' => $playlist->name,
                'value' => 'Total tracks: ' . $playlist->tracks->total,
                'inline' => false
            ];
        }

        $builder = Success::sendSuccess($discord, 'Playlists of ' . $me->display_name, 'Total playlists: ' . count($playlists), $interaction);
        $builder->setUrl($me->external_urls->spotify);
        $builder->setThumbnail($me->images[0]->url);

        $actionRow = ActionRow::new();
        ButtonBuilder::addLinkButton($actionRow, 'Open profile', $me->external_urls->spotify);

        foreach ($embedFields as $embedField) {
            $builder->addField($embedField['name'], $embedField['value'], $embedField['inline']);
        }
        $messageBuilder = MessageBuilder::buildMessage($builder, [$actionRow]);

        $interaction->updateOriginalResponse($messageBuilder);

    }
}