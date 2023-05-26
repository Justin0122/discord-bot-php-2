<?php

namespace Bot\Commands\Spotify;

use Bot\Builders\ButtonBuilder;
use Bot\Builders\EmbedBuilder;
use Bot\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;
use SpotifyWebAPI\Session;
use Bot\Events\Error;
use Bot\Models\Spotify as SpotifyModel;


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
        return [];
    }

    public function getGuildId(): ?string
    {
        return null;
    }

    public function handle(Interaction $interaction, Discord $discord, $user_id)
    {
        $session = new Session(
            $_ENV['SPOTIFY_CLIENT_ID'],
            $_ENV['SPOTIFY_CLIENT_SECRET'],
            $_ENV['SPOTIFY_REDIRECT_URI']
        );

        try {
            $me = new SpotifyModel();
            $me = $me->getMe($user_id);
            if ($me) {
                Error::sendError($interaction, $discord, 'You have already connected your spotify account');
            }
        } catch (\Exception $e) {
        }

        $url = "https://accounts.spotify.com/authorize?client_id={$_ENV['SPOTIFY_CLIENT_ID']}&response_type=code&redirect_uri={$_ENV['SPOTIFY_REDIRECT_URI']}&scope=user-read-email%20user-read-private%20user-library-read%20user-top-read%20user-read-recently-played%20user-read-playback-state%20user-read-currently-playing%20user-follow-read%20user-read-playback-position%20playlist-read-private%20playlist-modify-public%20playlist-modify-private%20playlist-read-collaborative%20user-library-modify%20user-follow-modify%20user-modify-playback-state%20user-read-recently-played%20user-read-playback-state%20user-modify-playback-state%20user-read-currently-playing%20user-read-playback-position%20user-read-recently-played%20user-read-playback-state%20user-modify-playback-state%20user-read-currently-playing%20user-read-playback-position%20user-read-recently-played%20user-read-playback-state%20user-modify-playback-state%20user-read-currently-playing%20user-read-playback-position%20user-read-recently-played%20user-read-playback-state%20user-modify-playback-state&state={$user_id}";

        $builder = new EmbedBuilder($discord);
        $builder->setTitle('Spotify');
        $builder->setSuccess();
        $row = ButtonBuilder::addLinkButton('Login', $url);

        $messageBuilder = MessageBuilder::buildMessage($builder, [$row]);

        $interaction->respondWithMessage($messageBuilder, true);
    }
}