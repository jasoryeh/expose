[![https://expose.dev/?ref=github](https://expose.dev/images/expose/og_card.png)](https://expose.dev/?ref=github)

# Fork of Expose
Purpose: be able to run Expose a little bit more quickly and with less configuration files.

How?: Environment variables and Docker (if you'd like, but not required).

## Changes in this Fork
1. All (most) Expose configuration can be modified via environment variables (good for Docker, good for less configuration files).
   * Result: Easier configuration (hopefully).
2. A little bit more info on server startup :)
   * Result: Easier to find info.

## Using this Fork
1. Go to a directory you want to keep Expose installed in e.g. `cd ~/Documents/DevTools/`
2. `git clone https://github.com/jasoryeh/expose.git`
3. Then, choose one of the below: client or server

### Installing as a Client `expose`
3. `php expose install`
   * Follow the instructions, and verify the details.
   * Run the command if you would like to proceed.

Alternatively, you may `php expose install --server=expose.example.com --token=tokenhere --export > ~/.profile` to cut straight to the chase.
* For regular server, replace server argument `--default-server`
* For no token, replace token argument with `--no-token`

### Installing as a Server
To run standalone:
3. `EXPOSE_OTHER_SETTINGS=... php expose server $DOMAIN --port $PORT --validateAuthTokens`
   * Specify additional config such as `EXPOSE_USERS` before the command

To run with Docker:
4. `cp .env-example-server .env`
5. Modify `.env` to your desired settings
6. Modify ports in `docker-compose.yml` for what expose should listen on
7. `docker compose up`
    * Add `-d`, and run again to run in background

# Expose

[![Latest Version on Packagist](https://img.shields.io/packagist/v/beyondcode/expose.svg?style=flat-square)](https://packagist.org/packages/beyondcode/expose)
[![Quality Score](https://img.shields.io/scrutinizer/g/beyondcode/expose.svg?style=flat-square)](https://scrutinizer-ci.com/g/beyondcode/expose)
[![Total Downloads](https://img.shields.io/packagist/dt/beyondcode/expose.svg?style=flat-square)](https://packagist.org/packages/beyondcode/expose)

An open-source ngrok alternative - written in PHP.

## ⭐️ Managed Expose & Expose Pro ⭐️

You can use a managed version with our proprietary platform and our free (EU) test server at the [official website](https://expose.dev). Upgrade to Expose Pro to use our global server network with your own custom domains and get high-speed tunnels all over the world.

[Create an account](https://expose.dev)

## Documentation

For installation instructions of your own server, in-depth usage and deployment details, please take a look at the [official documentation](https://expose.dev/docs).

### Security

If you discover any security related issues, please email marcel@beyondco.de instead of using the issue tracker.

## Credits

- [Marcel Pociot](https://github.com/mpociot)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
