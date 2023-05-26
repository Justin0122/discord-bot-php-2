<?php

namespace Bot\Commands;

use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;
use Bot\Builders\EmbedBuilder;


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

        $button = Button::new(Button::STYLE_SUCCESS)
            ->setLabel('Click Me')
            ->setCustomId('click_me')
            ->setEmoji('👍');

        $row = ActionRow::new()
            ->addComponent($button);

        $messageBuilder = new MessageBuilder();
        $messageBuilder->addEmbed($builder->build());
        $messageBuilder->addComponent($row);

        $interaction->respondWithMessage($messageBuilder);

        $button->setListener(function (Interaction $interaction) use ($discord) {
            $builder = new EmbedBuilder($discord);
            $builder->setTitle('Pong!');
            $builder->setDescription('Button Clicked!');
            $builder->setSuccess();

            $messageBuilder = new MessageBuilder();
            $messageBuilder->addEmbed($builder->build());

            $interaction->updateMessage($messageBuilder);

        }, $discord);
    }
}