<?php

namespace Bot\Commands\Github;

use Bot\Builders\InitialEmbed;
use Bot\Builders\MessageBuilder;
use Bot\Events\Success;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;

class UpdateSelf
{
    public function getName(): string
    {
        return 'updateself';
    }

    public function getDescription(): string
    {
        return 'Update the bot';
    }

    public function getOptions(): array
    {
        return [];
    }

    public function getGuildId(): ?string
    {
        return null;
    }

    /**
     * @throws \Exception
     */

    public function handle(Interaction $interaction, Discord $discord, $user_id): void
    {
        InitialEmbed::Send($interaction, $discord, 'Updating bot');

            $pid = pcntl_fork();
        if ($pid == -1) {
            die('could not fork');
        } else if ($pid) {
            //parent
        } else {
            //child
            $this->updateSelf($discord, $interaction);
        }
    }

    public function updateSelf(Discord $discord, Interaction $interaction): void
    {
        //pull from main
        exec('git pull origin main');

        //restart bot
        exec('pm2 restart 0');

        //send message
        $builder = Success::sendSuccess($discord, 'Bot updated', 'Bot has been updated');
        $messageBuilder = MessageBuilder::buildMessage($builder);
        $interaction->updateOriginalResponse($messageBuilder);
    }

}