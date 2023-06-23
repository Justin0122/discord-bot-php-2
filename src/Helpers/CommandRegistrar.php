<?php

namespace Bot\Helpers;

use Discord\Parts\Interactions\Command\Command;
use Discord\Discord;

class CommandRegistrar
{
    /**
     * @throws \Exception
     */
    public static function register(Discord $discord): void
    {
        $phpFiles = self::scanDirectory(__DIR__.'/../Commands');
        $commands = [];

        foreach ($phpFiles as $phpFile) {
            require_once $phpFile;

            $className = 'Bot\\Commands\\' . self::getClassNameFromFilename($phpFile);
            if (class_exists($className)) {
                $commandInstance = new $className();
                $name = strtolower((new \ReflectionClass($className))->getShortName());
                $description = $commandInstance->getDescription();
                $options = $commandInstance->getOptions();
                $guildId = $commandInstance->getGuildId();
                $cooldown = $commandInstance->getCooldown();

                $commandData = compact('name', 'description', 'options', 'guildId');
                $command = new Command($discord, $commandData);

                $target = $guildId ? $discord->guilds->offsetGet($guildId)->commands : $discord->application->commands;
                $target->save($command);
                echo "Registered command: {$name}" . ($guildId ? " to guild: {$guildId}" : "") . "\n";

                $category = explode('/', $phpFile)[count(explode('/', $phpFile)) - 2];
                $commands[] = compact('name', 'description', 'options', 'category', 'cooldown') + ['cooldown' => $commandInstance->getCooldown() ?? 5];
                if ($guildId) unset($commands[count($commands) - 1]['guild_id']);
            }
        }

        usort($commands, fn($a, $b) => $a['category'] <=> $b['category']);

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
            $commandFiles = self::scanDirectory(__DIR__.'/../Commands');
            $commandClasses = [];

            foreach ($commandFiles as $filename) {
                require_once $filename;
                $className = 'Bot\\Commands\\' . self::getClassNameFromFilename($filename);
                $commandClasses[strtolower((new \ReflectionClass($className))->getShortName())] = new $className();
            }

            self::$commandCache = $commandClasses;
        }

        return self::$commandCache[$command] ?? null;
    }

    private static function scanDirectory($directory): array
    {
        $files = [];

        foreach (scandir($directory) as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $directory . '/' . $item;
            $files = array_merge($files, is_dir($path) ? self::scanDirectory($path) : (pathinfo($path, PATHINFO_EXTENSION) === 'php' ? [$path] : []));
        }
        return $files;
    }

    private static function getClassNameFromFilename($filename): string
    {
        return str_replace('/', '\\', substr($filename, strpos($filename, 'Commands') + 9, -4));
    }
}
