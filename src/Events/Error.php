<?php

namespace Bot\Events;

use Discord\Parts\Interactions\Interaction;
use Discord\Builders\MessageBuilder;
use Bot\Builders\EmbedBuilder;
use Discord\Discord;

class Error
{
    public static function sendError(Interaction $interaction, Discord $discord, $message, $isEdit = false): void
    {
        $builder = new EmbedBuilder($discord);
        $builder->setTitle('Error');
        $builder->setDescription($message ?? 'Something went wrong');
        $builder->setError();

        $messageBuilder = new MessageBuilder();
        $messageBuilder->addEmbed($builder->build());

        if ($isEdit) {
            $interaction->sendFollowUpMessage($messageBuilder, true);
            $interaction->deleteOriginalResponse();
            return;
        }

        $interaction->respondWithMessage($messageBuilder, true);
    }
}