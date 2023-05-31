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
        return $api->getMyTop('tracks', [
            'limit' => $amount,
        ]);
    }

    /**
     * @throws \Exception
     */
    public function generatePlaylist($user_id, $startDate, $endDate, $public): bool|array
    {
        $api = (new SessionHandler())->setSession($user_id);
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
                break; // We've gone past the start date or there are no more tracks, so stop fetching
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
        $playlistImage = $playlist->images[0]->url;

        $trackUris = array_chunk($trackUris, 100);

        foreach ($trackUris as $trackUri) {
            $api->addPlaylistTracks($playlist->id, $trackUri);
        }

        return [$playlistUrl, $playlistId, $playlistImage];
    }

    public function getPlaylists($user_id, $amount): array | bool
    {
        $amount = $this->checkLimit($amount);
        $api = (new SessionHandler())->setSession($user_id);
        $playlists = [];
        $offset = 0;
        $me = $api->me();

        while (count($playlists) < $amount) {
            $fetchedPlaylists = $api->getUserPlaylists($me->id, [
                'limit' => 1,
                'offset' => $offset
            ]);

            $playlists = array_merge($playlists, $fetchedPlaylists->items);

            $offset += 1;
        }

        if (empty($playlists)) {
            return false;
        }

        return $playlists;
    }

    public function getSongSuggestions($user_id, $amount, $genre): array | bool
    {
        $api = (new SessionHandler())->setSession($user_id);

        // Get the top and latest songs of the user
        $topSongs = $this->getTopSongs($user_id, 50);
        $latestSongs = $this->getLatestSongs($user_id, 50);

        if (!$topSongs || !$latestSongs) {
            return false;
        }

        // Extract the track IDs from the top songs
        $topTrackIds = array_map(function ($track) {
            return $track->id;
        }, $topSongs);

        // Extract the track IDs from the latest songs
        $latestTrackIds = array_map(function ($track) {
            return $track->id ?? null;
        }, $latestSongs);

        // Merge the top track IDs and the latest track IDs and remove any null values
        $trackIds = array_filter(array_merge($topTrackIds, $latestTrackIds));

        $i = 0;

        while ($i < $amount) {
            // Shuffle the trackIds array
            shuffle($trackIds);

            // Get a random selection of seed tracks
            if ($genre) {
                $seedTracks = array_slice($trackIds, 0, 4);
                $recommendations = $api->getRecommendations([
                    'seed_tracks' => $seedTracks,
                    'seed_genres' => $genre,
                    'limit' => 5
                ]);
            }
            else{
                $seedTracks = array_slice($trackIds, 0, 5);
                $recommendations = $api->getRecommendations([
                    'seed_tracks' => $seedTracks,
                    'limit' => 5
                ]);
            }


            if (empty($recommendations->tracks)) {
                return false;
            }
            $i += 5;
        }

        return $recommendations->tracks;
    }

    public function createPlaylist($user_id, bool|array $tracks)
    {
        $api = (new SessionHandler())->setSession($user_id);

            $playlistTitle = 'Song Suggestions';
            $playlist = $api->createPlaylist([
                'name' => $playlistTitle,
                'public' => false,
                'description' =>
                    'This playlist was generated with your suggested songs.'
                ]);

            //loop through the song suggestions and add them to the playlist
            foreach ($tracks as $song) {
                $api->addPlaylistTracks($playlist->id, $song->uri);
            }

            return $playlist->external_urls->spotify;
    }


    private function getAudioFeatures($user_id, $trackIds): array | bool
    {
        $api = (new SessionHandler())->setSession($user_id);
        $audioFeatures = [];
        $offset = 0;

        while (count($audioFeatures) < count($trackIds)) {
            foreach (array_chunk($trackIds, 100) as $trackIdsChunk) {
                $fetchedAudioFeatures = $api->getMultipleAudioFeatures($trackIdsChunk);
            }

            $audioFeatures = array_merge($audioFeatures, $fetchedAudioFeatures->audio_features);

            $offset += 100;
        }

        if (empty($audioFeatures)) {
            return false;
        }

        return $audioFeatures;
    }

    private function getAverage($array, $key): float
    {
        $sum = 0;
        foreach ($array as $item) {
            $sum += $item->$key;
        }
        return $sum / count($array);
    }

}
