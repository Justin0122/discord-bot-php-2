<?php

namespace Bot\Commands\Spotify;

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
        return 'getplaylists';
    }

    public function getDescription(): string
    {
        return 'Get your playlists';
    }

    public function getOptions(): array
    {
        return [
            [
                'name' => 'amount',
                'description' => 'amount of playlists',
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
        $amount = $optionRepository['amount']->value ?? 6;

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

    private function getPlaylistsFromUser($user_id, $amount, $discord, $interaction): void
    {
        $spotify = new Spotify();
        $playlists = $spotify->getPlaylists($user_id, $amount);
        var_dump($playlists);

        if (!$playlists) {
            Error::sendError($interaction, $discord, 'Something went wrong', true);
            return;
        }


        $me = $spotify->getMe($user_id);
        echo $me->display_name . PHP_EOL;

        $embedFields = [];
        foreach ($playlists as $playlist) {
            echo $playlist->name . PHP_EOL;
            $embedFields[] = [
                'name' => $playlist->name,
                'value' => 'Total tracks: ' . $playlist->tracks->total,
                'inline' => false
            ];
        }

        $builder = Success::sendSuccess($discord, 'Playlists of ' . $me->display_name, 'Total playlists: ' . count($playlists));

        $messageBuilder = MessageBuilder::buildMessage($builder);
        $slashIndex = new SlashIndex($embedFields);
        $slashIndex->handlePagination(count($embedFields), $messageBuilder, $discord, $interaction, $builder, true);
    }


}