<?php

namespace Bot\Events;

use Bot\Builders\EmbedBuilder;
use Discord\Discord;

class Success
{
    public static function sendSuccess(Discord $discord, $title, $description = null): EmbedBuilder
    {
        $builder = new EmbedBuilder($discord);
        $builder->setTitle($title);
        $builder->setDescription($description ?? '');
        $builder->setSuccess();

        return $builder;
    }
}