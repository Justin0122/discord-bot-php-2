<?php

namespace Bot\Models;

use Bot\Helpers\SessionHandler;
use Bot\Helpers\TokenHandler;
use DateTime;

class Spotify
{
    private TokenHandler $tokenHandler;

    public function __construct()
    {
        $this->tokenHandler = new TokenHandler($_ENV['API_URL'], $_ENV['SECURE_TOKEN']);
    }

    public function checkLimit($amount)
    {
        if ($amount > 24) {
            $amount = 24;
        }
        else if (!$amount) {
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

    public function generatePlaylist($user_id, $startDate, $endDate, $public): void
    {
        $api = (new SessionHandler())->setSession($user_id);
        $me = $api->me();
        $totalTracks = 250; // Total number of tracks to fetch
        $limit = 50; // Number of tracks per request
        $offset = 0; // Initial offset


        $trackUris = []; // Array to store track URIs


        // Fetch tracks in batches until there are no more tracks available
        while (count($trackUris) < $totalTracks) {
            echo 'Fetching tracks ' . ($offset + 1) . ' to ' . ($offset + $limit) . '...' . PHP_EOL;
            $tracks = $api->getMySavedTracks([
                'limit' => $limit,
                'offset' => $offset,
                'time_range' => 'short_term'
            ]);

            $addedAt = new DateTime($tracks->items[0]->added_at);
            if ($addedAt < $startDate || (empty($tracks->items))) {
                echo 'No more tracks can be added.' . PHP_EOL;
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
            echo 'No tracks found.' . PHP_EOL;
            exit(); // Terminate the child process
        }


        $playlistTitle = 'Liked Songs of ' . $startDate->format('M Y') .'.';
        $playlist = $api->createPlaylist([
            'name' => $playlistTitle,
            'public' => (bool)$public,
            'description' =>
                'This playlist was generated with your liked songs from ' .
                $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d') . '.'
        ]);

        $trackUris = array_chunk($trackUris, 100);

        foreach ($trackUris as $trackUri) {
            $api->addPlaylistTracks($playlist->id, $trackUri);
            echo 'Added ' . count($trackUri) . ' tracks to ' . $playlistTitle . '.' . PHP_EOL;
        }

        echo 'Done!' . PHP_EOL;
    }

    public function getPlaylistIdByName($user_id, $playlistName)
    {
        $api = (new SessionHandler())->setSession($user_id);
        $playlists = $api->getUserPlaylists($api->me()->id);
        foreach ($playlists->items as $playlist) {
            if ($playlist->name == $playlistName) {
                return $playlist->id;
            }
        }
        return null;
    }
}
