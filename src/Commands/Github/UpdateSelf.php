<?php

namespace Bot\Commands\Github;

use Discord\Parts\Interactions\Interaction;
use Bot\Builders\MessageBuilder;
use Bot\Builders\InitialEmbed;
use Bot\Events\Success;
use Discord\Discord;

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
        return [
            [
                'name' => 'ephemeral',
                'description' => 'Send the message only to you',
                'type' => 5,
                'required' => false
            ]
        ];
    }

    public function getGuildId(): ?string
    {
        return null;
    }

    /**
     * @throws \Exception
     */

    public function handle(Interaction $interaction, Discord $discord): void
    {
        $optionRepository = $interaction->data->options;
        $ephemeral = $optionRepository['ephemeral']->value ?? true;
        $botPid = getmypid();
        InitialEmbed::Send($interaction, $discord, 'Updating bot');

        $pid = pcntl_fork();
        if ($pid == -1) {
            die('could not fork');
        } else if ($pid) {
            //parent
        } else {
            //child
            $this->updateSelf($discord, $interaction, $botPid, $ephemeral);
        }
    }

    public function updateSelf(Discord $discord, Interaction $interaction, $botPid, $ephemeral): void
    {
        exec('git pull origin main');

        $builder = Success::sendSuccess($discord, 'Bot updated', 'Bot has been updated. ' . PHP_EOL . 'Please restart the bot to apply the changes.');
        $messageBuilder = MessageBuilder::buildMessage($builder);
        $interaction->sendFollowUpMessage($messageBuilder, $ephemeral);
        if ($ephemeral) {
            $interaction->deleteOriginalResponse();
        }
    }

}