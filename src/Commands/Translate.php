<?php

namespace Bot\Commands;

use Bot\Builders\MessageBuilder;
use Bot\Events\Success;
use Discord\Parts\Interactions\Interaction;
use Discord\Discord;

class Translate
{
    public function getName(): string
    {
        return 'translate';
    }

    public function getDescription(): string
    {
        return 'translate text';
    }

    public function getOptions(): array
    {
        return [
            [
                'name' => 'text',
                'description' => 'text to translate',
                'type' => 3,
                'required' => true
            ],
            [
                'name' => 'to',
                'description' => 'language to translate to',
                'type' => 3,
                'required' => true
            ],
            [
                'name' => 'from',
                'description' => 'language to translate from (default: auto. Might result in wrong translation!)',
                'type' => 3,
                'required' => false
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

    public function handle(Interaction $interaction, Discord $discord): void
    {
        $optionRepository = $interaction->data->options;
        $ephemeral = $optionRepository['ephemeral']->value ?? false;
        $text = $optionRepository['text']->value;
        $to = $optionRepository['to']->value;
        $from = $optionRepository['from']->value ?? 'auto';

        exec('trans -b -t ' . $to . ' ' . $text . ' -f ' . $from, $output);
        $translation = implode(' ', $output);

        $builder = Success::sendSuccess($discord, 'Translation', $translation);
        $messageBuilder = MessageBuilder::buildMessage($builder);
        $interaction->respondWithMessage($messageBuilder, $ephemeral);

    }

}