<?php

if ($argc < 2) {
    echo "Usage: php MakeCommand.php [Command Name] [Command Directory]\n";
    exit(1);
}

$commandName = $argv[1];
$commandNameNonCapitalized = lcfirst($commandName);
$commandDirectory = $argc > 2 ? $argv[2] : '';
$commandPath = __DIR__ . "/src/Commands/" . ($commandDirectory ? "$commandDirectory/" : "") . "$commandName.php";
$namespace = "Bot\Commands" . ($commandDirectory ? "\\$commandDirectory" : "");
$template = <<<TEMPLATE
<?php

namespace $namespace;

use Discord\Parts\Interactions\Interaction;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Bot\Builders\MessageBuilder;
use Bot\Builders\ButtonBuilder;
use Bot\Events\ButtonListener;
use Discord\Parts\Embed\Embed;
use Bot\Events\Success;
use Discord\Discord;

class $commandName
{
    public function getName(): string
    {
        return '$commandNameNonCapitalized';
    }

    public function getDescription(): string
    {
        return 'Set description here';
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

    public function handle(Interaction \$interaction, Discord \$discord): void
    {
        \$optionRepository = \$interaction->data->options;
        \$ephemeral = \$optionRepository['ephemeral']->value ?? false;
        \$builder = Success::sendSuccess(\$discord, 'Pong!', 'Pong!');
        \$button = ButtonBuilder::addPrimaryButton('Click me!', 'test');
        \$messageBuilder = MessageBuilder::buildMessage(\$builder, [\$button[0]]);
        \$interaction->respondWithMessage(\$messageBuilder, \$ephemeral);

        ButtonListener::listener(\$discord, \$button[1], 'Pong!', 'Button Clicked!');
    }
}
TEMPLATE;

if (!file_exists(dirname($commandPath))) {
    mkdir(dirname($commandPath), 0777, true);
}

if (file_put_contents($commandPath, $template) !== false) {
    echo "Command file created successfully: $commandPath\n";
} else {
    echo "Failed to create the command file.\n";
}
