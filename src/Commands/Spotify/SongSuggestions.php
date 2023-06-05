<?php

namespace Bot\Commands\Spotify;

use Bot\Builders\EmbedBuilder;
use Bot\Events\EphemeralResponse;
use Discord\Parts\Interactions\Interaction;
use Bot\Builders\MessageBuilder;
use Bot\Builders\ButtonBuilder;
use Bot\Builders\InitialEmbed;
use Bot\Models\Spotify;
use Bot\Events\Success;
use Bot\Events\Error;
use Discord\Discord;
use Bot\SlashIndex;

class SongSuggestions
{
    public function getName(): string
    {
        return 'songsuggestions';
    }

    public function getDescription(): string
    {
        return 'Get song suggestions based on your top songs';
    }

    public function getOptions(): array
    {
        return [
            [
                'name' => 'amount',
                'description' => 'amount of songs (default 100)',
                'type' => 4,
                'required' => false
            ],
            [
                'name' => 'genre',
                'description' => 'Filter the suggestions by genre (default none) (This does very little)',
                'type' => 3,
                'required' => false
            ],
            [
                'name' => 'ephemeral',
                'description' => 'Send the message only to you',
                'type' => 5,
                'required' => false
            ],
            [
                'name' => 'mood',
                'description' => 'Select an option',
                'type' => 3,
                'required' => false,
                'choices' => [
                    [
                        'name' => 'Happy',
                        'value' => 'happy'
                    ],
                    [
                        'name' => 'Sad',
                        'value' => 'sad'
                    ],
                    [
                        'name' => 'Dance',
                        'value' => 'dance'
                    ]
                ]
            ]
        ];
    }

    public function getGuildId(): ?string
    {
        return null;
    }

    public function handle(Interaction $interaction, Discord $discord, $user_id): void
    {
        InitialEmbed::send($interaction, $discord, 'Please wait while we are fetching your song suggestions', true);

        $optionRepository = $interaction->data->options;
        $amount = $optionRepository['amount']->value ?? 100;
        $genre = $optionRepository['genre']->value ?? false;
        $ephemeral = $optionRepository['ephemeral']->value ?? false;
        $mood = $optionRepository['mood']->value ?? false;

        $queue = json_decode(file_get_contents(__DIR__ . '/../../../queue.json'), true);
        //if the user is already in the queue, send a message that they are already in the queue
        if (isset($queue[$user_id])) {
            $builder = new EmbedBuilder($discord);
            $builder->setTitle('You are already in the queue');
            $builder->setDescription('Please wait until your song suggestions are ready');
            $builder->setError();
            $messageBuilder = MessageBuilder::buildMessage($builder);
            $interaction->updateOriginalResponse($messageBuilder);
            return;
        }
        $queue[$user_id] = [
            'amount' => $amount,
            'genre' => $genre,
            'mood' => $mood,
            'user_id' => $user_id,
        ];
        file_put_contents(__DIR__ . '/../../../queue.json', json_encode($queue, JSON_PRETTY_PRINT));
    }

    public function getSongSuggestions($user_id, $amount, $genre, $mood): void
    {

        $spotify = new Spotify();

        $songSuggestions = $spotify->getSongSuggestions($user_id, $amount, $genre, $mood);

        echo 'Song suggestions fetched' . PHP_EOL;

        $spotify->createPlaylist($user_id, $songSuggestions);
        echo 'Playlist created' . PHP_EOL;
    }

}