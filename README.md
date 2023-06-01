# Discord Bot (PHP)

This PHP-based Discord bot, powered by the [discord-php](https://github.com/discord-php/DiscordPHP) library, is a hobby project aimed at expanding my knowledge of PHP and exploring the capabilities of the Discord API, as well as integrating with other APIs.

By developing this bot, I aim to gain hands-on experience in building and managing a Discord bot using PHP. Throughout the development process, I will be leveraging the functionality provided by the discord-php library to interact with the Discord API, enabling features such as command handling, event management, and more.

Furthermore, this project presents an opportunity for me to delve into other APIs and learn how to integrate them with my bot. For instance, I am exploring the use of the [translate-shell](https://github.com/soimort/translate-shell) for translation commands and leveraging the [Spotify API](https://developer.spotify.com/documentation/web-api) to provide music-related functionalities like song suggestions, top songs, and playlists.

Through this hobby project, I look forward to enhancing my PHP skills, deepening my understanding of the Discord and other APIs, and ultimately creating a useful and enjoyable Discord bot for users to interact with.


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
| Category         | Command                                        | Description                                                                |
|------------------|------------------------------------------------|----------------------------------------------------------------------------|
| Basic commands   | `/ping`                                        | Replies with "pong" and a simple button to test if the buttons are working |
|                  | `/Pagination [Amount]`                         | A simple pagination example with buttons.                                  |
|                  | `/Translate [Text] [To] [From] [Ephemeral]`    | Translates a message to a given language.                                  |
| Weather commands | `/Astronomy [Country] [City]`                  | Shows astronomy data for a given location.                                 |
|                  | `/Weather [Country] [City]`                    | Shows weather data for a given location.                                   |
|                  | `/Forecast [Country] [City]`                   | Shows the weather forecast for a given location (3 days).                  |
| Spotify commands | `/Spotify [Login, Logout, Me]`                 | Performs login, logout, or displays user profile.                          |
|                  | `/Songsuggestions [Amount] [Playlist] [Genre]` | Shows song suggestions and optionally adds them to a playlist.             |
|                  | `/Topsongs [Amount]`                           | Shows your top songs.                                                      |
|                  | `/Latestsongs [Amount]`                        | Shows your latest liked songs.                                             |
|                  | `/Playlists [Amount]`                          | Shows your playlists.                                                      |
|                  | `/Playlistgen [startdate] [public]`            | Generates a playlist based on your top songs.                              |
|                  | `/Currentsong`                                 | Displays the currently playing song.                                       |
| GitHub commands  | `/Updateself`                                  | Updates the bot to the latest version.                                     |


## Notes

- This bot is currently under development and may contain bugs.
- Some commands, like `/Songsuggestions [playlist=true]`, may result in multiple incomplete playlists.