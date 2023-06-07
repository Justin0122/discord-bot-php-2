<?php

namespace Bot\Commands;

use Discord\Parts\Interactions\Interaction;
use Discord\Builders\MessageBuilder;
use Bot\Events\EphemeralResponse;
use Bot\Helpers\CommandRegistrar;
use Bot\Builders\EmbedBuilder;
use Bot\Events\Success;
use Bot\Events\Error;
use Bot\Events\Info;
use Discord\Discord;
use Bot\SlashIndex;

class Help
{
    public function getName(): string
    {
        return 'help';
    }

    public function getDescription(): string
    {
        return 'Show all commands';
    }

    public function getOptions(): array
    {
        $commands = CommandRegistrar::getAllCommands();
        foreach ($commands as $command) {
            $choices[] = [
                'name' => $command['name'],
                'value' => $command['name']
            ];
        }
        return [
            [
                'name' => 'command',
                'description' => 'command to show help for',
                'type' => 3,
                'required' => false,
                'choices' => $choices
            ],
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

    public function handle(Interaction $interaction, Discord $discord): void
    {
        $optionRepository = $interaction->data->options;
        $command = $optionRepository['command']->value;
        $ephemeral = $optionRepository['ephemeral']->value ?? false;

        $count = 0;

        if (!$command && $ephemeral){
            Error::sendError($interaction, $discord, "You can't use the ephemeral option without a command");
            return;
        }

        $embedFields = [];

        if ($command === null) {
            $commands = json_decode(file_get_contents(__DIR__.'/../../commands.json'), true);

            foreach ($commands as $command) {
                $embedFields = $this->getFields($command, $embedFields);
            }
            $description = 'All commands';

        }
        else{
            $description = 'Help for ' . $command;
            $commands = json_decode(file_get_contents(__DIR__.'/../../commands.json'), true);
            foreach ($commands as $command) {
                if ($command['name'] === $optionRepository['command']->value) {
                    $embedFields = $this->getFields($command, $embedFields, $count);
                    $count++;
                }
            }
        }
        $perPage = 4;
        $title = 'Help';

        $builder = Info::sendInfo($discord, $title, $description, $interaction);
        if (count($embedFields) <= $perPage) {
            $builder->addFirstPage($embedFields, $perPage);
        }
        $messageBuilder = \Bot\Builders\MessageBuilder::buildMessage($builder);

        if ($ephemeral) {
            EphemeralResponse::send($interaction, $messageBuilder, $ephemeral);
            return;
        }

        $slashIndex = new SlashIndex($embedFields);
        $slashIndex->setTotalPerPage($perPage);
        $slashIndex->handlePagination(count($embedFields), $messageBuilder, $discord, $interaction, $builder, $title, $description);
    }

    public function getFields(mixed $command, array $embedFields, $count = null): array
    {
        $options = '';
        foreach ($command['options'] as $option) {
            $required = $option['required'] ? ' (required)' : '';
            $options .= "- {$option['name']}{$required}\n";
        }

        $embedFields[] = [
            'name' =>  $command['description'],
            'value' => "```{$command['name']}```\n```{$options}```",
            'inline' => false
        ];
        return $embedFields;
    }

}