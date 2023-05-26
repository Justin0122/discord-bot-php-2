<?php

namespace Bot\Helpers;

use Discord\Discord;

class RemoveAllCommands
{
    public static function removeAllCommands(Discord $discord): void
    {
        try {
            $discord->application->commands->freshen()->done(function ($commands) {
                foreach ($commands as $command) {
                    $command->delete();
                    echo 'Removed command: ' . $command->name . PHP_EOL;
                }
            });
        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;
            echo "Error removing commands" . PHP_EOL;
        }
    }
}