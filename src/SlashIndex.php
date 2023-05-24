<?php

namespace Bot;

use AllowDynamicProperties;
use Bot\Builders\EmbedBuilder;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\Interaction;

class SlashIndex
{
    public int $offset = -12;
    public int $perPage = 12;
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
        $embed->setTitle('Button clicked');
        $embed->setColor('00ff00');

        $fields = [];
        foreach (range(1, $this->perPage) as $i) {
            if ($this->getOffset() + $i > $this->getTotal()) {
                break;
            }
                $fields[] = [
                    'name' => 'test',
                    'value' => 'test' . $this->getOffset() + $i,
                    'inline' => true
                ];
        }
        foreach ($fields as $field) {
            $embed->addFieldValues($field['name'], $field['value'], $field['inline']);
        }
        $page = $this->getOffset() / $this->perPage;
        $embed->setFooter('Page ' . ($page + 1) . ' of ' . ceil($this->getTotal() / $this->perPage));

        return $embed;
    }

    public function handlePagination(int $total, $builder, $discord, $interaction): void
    {
        if ($total > 12) {
            $button1 = $this->paginationButton($discord, true);
            $button2 = $this->paginationButton($discord, false);
            if (($this->getOffset() + 1) === $this->getTotal()) {
                $button1->setDisabled(true);
            }

            if ($this->getOffset() === 0) {
                $button2->setDisabled(true);
            }

            $row = ActionRow::new()
                ->addComponent($button2)
                ->addComponent($button1);

            $builder->addComponent($row);
        }
        else{
            $builder = new EmbedBuilder($discord);
            $builder->setTitle('Pong!');
            $builder->setDescription('Pong!');
            $builder->setSuccess();

            $messageBuilder = new MessageBuilder();
            $messageBuilder->addEmbed($builder->build());


            $interaction->respondWithMessage($messageBuilder);
        }

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