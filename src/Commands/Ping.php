<?php

namespace Bot\Commands;

use Bot\Events\EphemeralResponse;
use Discord\Parts\Interactions\Interaction;
use Bot\Builders\MessageBuilder;
use Bot\Builders\ButtonBuilder;
use Bot\Events\ButtonListener;
use Bot\Events\Success;
use Discord\Discord;


class Ping
{
    public function getName(): string
    {
        return 'ping';
    }

    public function getDescription(): string
    {
        return 'Ping the bot to check if it is online';
    }

    public function getOptions(): array
    {
        return [
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
        $builder = Success::sendSuccess($discord, 'Pong!', 'Pong!');
        $button = ButtonBuilder::addPrimaryButton('Click me!', 'test');
        $messageBuilder = MessageBuilder::buildMessage($builder, [$button[0]]);
        $interaction->respondWithMessage($messageBuilder, $ephemeral);

        ButtonListener::listener($discord, $button[1], 'Pong!', 'Button Clicked!');
    }
}