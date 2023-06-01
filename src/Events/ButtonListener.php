<?php

namespace Bot\Events;

use Discord\Parts\Interactions\Interaction;
use Bot\Builders\MessageBuilder;
use Bot\Builders\EmbedBuilder;
use Discord\Discord;

class ButtonListener
{
    public static function listener(Discord $discord, $button, $title = "No title set", $description = "No description set", $run = null, $isFollowUp = false): void
    {
        $button->setListener(function (Interaction $interaction) use ($button, $run, $description, $title, $discord, $isFollowUp) {
            $builder = new EmbedBuilder($discord);
            $builder->setTitle($title);
            $builder->setDescription($description);
            $builder->setSuccess();

            //get the userid of the person who clicked the button
            $user_id = $interaction->member->user->id;

            $messageBuilder = MessageBuilder::buildMessage($builder);

            if ($run) {
                $pid = pcntl_fork();
                if ($pid == -1) {
                    die('could not fork');
                } else if ($pid) {
                    //parent
                } else {
                    //child
                    $run($user_id);
                }
            }

            if ($isFollowUp) {
                $interaction->sendFollowUpMessage($messageBuilder, true);
            }else {
                $interaction->updateMessage($messageBuilder);
            }
        }, $discord);
    }
}