<?php

use Discord\Parts\Interactions\Interaction;
use Discord\Exceptions\IntentException;
use Bot\Helpers\RemoveAllCommands;
use Bot\Helpers\CommandRegistrar;
use Discord\Parts\User\Activity;
use Discord\WebSockets\Intents;
use Discord\WebSockets\Event;
use Discord\Discord;
use Dotenv\Dotenv;
use Bot\Events\Error;

include __DIR__.'/vendor/autoload.php';
include 'Includes.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $discord = new Discord([
        'token' => $_ENV['DISCORD_BOT_TOKEN'],
        'intents' => Intents::getDefaultIntents()
    ]);
} catch (IntentException $e) {
    echo $e->getMessage() . PHP_EOL;
    exit(1);
}

$discord->on('ready', function (Discord $discord) {
    echo "Bot is ready!", PHP_EOL;
                $activity = new Activity($discord, [
                'type' => Activity::TYPE_PLAYING,
                'name' => 'PHP'
            ]);
            $discord->updatePresence($activity);

//    RemoveAllCommands::deleteAllCommands($discord);
    CommandRegistrar::register($discord);

    generateCommandsTable();
});

$discord->on(Event::INTERACTION_CREATE, function (Interaction $interaction, Discord $discord) {
    $command = CommandRegistrar::getCommandByName($interaction->data->name);
    if ($command) {
        $userId = $interaction->member->user->id;
        $cooldownDuration = $command->getCooldown();
        $cooldownFile = 'logs/cooldowns.json'; // JSON file to store cooldown timestamps

        // Load existing cooldown data from the JSON file
        $cooldowns = [];
        if (file_exists($cooldownFile)) {
            $cooldowns = json_decode(file_get_contents($cooldownFile), true);
        }

        // Check if the user has a cooldown timestamp for the command
        //get the command name by classname
        $commandName = explode('\\', get_class($command));
        if (isset($cooldowns[$userId][$commandName[count($commandName) - 1]])) {
            $lastCommandTimestamp = $cooldowns[$userId][$commandName[count($commandName) - 1]];
            $currentTimestamp = time();
            $timeElapsed = $currentTimestamp - $lastCommandTimestamp;

            // Check if the cooldown period has elapsed
            if ($timeElapsed < $cooldownDuration) {
                Error::sendError($interaction, $discord, 'Please wait ' . ($cooldownDuration - $timeElapsed) . ' seconds before using this command again');
                return;
            }
        }

        // Execute the command
        $command->handle($interaction, $discord, $userId);

        // Update the cooldown timestamp for the user and command
        $cooldowns[$userId][$commandName[count($commandName) - 1]] = time();

        // Clean up expired cooldown timestamps
        foreach ($cooldowns as $user => $userCooldowns) {
            foreach ($userCooldowns as $commandName => $timestamp) {
                $timeElapsed = time() - $timestamp;
                if ($timeElapsed >= $cooldownDuration) {
                    unset($cooldowns[$user][$commandName]);
                }
            }
            if (empty($cooldowns[$user])) {
                unset($cooldowns[$user]);
            }
        }

        // Save the updated cooldown data to the JSON file
        file_put_contents($cooldownFile, json_encode($cooldowns));
    }
});

$discord->run();