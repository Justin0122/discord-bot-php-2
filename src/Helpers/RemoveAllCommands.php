<?php

namespace Bot\Helpers;

use Discord\Discord;

class RemoveAllCommands
{
    public static function deleteAllCommands(Discord $discord): void
    {
        $discord->application->commands->freshen()->done(function ($commands) {
            foreach ($commands as $command) {
                echo "Deleting command: {$command->name}", PHP_EOL;
                $commands->delete($command);
            }
        });
    }
}