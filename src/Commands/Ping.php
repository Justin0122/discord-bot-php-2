<?php

namespace Bot\Commands;

use Bot\Builders\ButtonBuilder;
use Bot\Builders\MessageBuilder;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;
use Bot\Builders\EmbedBuilder;
use Bot\Events\ButtonListener;


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

        $builder = new EmbedBuilder($discord);
        $builder->setTitle('Pong!');
        $builder->setDescription('Pong!');
        $builder->setSuccess();

        if ($value) {
            $builder->addField('Test', $value, false);
        }

        $button = ButtonBuilder::addPrimaryButton('Click me!', 'test');
        $messageBuilder = MessageBuilder::buildMessage($builder, [$button[0]]);
        $interaction->respondWithMessage($messageBuilder);

        ButtonListener::listener($discord, $button[1], 'Pong!', 'Button Clicked!');
    }
}