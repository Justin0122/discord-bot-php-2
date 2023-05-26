<?php

namespace Bot\Models;

use Bot\Helpers\SessionHandler;
use Bot\Helpers\TokenHandler;
use DateTime;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;

class Spotify
{
    private TokenHandler $tokenHandler;

    public function __construct()
    {
        $this->tokenHandler = new TokenHandler($_ENV['API_URL'], $_ENV['SECURE_TOKEN']);
    }

    public function checkLimit($amount)
    {
        if ($amount > 50) {
            $amount = 50;
        } else if (!$amount) {
            $amount = 24;
        }
        return $amount;
    }

    private function getTokens($user_id): bool|array
    {
        return $this->tokenHandler->getTokens($user_id);
    }

    public function getLatestSongs($user_id, $amount): object|array|null
    {
        $amount = $this->checkLimit($amount);

        $api = (new SessionHandler())->setSession($user_id);
        return $api->getMySavedTracks([
            'limit' => $amount,
        ]);
    }

    public function getMe($user_id): object|array|null
    {
        $api = (new SessionHandler())->setSession($user_id);
        return $api->me();
    }

    public function getCurrentSong($user_id): object|array|null
    {
        $tokens = $this->getTokens($user_id);
        if (!$tokens) {
            return null;
        }
        $api = (new SessionHandler())->setSession($user_id);
        if ($api->getMyCurrentTrack() == null) {
            return null;
        }
        return $api->getMyCurrentTrack();
    }

    public function getTopSongs($user_id, $amount): object|array|null
    {
        $amount = $this->checkLimit($amount);
        $api = (new SessionHandler())->setSession($user_id);
        $topTracks = $api->getMyTop('tracks', [
            'limit' => $amount,
        ]);
        return $topTracks;
    }

    /**
     * @throws \Exception
     */
    public function generatePlaylist($user_id, $startDate, $endDate, $public, Discord $discord, Interaction $interaction): bool|array
    {
        $api = (new SessionHandler())->setSession($user_id);
        $me = $api->me();
        $totalTracks = 250; // Total number of tracks to fetch
        $limit = 50; // Number of tracks per request
        $offset = 0; // Initial offset

        $trackUris = []; // Array to store track URIs

        // Fetch tracks in batches until there are no more tracks available
        while (count($trackUris) < $totalTracks) {
            $tracks = $api->getMySavedTracks([
                'limit' => $limit,
                'offset' => $offset,
                'time_range' => 'short_term'
            ]);

            $addedAt = new DateTime($tracks->items[0]->added_at);
            if ($addedAt < $startDate || (empty($tracks->items))) {
                // We've gone past the start date or there are no more tracks, so stop fetching
                break;
            }


            $filteredTracks = array_filter($tracks->items, function ($item) use ($startDate, $endDate) {
                $addedAt = new DateTime($item->added_at);
                return $addedAt >= $startDate && $addedAt <= $endDate;
            });

            $trackUris = array_merge($trackUris, array_map(function ($item) {
                return $item->track->uri;
            }, $filteredTracks));

            $offset += $limit; // Increment the offset for the next request
        }

        if (empty($trackUris)) {
            return false;
        }
        $playlistTitle = 'Liked Songs of ' . $startDate->format('M Y') . '.';

        $playlist = $api->createPlaylist([
            'name' => $playlistTitle,
            'public' => (bool)$public,
            'description' =>
                'This playlist was generated with your liked songs from ' .
                $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d') . '.'
        ]);

        $playlistUrl = $playlist->external_urls->spotify;
        $playlistId = $playlist->id;

        $trackUris = array_chunk($trackUris, 100);

        foreach ($trackUris as $trackUri) {
            $api->addPlaylistTracks($playlist->id, $trackUri);
        }

        return [$playlistUrl, $playlistId];
    }

    public function getPlaylists($user_id, $amount): object|array|null
    {
        echo 'getting playlists' . PHP_EOL;
        $amount = $this->checkLimit($amount);
        $api = (new SessionHandler())->setSession($user_id);
        $playlists = [];
        $offset = 0;

        //fetch the playlists in batches of 50 (the max)
        while (count($playlists) < $amount) {
            $fetchedPlaylists = $api->getUserPlaylists($user_id, [
                'limit' => 50,
                'offset' => $offset
            ]);

            $playlists = array_merge($playlists, $fetchedPlaylists);
            $offset += 50;
        }

        return $playlists;
    }

}
