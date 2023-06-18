<?php

namespace Bot\Helpers;

use Discord\Parts\Interactions\Command\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Discord\Discord;
use RegexIterator;
use Exception;

class CommandRegistrar
{
    /**
     * @throws Exception
     */
    public static function register(Discord $discord): void
    {

        $dirIterator = new RecursiveDirectoryIterator(__DIR__.'/../Commands');
        $iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::LEAVES_ONLY);
        $phpFiles = new RegexIterator($iterator, '/^.+\.php$/i', RegexIterator::GET_MATCH);

        foreach ($phpFiles as $phpFile) {
            $filename = $phpFile[0];
            require_once $filename;

            $className = 'Bot\\Commands\\' . str_replace('/', '\\', substr($filename, strlen(__DIR__.'/../Commands/'), -4));

            if (class_exists($className)) {
                $name = strtolower((new \ReflectionClass($className))->getShortName());

                //if the guild id is set, register the command to the guild
                if ((new $className())->getGuildId()) {
                    $command = new Command($discord, ['name' => $name, 'description' => (new $className())->getDescription(), 'options' => (new $className())->getOptions(), 'guild_id' => (new $className())->getGuildId()]);
                    $discord->guilds->offsetGet((new $className())->getGuildId())->commands->save($command);
                    echo "Registered command: " . $name . "to guild: " . (new $className())->getGuildId() . "\n";
                    continue;
                }
                else {

                    $command = new Command($discord, ['name' => $name, 'description' => (new $className())->getDescription(), 'options' => (new $className())->getOptions(), 'guild_id' => (new $className())->getGuildId()]);
                    $discord->application->commands->save($command);
                    echo "Registered command: " . $name . "\n";
                }

                $category = explode('/', $filename);
                $category = $category[count($category) - 2];
                $commands[] = [
                    'name' => $command->name,
                    'description' => $command->description,
                    'options' => $command->options,
                    'guild_id' => $command->guild_id,
                    'category' => $category,
                    'cooldown' => (new $className())->getCooldown() ?? 5,
                ];
                //if the guild id is set, remove it from the array. (so it doesn't show up in /help in other guilds for example)
                if ((new $className())->getGuildId()) {
                    unset($commands[count($commands) - 1]['guild_id']);
                }
            }
        }
        usort($commands, function ($a, $b) {
            return $a['category'] <=> $b['category'];
        });
        $commands = (object) $commands;
        file_put_contents(__DIR__.'/../../commands.json', json_encode($commands, JSON_PRETTY_PRINT));
    }

    private static $commandCache = null;

    /**
     * @throws \ReflectionException
     */
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
                $commandName = strtolower((new \ReflectionClass($className))->getShortName());
                $commandClasses[$commandName] = $commandClass;
                $commandCooldown = $commandClass->getCooldown() ?? 5;
            }

            self::$commandCache = $commandClasses;
        }

        return self::$commandCache[$command] ?? null;
    }

    private static function scanDirectory($directory): array
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

    private static function getClassNameFromFilename($filename): array|string
    {
        $relativePath = substr($filename, strpos($filename, 'Commands') + strlen('Commands') + 1, -4);
        return str_replace('/', '\\', $relativePath);
    }

}