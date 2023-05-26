<?php

namespace Bot;

use Discord\Parts\Interactions\Interaction;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Embed\Embed;
use Discord\Discord;

class SlashIndex
{
    public int $perPage = 12;
    public int $offset = -12;
    public int $total = 0;
    public array $fields = [];

    public function __construct(array $fields)
    {
        $this->total = count($fields);
        $this->fields = $fields;
    }

    public function paginationButton(Discord $discord, bool $isNextButton): Button
    {
        $label = $isNextButton ? 'Next' : 'Previous';

        return Button::new(Button::STYLE_PRIMARY)
            ->setLabel($label)
            ->setListener(function (Interaction $interaction) use ($discord, $isNextButton) {
                if ($interaction->member->id !== $interaction->message->interaction->user->id) {
                    $embed = new Embed($discord);
                    $embed->setTitle('Error');
                    $embed->setDescription('You can\'t do that!');
                    $embed->setColor('ff0000');
                    $interaction->respondWithMessage(MessageBuilder::new()->addEmbed($embed), true);
                    return;
                }

                if ($isNextButton) {
                    $this->incOffset($this->perPage);
                } else {
                    $this->incOffset(-$this->perPage);
                }

                $next = $this->paginationButton($discord, true);
                $previous = $this->paginationButton($discord, false);

                if (($this->getOffset() + $this->perPage) >= $this->getTotal()) {
                    $next->setDisabled(true);
                }

                if ($this->getOffset() <= 0) {
                    $previous->setDisabled(true);
                }

                $actionRow = ActionRow::new()->addComponent($previous)->addComponent($next);
                $interaction->message->edit(MessageBuilder::new()->addEmbed($this->getEmbed($discord))->addComponent($actionRow));
            }, $discord);
    }

    public function incOffset(int $amount): void
    {
        $this->offset += $amount;
    }

    public function getEmbed(Discord $discord): Embed
    {
        $embed = new Embed($discord);
        $embed->setColor('00ff00');

        $fields = array_slice($this->fields, $this->getOffset(), $this->perPage);
        foreach ($fields as $field) {
            $embed->addFieldValues($field['name'], $field['value'], $field['inline']);
        }
        $page = $this->getOffset() / $this->perPage;
        $embed->setFooter('Page ' . ($page + 1) . ' of ' . ceil($this->getTotal() / $this->perPage));

        return $embed;
    }

    public function handlePagination(int $totalFields, $builder, Discord $discord, Interaction $interaction, $embed, $isEdit = false): void
    {
        if ($totalFields > 0) {
            $button1 = $this->paginationButton($discord, true);
            $button2 = $this->paginationButton($discord, false);
            if (($this->getOffset() + 1) === $this->getTotal()) {
                $button1->setDisabled(true);
            }

            if ($this->getOffset() === 0) {
                $button2->setDisabled(true);
            }

            $fields = array_slice($this->fields, $this->getOffset(), $this->perPage);
            foreach ($fields as $field) {
                $embed->addField($field['name'], $field['value'], $field['inline']);
            }

            $row = ActionRow::new()
                ->addComponent($button2)
                ->addComponent($button1);

            $builder->addComponent($row);

            if ($isEdit) {
                $interaction->updateOriginalResponse($builder);
            } else {
                $interaction->respondWithMessage($builder);
            }
        }
        else{
            $interaction->respondWithMessage($builder);
        }

        $this->total = $totalFields;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

}