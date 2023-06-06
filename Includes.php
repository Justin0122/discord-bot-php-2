<?php

$directories = [
    'Helpers',
    'Scheduler',
    'Events',
    'Builders',
    'Models',
    'Components'
];

foreach ($directories as $directory) {
    foreach (glob(__DIR__ . '/src/' . $directory . '/*.php') as $filename) {
        include $filename;
    }
}

foreach (glob(__DIR__ . '/src/*.php') as $filename) {
    include $filename;
}
