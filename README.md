# discord-bot-php

### this is a bot written in php for discord, using the [discord-php](https://github.com/discord-php/DiscordPHP) library.

## Installation

### Requirements

- PHP 8.2.5 or higher
- Composer
- translate-shell (for the translate command)
- (look in the <mark>.env</mark> file for needed tokens/secrets)
- Spotify Application [here](https://developer.spotify.com/dashboard/applications) (for the spotify commands)

### Steps

1. Clone the repository
2. Run `composer install`
3. Copy `.env.example` to `.env` and fill in the required values
4. Run `php bot.php` to start the bot
5. Enjoy!

## Features

- [x] Basic commands
- [x] Basic event handling

## Slash Commands

### Basic commands
- [x] `ping` - replies with "pong"
- [x] `Pagination` - a simple pagination example with buttons
- [x] `Translate` - translates a message to a given language

### Weather commands
- [x] `Astronomy` - shows the astronomy data for a given location
- [x] `Weather` - shows the weather data for a given location
- [x] `forecast` - shows the weather forecast for a given location (3 days)

### Spotify commands
- [x] `Spotify [Login, Logout, Me]` - login, logout or show your profile
- [x] `Songsuggestions` - shows a list of songs based on your last liked and most played songs
- [x] `Topsongs` - shows your top songs
- [x] `Latestsongs` - shows your latest liked songs
- [x] `Playlists` - shows your playlists
- [x] `Playlistgen` - generates a playlist of a given month and year
- [x] `Currentsong` - shows the song you are currently listening to

### Github commands

- [x] `Updateself` - updates the bot to the latest version

## Please note

- This bot is still in development, so there might be bugs
- Some commands will be buggy, like the <mark>/Songsuggestions [playlist = true]</mark> command. It may result in multiple, incomplete playlists.

