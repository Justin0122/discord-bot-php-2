<?php

namespace Bot\Commands;

use Discord\Parts\Interactions\Interaction;
use Discord\Builders\MessageBuilder;
use Bot\Builders\EmbedBuilder;
use Discord\Discord;
use Bot\SlashIndex;

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
        $value = $optionRepository['field']->value ?? 'test';
        $amount = $optionRepository['fields']->value;

        for ($i = 1; $i < $amount; $i++) {
            $embedFields[] = [
                'name' => 'Field ' . $i,
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