<?php

namespace Bot\Events;

use Discord\Parts\Interactions\Interaction;

class EphemeralResponse
{
    public static function send(Interaction $interaction, $messageBuilder, $ephemeral = false, $isInitialEphemeral = false): void
    {
        if ($ephemeral && $isInitialEphemeral){
            $interaction->updateOriginalResponse($messageBuilder);
            return;
        }

        if (!$ephemeral || $isInitialEphemeral) {
            $interaction->sendFollowUpMessage($messageBuilder, $ephemeral);
            $interaction->deleteOriginalResponse();
        }
        $interaction->updateOriginalResponse($messageBuilder);
    }
}