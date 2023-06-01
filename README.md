# Discord Bot (PHP)

This is a PHP-based bot for Discord, utilizing the [discord-php](https://github.com/discord-php/DiscordPHP) library.

## Installation

### Requirements

- PHP 8.2.5 or higher
- Composer
- translate-shell (for the translation command)
- [Spotify Application](https://developer.spotify.com/dashboard/applications) (for Spotify commands)
- Required tokens/secrets can be found in the `.env` file.

### Installation Steps

1. Clone the repository.
2. Run `composer install`.
3. Duplicate the `.env.example` file as `.env` and provide the required values.
4. Start the bot by running `php bot.php`.
5. Enjoy!


## Features

- [x] Basic commands
- [x] Basic event handling

## Slash Commands
| Category         | Command                               | Description                                                       |
|------------------|---------------------------------------|-------------------------------------------------------------------|
| Basic commands   | `/ping`                               | Replies with "pong".                                              |
|                  | `/Pagination`                         | A simple pagination example with buttons.                         |
|                  | `/Translate`                          | Translates a message to a given language.                         |
| Weather commands | `/Astronomy`                          | Shows astronomy data for a given location.                        |
|                  | `/Weather`                            | Shows weather data for a given location.                          |
|                  | `/Forecast`                           | Shows the weather forecast for a given location (3 days).         |
| Spotify commands | `/Spotify [Login, Logout, Me]`        | Performs login, logout, or displays user profile.                 |
|                  | `/Songsuggestions [Amount] [Playlist] [Genre]` | Shows song suggestions and optionally adds them to a playlist. |
|                  | `/Topsongs [Amount]`                  | Shows your top songs.                                             |
|                  | `/Latestsongs [Amount]`               | Shows your latest liked songs.                                    |
|                  | `/Playlists`                          | Shows your playlists.                                             |
|                  | `/Playlistgen [startdate] [public]`   | Generates a playlist based on your top songs.                     |
|                  | `/Currentsong`                        | Displays the currently playing song.                               |
| GitHub commands  | `/Updateself`                         | Updates the bot to the latest version.                            |


## Notes

- This bot is currently under development and may contain bugs.
- Some commands, like `/Songsuggestions [playlist=true]`, may result in multiple incomplete playlists.
