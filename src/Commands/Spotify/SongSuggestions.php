<?php

namespace Bot\Commands\Spotify;

use Bot\Events\Error;
use Discord\Parts\Interactions\Interaction;
use Bot\Events\EphemeralResponse;
use Bot\Builders\MessageBuilder;
use Bot\Builders\ButtonBuilder;
use Bot\Builders\EmbedBuilder;
use Bot\Builders\InitialEmbed;
use Bot\Events\Success;

use Discord\Discord;
use JetBrains\PhpStorm\NoReturn;

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
            ],
            [
                'name' => 'queue',
                'description' => 'Get the current queue position or remove yourself from the queue',
                'type' => 3,
                'required' => false,
                'choices' => [
                    [
                        'name' => 'Get your position',
                        'value' => 'get'
                    ],
                    [
                        'name' => 'Remove me from queue',
                        'value' => 'remove'
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

        $queue = json_decode(file_get_contents(__DIR__ . '/../../../queue.json'), true);
        $position = array_search($user_id, array_keys($queue)) + 1;

        if ($interaction->data->options['queue']->value === 'get') {
            if (!isset($queue[$user_id])) {
                Error::sendError($interaction, $discord, 'You are not in the queue');
                return;
            }
            $builder = Success::sendSuccess($discord, 'You are currently in position ' . $position);
            $messageBuilder = MessageBuilder::buildMessage($builder);
            $interaction->respondWithMessage($messageBuilder, true);
            return;
        }

        if ($interaction->data->options['queue']->value === 'remove') {
            if (!isset($queue[$user_id])) {
                Error::sendError($interaction, $discord, 'You are not in the queue');
                return;
            }
            unset($queue[$user_id]);
            file_put_contents(__DIR__ . '/../../../queue.json', json_encode($queue, JSON_PRETTY_PRINT));
            $builder = Success::sendSuccess($discord, 'You have been removed from the queue');
            $messageBuilder = MessageBuilder::buildMessage($builder);
            $interaction->respondWithMessage($messageBuilder, true);
            return;
        }

        InitialEmbed::send($interaction, $discord, 'Please wait while we are fetching your song suggestions.', true);

        $optionRepository = $interaction->data->options;
        $amount = $optionRepository['amount']->value ?? 100;
        $genre = $optionRepository['genre']->value ?? false;
        $ephemeral = $optionRepository['ephemeral']->value ?? false;
        $mood = $optionRepository['mood']->value ?? false;

        if (isset($queue[$user_id])) {
            $builder = new EmbedBuilder($discord);
            $builder->setTitle('You are already in the queue');
            $builder->setDescription('Please wait until your song suggestions are ready.' . PHP_EOL . 'You are currently in position ' . $position);
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

        $pid = pcntl_fork();
        if ($pid == -1) {
            die('could not fork');
        } else if ($pid) {
            //parent
        } else {
            //child
            $this->loopOverJson($queue, $interaction, $discord, $user_id);
        }
    }

    #[NoReturn] private function loopOverJson($queue, $interaction, $discord, $userId): void
    {
        while (true) {
            if (!isset($queue[$userId])) {
                $this->sendFinishedMessage($interaction, $discord);
                exit(0);
            }
            sleep(1);
        }
    }

    private function sendFinishedMessage(Interaction $interaction, Discord $discord): void
    {
        echo 'finished' . PHP_EOL;
        $builder = Success::sendSuccess($discord, 'Playlist created', 'Your playlist has been created', $interaction);
        $messageBuilder = MessageBuilder::buildMessage($builder);

        $interaction->sendFollowUpMessage($messageBuilder, true);
    }

}