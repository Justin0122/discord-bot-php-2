<?php

namespace Bot\Events;

use Discord\Parts\Interactions\Interaction;
use Bot\Builders\MessageBuilder;
use Bot\Builders\EmbedBuilder;
use Discord\Discord;

class ButtonListener
{
    public static function listener(Discord $discord, $button, $title = "No title set", $description = "No description set", $isFollowUp = false): void
    {
        $button->setListener(function (Interaction $interaction) use ($button, $run, $description, $title, $discord, $isFollowUp) {
            $builder = new EmbedBuilder($discord);
            $builder->setTitle($title);
            $builder->setDescription($description);
            $builder->setSuccess();
            $messageBuilder = MessageBuilder::buildMessage($builder);


            if ($isFollowUp) {
                $interaction->sendFollowUpMessage($messageBuilder, true);
            }else {
                $interaction->updateMessage($messageBuilder);
            }
        }, $discord);
    }
}