<?php

namespace Bot\Buttons;

use AllowDynamicProperties;
use Discord\Builders\Components\Button;

#[AllowDynamicProperties] class NextButton extends Button
{
    public function __construct()
    {
        $this->type = 2;
        $this->style = 1;
        $this->label = 'next';
        $this->custom_id = 'Next';
    }

}