<?php

namespace Bot\Commands;

use Bot\SlashIndex;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
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
                'name' => 'field',
                'description' => 'field content',
                'type' => 3,
                'required' => false
            ],
            [
                'name' => 'fields',
                'description' => 'amount of fields',
                'type' => 4,
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

        $fields = [];

        $slashIndex = new SlashIndex($fields);

        $builder = new EmbedBuilder($discord);
        $builder->setTitle('Pong!');
        $builder->setDescription('Pong!');
        $builder->setSuccess();

        $slashIndex->setTotal($amount);
        $button1 = $slashIndex->paginationButton($discord, true);
        $button2 = $slashIndex->paginationButton($discord, false);

        if (($slashIndex->getOffset() + 1) === $slashIndex->getTotal()) {
            $button1->setDisabled(true);
        }

        if ($slashIndex->getOffset() === 0) {
            $button2->setDisabled(true);
        }

        $row = ActionRow::new()
            ->addComponent($button2)
            ->addComponent($button1);

        $messageBuilder = new MessageBuilder();
        $messageBuilder->addEmbed($builder->build());
        $messageBuilder->addComponent($row);

        $interaction->respondWithMessage($messageBuilder);
    }

}