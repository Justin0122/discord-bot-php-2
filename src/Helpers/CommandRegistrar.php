<?php

namespace Bot\Helpers;

use Discord\Parts\Interactions\Command\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Discord\Discord;
use Discord\Builders\CommandBuilder;

class CommandRegistrar
{
    /**
     * @throws \Exception
     */
    public static function register(Discord $discord)
    {

        $dirIterator = new RecursiveDirectoryIterator(__DIR__.'/../Commands');
        $iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::LEAVES_ONLY);
        $phpFiles = new RegexIterator($iterator, '/^.+\.php$/i', RegexIterator::GET_MATCH);

        foreach ($phpFiles as $phpFile) {
            $filename = $phpFile[0];
            require_once $filename;

            $className = 'Bot\\Commands\\' . str_replace('/', '\\', substr($filename, strlen(__DIR__.'/../Commands/'), -4));

            if (class_exists($className)) {

                //if the guild id is set, register the command to the guild
                if ((new $className())->getGuildId()) {
                    $command = new Command($discord, ['name' => (new $className())->getName(), 'description' => (new $className())->getDescription(), 'options' => (new $className())->getOptions(), 'guild_id' => (new $className())->getGuildId()]);
                    $discord->guilds->offsetGet((new $className())->getGuildId())->commands->save($command);
                    echo "Registered command: " . $command->name . " to guild: " . $command->guild_id . PHP_EOL;
                    continue;
                }

                $command = new Command($discord, ['name' => (new $className())->getName(), 'description' => (new $className())->getDescription(), 'options' => (new $className())->getOptions(), 'guild_id' => (new $className())->getGuildId()]);
                $discord->application->commands->save($command);
                echo "Registered command: " . $command->name . PHP_EOL;
            }
        }
    }

    private static $commandCache = null;

    public static function getCommandByName($command)
    {
        if (self::$commandCache === null) {
            $commandClasses = [];

            // Scan the Commands directory recursively
            $commandFiles = self::scanDirectory(__DIR__ . '/../Commands');
            foreach ($commandFiles as $filename) {
                require_once $filename;
                $className = 'Bot\\Commands\\' . self::getClassNameFromFilename($filename);
                $commandClass = new $className();
                $commandName = $commandClass->getName();
                $commandClasses[$commandName] = $commandClass;
            }

            self::$commandCache = $commandClasses;
        }

        return self::$commandCache[$command] ?? null;
    }

    private static function scanDirectory($directory)
    {
        $files = [];
        $items = scandir($directory);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $directory . '/' . $item;
            if (is_dir($path)) {
                $files = array_merge($files, self::scanDirectory($path));
            } elseif (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === 'php') {
                $files[] = $path;
            }
        }
        return $files;
    }

    private static function getClassNameFromFilename($filename)
    {
        $relativePath = substr($filename, strpos($filename, 'Commands') + strlen('Commands') + 1, -4);
        return str_replace('/', '\\', $relativePath);
    }
}