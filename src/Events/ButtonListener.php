<?php

namespace Bot\Events;

use Bot\Builders\EmbedBuilder;
use Bot\Builders\MessageBuilder;
use Discord\Builders\Components\Button;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;

class ButtonListener
{
    public static function listener(Discord $discord, $button, $title = "No title set", $description = "No description set"): void
    {
        $button->setListener(function (Interaction $interaction) use ($description, $title, $discord) {
            $builder = new EmbedBuilder($discord);
            $builder->setTitle($title);
            $builder->setDescription($description);
            $builder->setSuccess();

            $messageBuilder = MessageBuilder::buildMessage($builder);

            $interaction->updateMessage($messageBuilder);
        }, $discord);
    }
}