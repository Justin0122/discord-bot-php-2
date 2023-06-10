<?php

namespace Bot\Commands\Github;

use Bot\Builders\InitialEmbed;
use Bot\Builders\MessageBuilder;
use Bot\Events\EphemeralResponse;
use Bot\Events\Error;
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
        return [
            [
                'name' => 'ephemeral',
                'description' => 'Send the message only to you (default: true)',
                'type' => 5,
                'required' => false
            ]
        ];
    }

    public function getGuildId(): ?string
    {
        return null;
    }

    public function getCooldown(): ?int
    {
        return 120;
    }
    /**
     * @throws \Exception
     */

    public function handle(Interaction $interaction, Discord $discord): void
    {
        if ($interaction->member->user->id != $_ENV['DISCORD_BOT_OWNER_ID']) {
            Error::sendError($interaction, $discord, 'You are not the bot owner');
            return;
        }
        $optionRepository = $interaction->data->options;
        $ephemeral = $optionRepository['ephemeral']->value ?? true;
        $botPid = getmypid();
        InitialEmbed::Send($interaction, $discord, 'Updating bot', $ephemeral);

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

        $builder = Success::sendSuccess($discord, 'Bot updated', 'Bot has been updated. ' . PHP_EOL . 'Please restart the bot to apply the changes.', $interaction);
        $messageBuilder = MessageBuilder::buildMessage($builder);
        EphemeralResponse::send($interaction, $messageBuilder, $ephemeral, true);
    }

}