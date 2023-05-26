<?php

namespace Bot\Builders;

use Discord\Builders\MessageBuilder;
use Discord\Parts\Embed\Embed;
use Discord\Discord;

class EmbedBuilder extends MessageBuilder
{
    public $embed;
    private Discord $discord;

    public function __construct(Discord $discord)
    {
        $this->embed = new Embed($discord);
        $this->discord = $discord;
    }

    public function setTitle(string $title): self
    {
        $this->embed->title = $title;
        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->embed->description = $description;
        return $this;
    }

    public function addField(string $name, string $value, bool $inline): self
    {
        $this->embed->addFieldValues($name, $value, $inline);
        return $this;
    }

    public function setAuthor(string $name, string $iconUrl, string $url): self
    {
        $this->embed->setAuthor($name, $iconUrl, $url);
        return $this;
    }

    public function setFooter(string $text, string $iconUrl): self
    {
        $this->embed->setFooter($text, $iconUrl);
        return $this;
    }

    public function setImage(string $url): self
    {
        $this->embed->setImage($url);
        return $this;
    }

    public function setThumbnail(string $url): self
    {
        $this->embed->setThumbnail($url);
        return $this;
    }

    public function setTimestamp(string $timestamp): self
    {
        $this->embed->setTimestamp($timestamp);
        return $this;
    }

    public function setUrl(string $url): self
    {
        $this->embed->setUrl($url);
        return $this;
    }

    public function setSuccess(): self
    {
        $this->embed->setColor('00ff00');
        return $this;
    }

    public function setWarning(): self
    {
        $this->embed->setColor('ffff00');
        return $this;
    }

    public function setError(): self
    {
        $this->embed->setColor('ff0000');
        return $this;
    }

    public function setInfo(): self
    {
        $this->embed->setColor('0000ff');
        return $this;
    }


    public function build(): Embed
    {
        return $this->embed;
    }
}