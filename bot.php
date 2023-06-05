<?php

use Discord\Parts\Interactions\Interaction;
use Discord\Exceptions\IntentException;
use Bot\Helpers\RemoveAllCommands;
use Bot\Helpers\CommandRegistrar;
use Discord\Parts\User\Activity;
use Discord\WebSockets\Intents;
use Discord\WebSockets\Event;
use Discord\Discord;

include __DIR__.'/vendor/autoload.php';

include 'Includes.php';

use Dotenv\Dotenv;

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
});

$discord->on(Event::INTERACTION_CREATE, function (Interaction $interaction, Discord $discord) {
    $command = CommandRegistrar::getCommandByName($interaction->data->name);
    if ($command) {
        $command->handle($interaction, $discord, $interaction->member->user->id);
    }
});

$discord->run();