<?php

namespace Bot;

use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\Interaction;

class SlashIndex
{
    public int $offset = 0;
    public int $perPage = 5;
    public int $total = 0;

    public function __construct(array $fields)
    {
        $this->total = count($fields);
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
                    $embed->setColor('RED');
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
        $embed->setTitle('Button clicked');
        $embed->setColor('GREEN');

        $fields = [];
        foreach (range(1, $this->perPage) as $i) {
            if ($this->getOffset() + $i > $this->getTotal()) {
                break;
            }
                $fields[] = [
                    'name' => 'test',
                    'value' => 'test' . $this->getOffset() + $i,
                    'inline' => false
                ];
        }
        foreach ($fields as $field) {
            $embed->addFieldValues($field['name'], $field['value'], $field['inline']);
        }
        $page = $this->getOffset() / $this->perPage;
        $embed->setFooter('Page ' . ($page + 1) . ' of ' . ceil($this->getTotal() / $this->perPage));

        return $embed;
    }

    public function setTotal(int $total): void
    {
        $this->total = $total;
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