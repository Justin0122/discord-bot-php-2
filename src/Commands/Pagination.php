<?php

namespace Bot\Commands;

use Bot\SlashIndex;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;
use Bot\Builders\EmbedBuilder;

class Pagination
{
    public function getName(): string
    {
        return 'pagination';
    }

    public function getDescription(): string
    {
        return 'test pagination';
    }

    public function getOptions(): array
    {
        return [
            [
                'name' => 'fields',
                'description' => 'amount of fields',
                'type' => 4,
                'required' => true
            ],
            [
                'name' => 'field',
                'description' => 'field content',
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
        $value = $optionRepository['field']->value;
        $amount = $optionRepository['fields']->value;

        for ($i = 0; $i < $amount; $i++) {
            $embedFields[] = [
                'name' => 'test',
                'value' => $value,
                'inline' => true
            ];
        }

        $slashIndex = new SlashIndex($embedFields);

        $builder = new EmbedBuilder($discord);
        $builder->setTitle('Pong!');
        $builder->setDescription('Pong!');
        $builder->setSuccess();

        $messageBuilder = new MessageBuilder();
        $messageBuilder->addEmbed($builder->build());

        $slashIndex->handlePagination(count($embedFields), $messageBuilder, $discord, $interaction, $builder);
    }

}