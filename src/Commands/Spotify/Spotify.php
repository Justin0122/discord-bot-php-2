<?php

namespace Bot\Commands\Spotify;

use Discord\Parts\Interactions\Interaction;
use Bot\Models\Spotify as SpotifyModel;
use Bot\Events\EphemeralResponse;
use Bot\Builders\MessageBuilder;
use Bot\Builders\ButtonBuilder;
use Bot\Builders\InitialEmbed;
use Bot\Events\Success;
use Bot\Events\Error;
use Discord\Discord;

class Spotify
{
    public function getName(): string
    {
        return 'spotify';
    }
    public function getDescription(): string
    {
        return 'Allow the bot to access your spotify account';
    }
    public function getOptions(): array
    {
        return [
            [
                'name' => 'select',
                'description' => 'Select an option',
                'type' => 3,
                'required' => true,
                'choices' => [
                    [
                        'name' => 'Login',
                        'value' => 'login'
                    ],
                    [
                        'name' => 'Logout',
                        'value' => 'logout'
                    ],
                    [
                        'name' => 'Me',
                        'value' => 'me'
                    ]
                ]
            ],
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

    public function handle(Interaction $interaction, Discord $discord, $user_id)
    {
        $optionRepository = $interaction->data->options;
        $login = $optionRepository['select']->value === 'login';
        $logout = $optionRepository['select']->value === 'logout';
        $me = $optionRepository['select']->value === 'me';
        $ephemeral = $optionRepository['ephemeral']->value ?? false;

        InitialEmbed::Send($interaction, $discord,'Fetching your data', true);

        $pid = pcntl_fork();
        if ($pid == -1) {
            die('could not fork');
        } else if ($pid) {
            //parent
        } else {
            $me = $this->connect($user_id);
            //child
            if ($login) {
                $this->login($interaction, $discord, $user_id, $me);
            } elseif ($logout) {
                $this->logout($interaction, $discord, $user_id, $me);
            } elseif ($me) {
                $this->me($interaction, $discord, $user_id, $ephemeral, $me);
            }
        }

    }

    private function login(Interaction $interaction, Discord $discord, $user_id, $me): void
    {
        if ($me){
            Error::sendError($interaction, $discord, 'You are already connected to Spotify', true);
        }

        $url = "https://accounts.spotify.com/authorize?client_id={$_ENV['SPOTIFY_CLIENT_ID']}&response_type=code&redirect_uri={$_ENV['SPOTIFY_REDIRECT_URI']}&scope=user-read-email%20user-read-private%20user-library-read%20user-top-read%20user-read-recently-played%20user-read-playback-state%20user-read-currently-playing%20user-follow-read%20user-read-playback-position%20playlist-read-private%20playlist-modify-public%20playlist-modify-private%20playlist-read-collaborative%20user-library-modify%20user-follow-modify%20user-modify-playback-state%20user-read-recently-played%20user-read-playback-state%20user-modify-playback-state%20user-read-currently-playing%20user-read-playback-position%20user-read-recently-played%20user-read-playback-state%20user-modify-playback-state%20user-read-currently-playing%20user-read-playback-position%20user-read-recently-played%20user-read-playback-state%20user-modify-playback-state%20user-read-currently-playing%20user-read-playback-position%20user-read-recently-played%20user-read-playback-state%20user-modify-playback-state&state={$user_id}";

        $builder = Success::sendSuccess($discord, 'Spotify');
        $button = ButtonBuilder::addLinkButton('Login', $url);

        $messageBuilder = MessageBuilder::buildMessage($builder, [$button[0]]);

        $interaction->sendFollowUpMessage($messageBuilder, true);
        $interaction->deleteOriginalResponse();
    }

    private function logout(Interaction $interaction, Discord $discord, $user_id): void
    {
        $this->connect($user_id);

        Error::sendError($interaction, $discord, 'Not implemented yet', true);
    }

    private function me(Interaction $interaction, Discord $discord, $user_id, $ephemeral, $me): void
    {
        if (!$me){
            Error::sendError($interaction, $discord, 'You are not connected to Spotify. Please use /spotify [Login] first', true);
        }
        $builder = Success::sendSuccess($discord, $me->display_name, '', $interaction);
        $builder->addField('Followers', $me->followers->total, true);
        $builder->addField('Country', $me->country, true);
        $builder->addField('Plan', $me->product, true);

        $spotify = new SpotifyModel();
        $topSongs = $spotify->getTopSongs($user_id, 3);

        if ($topSongs !== null && count($topSongs->items) > 0) {
            $topSongsField = "";
            foreach ($topSongs->items as $song) {
                $songName = $song->name;
                $artistName = $song->artists[0]->name;
                $songLink = $song->external_urls->spotify;
                $topSongsField .= "[{$songName}]({$songLink}) - {$artistName}\n";
            }
            $builder->addField('Top Songs', $topSongsField, true);
        } else {
            $builder->addField('Top Songs', 'No songs found', true);
        }
        if (isset($me->images[0]->url)) {
            $builder->setThumbnail($me->images[0]->url);
        }

        $currentSong = $spotify->getCurrentSong($user_id);
        $builder->addField('Currently listening to ', $currentSong->item->name . ' - ' . $currentSong->item->artists[0]->name, false ?? 'No song playing');

        $button = ButtonBuilder::addLinkButton('Open profile', $me->external_urls->spotify);
        if ($currentSong->item->external_urls->spotify) {
            $button2 = ButtonBuilder::addLinkButton('Listen along', $currentSong->item->external_urls->spotify);
            $messageBuilder = MessageBuilder::buildMessage($builder, [$button[0], $button2[0]]);
        }
        else {
            $messageBuilder = MessageBuilder::buildMessage($builder, [$button[0]]);
        }

        EphemeralResponse::send($interaction, $messageBuilder, $ephemeral, true);

    }

    private function connect($user_id): ?object
    {
        try {
            $me = new SpotifyModel();
            $me = $me->getMe($user_id);
            if (!$me) {
                return null;
            }
            return $me;
        } catch (\Exception $e) {

        }
        return null;
    }
}