<?php

namespace Bot\Commands\Spotify;

use Bot\Builders\InitialEmbed;
use Bot\Builders\MessageBuilder;
use Bot\SlashIndex;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;
use Bot\Builders\EmbedBuilder;
use Bot\Models\Spotify;
use Bot\Events\Error;

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
        $amount = $optionRepository['amount']->value ?? 24;

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
        echo "Fetching playlists...\n";
        $spotify = new Spotify();
        $playlists = $spotify->getPlaylists($user_id, $amount);

        if ($playlists === null) {
            Error::sendError($interaction, $discord, 'You have no playlists');
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

        $builder = new EmbedBuilder($discord);
        $builder->setTitle('Playlists of ' . $me->display_name);
        $builder->setDescription("Here are your playlists");
        $builder->setSuccess();

        $messageBuilder = MessageBuilder::buildMessage($builder);
        $slashIndex = new SlashIndex($embedFields);
        $slashIndex->handlePagination(count($embedFields), $messageBuilder, $discord, $interaction, $builder);
    }


}