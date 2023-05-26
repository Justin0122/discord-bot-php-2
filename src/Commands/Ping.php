<?php

namespace Bot\Commands;

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
                'name' => 'test',
                'description' => 'test',
                'type' => 3,
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
        $firstOption = $optionRepository['test'];
        $value = $firstOption->value;

        $builder = Success::sendSuccess($discord, 'Pong!', 'Pong!');

        if ($value) {
            $builder->addField('Test', $value, false);
        }

        $button = ButtonBuilder::addPrimaryButton('Click me!', 'test');
        $messageBuilder = MessageBuilder::buildMessage($builder, [$button[0]]);
        $interaction->respondWithMessage($messageBuilder);

        ButtonListener::listener($discord, $button[1], 'Pong!', 'Button Clicked!');
    }
}