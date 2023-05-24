<?php

namespace Bot\Events;

use Bot\Builders\EmbedBuilder;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;


class Error
{
    public static function sendError(Interaction $interaction, Discord $discord, $message): void
    {
        $builder = new EmbedBuilder($discord);
        $builder->setTitle('Error');
        $builder->setDescription($message ?? 'Something went wrong');
        $builder->setError();

        $messageBuilder = new MessageBuilder();
        $messageBuilder->addEmbed($builder->build());

        $interaction->respondWithMessage($messageBuilder, true);
    }
}