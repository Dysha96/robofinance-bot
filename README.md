**Important!** Before getting started with this project, make sure you have read the official [readme][2] to understand how the PHP Telegram Bot library works and what is required to run a Telegram bot.

Let's get started then! :smiley:


## 0. Cloning this repository

To start off, you can clone this repository using git:

```bash
$ git clone https://github.com/php-telegram-bot/example-bot.git
$ composer install
```
---

## Webhook installation

Note: For a more detailed explanation, head over to the [example-bot repository][example-bot-repository] and follow the instructions there.

In order to set a [Webhook][api-setwebhook] you need a server with HTTPS and composer support.
(For a [self signed certificate](#self-signed-certificate) you need to add some extra code)

Create [*set.php*][set.php] with the following contents:
```php
<?php
// Load composer
require __DIR__ . '/vendor/autoload.php';

$bot_api_key  = 'your:bot_api_key';
$bot_username = 'username_bot';
$hook_url     = 'https://your-domain/path/to/hook.php';

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

    // Set webhook
    $result = $telegram->setWebhook($hook_url);
    if ($result->isOk()) {
        echo $result->getDescription();
    }
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // log telegram errors
    // echo $e->getMessage();
}
```

Open your *set.php* via the browser to register the webhook with Telegram.
You should see `Webhook was set`.

Now, create [*hook.php*][hook.php] with the following contents:
```php
<?php
// Load composer
require __DIR__ . '/vendor/autoload.php';

$bot_api_key  = 'your:bot_api_key';
$bot_username = 'username_bot';

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

    // Handle telegram webhook request
    $telegram->handle();
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // Silence is golden!
    // log telegram errors
    // echo $e->getMessage();
}
```

### Self Signed Certificate

To upload the certificate, add the certificate path as a parameter in *set.php*:
```php
$result = $telegram->setWebhook($hook_url, ['certificate' => '/path/to/certificate']);
```

### Unset Webhook

Edit [*unset.php*][unset.php] with your bot credentials and execute it.

**Default**
Next, edit the following files, replacing all necessary values with those of your project.
Thanks to reading the main readme file, you should know what these do.

- `composer.json` (Describes your project and it's dependencies)
- `set.php` (Used to set the webhook)
- `unset.php` (Used to unset the webhook)
- `hook.php` (Used for the webhook method)
- `getUpdatesCLI.php` (Used for the getUpdates method)
- `cron.php` (Used to execute commands via cron)

**Bot Manager**
Using the bot manager makes life much easier, as all configuration goes into a single file, `manager.php`.

If you decide to use the Bot Manager, be sure to [read all about it][4] and change the `require` block in the `composer.json` file:
```json
"require": {
    "php-telegram-bot/telegram-bot-manager": "*"
}
```

Then, edit the following files, replacing all necessary values with those of your project.

- `composer.json` (Describes your project and it's dependencies)
- `manager.php` (Used as the main entry point for everything)

---

Now you can install all dependencies using [composer][5]:
```bash
$ composer install
```

## To be continued!

[1]: https://github.com/php-telegram-bot/core "php-telegram-bot/core"
[2]: https://github.com/php-telegram-bot/core#readme "PHP Telegram Bot - README"
[3]: https://github.com/php-telegram-bot/telegram-bot-manager "php-telegram-bot/telegram-bot-manager"
[4]: https://github.com/php-telegram-bot/telegram-bot-manager#readme "PHP Telegram Bot Manager - README"
[5]: https://getcomposer.org/ "Composer"
[example-bot-repository]: https://github.com/php-telegram-bot/example-bot "Example Bot repository"
[api-setwebhook]: https://core.telegram.org/bots/api#setwebhook "Webhook on Telegram Bot API"
[set.php]: https://github.com/php-telegram-bot/example-bot/blob/master/set.php "example set.php"
[unset.php]: https://github.com/php-telegram-bot/example-bot/blob/master/unset.php "example unset.php"
[hook.php]: https://github.com/php-telegram-bot/example-bot/blob/master/hook.php "example hook.php"
