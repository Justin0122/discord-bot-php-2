<?php

namespace Bot\Commands\Spotify;

use Bot\Builders\ButtonBuilder;
use Bot\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;
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
                'name' => 'playlist',
                'description' => 'Add the suggestions to a playlist',
                'type' => 5,
                'required' => false
            ],
            [
                'name' => 'genre',
                'description' => 'Filter the suggestions by genre (default none)',
                'type' => 3,
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
        InitialEmbed::send($interaction, $discord, 'Please wait while we are fetching your song suggestions');

        $pid = pcntl_fork();
        if ($pid == -1) {
            die('could not fork');
        } else if ($pid) {
            //parent
        } else {
            //child
            $this->getSongSuggestions($user_id, $discord, $interaction);
        }

    }


    private function getSongSuggestions($user_id, $discord, Interaction $interaction): void
    {
        $optionRepository = $interaction->data->options;
        $amount = $optionRepository['amount']->value ?? 100;
        $playlist = $optionRepository['playlist']->value ?? false;
        $genre = $optionRepository['genre']->value ?? false;

        $spotify = new Spotify();

        $songSuggestions = $spotify->getSongSuggestions($user_id, $amount, $genre);

        if ($songSuggestions === false) {
            Error::sendError($interaction, $discord, 'Something went wrong while fetching your song suggestions');
            return;
        }

        $embedFields = [];
        foreach ($songSuggestions as $songSuggestion) {
            $embedFields[] = [
                'name' => $songSuggestion->name,
                'value' => $songSuggestion->artists[0]->name . PHP_EOL . '[Open in Spotify](' . $songSuggestion->external_urls->spotify . ')',
                'inline' => true
            ];
        }
        $builder = Success::sendSuccess($discord, 'Song suggestions', 'Here are your song suggestions');
        if ($playlist) {
            $builder->addField('Playlist', 'A playlist will be created with your song suggestions', false);
        }


        $messageBuilder = MessageBuilder::buildMessage($builder);
        $slashIndex = new SlashIndex($embedFields);
        $slashIndex->handlePagination(count($embedFields), $messageBuilder, $discord, $interaction, $builder, true);
        if ($playlist) {
            $pid = pcntl_fork();
            if ($pid == -1) {
                die('could not fork');
            } else if ($pid) {
                //parent
            } else {
                //child
                $playlist = $spotify->createPlaylist($user_id, $songSuggestions);
                if ($playlist === false) {
                    Error::sendError($interaction, $discord, 'Something went wrong while creating your playlist');
                    exit(1);
                }
                else{
                    $builder2 = Success::sendSuccess($discord, 'Playlist created', 'Your playlist has been created');
                    $button = ButtonBuilder::addLinkButton('Open playlist', $playlist);
                    $messageBuilder = MessageBuilder::buildMessage($builder2, [$button[0]]);
                    $interaction->sendFollowUpMessage($messageBuilder);
                }
            }
        }
    }


}