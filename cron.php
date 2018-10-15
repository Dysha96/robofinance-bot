<?php
/**
 * README
 * This configuration file is intended to run a list of commands with crontab.
 * Uncommented parameters must be filled
 */

// Your command(s) to run, pass it just like in a message (arguments supported)
$commands = [
    '/whoami',
    "/echo I'm a bot!",
];

// Load composer
require_once __DIR__ . '/vendor/autoload.php';

// Add you bot's API key and name
$bot_api_key = getenv('BOT_API_KEY');
$bot_username = getenv('BOT_USERNAME');

// Define all IDs of admin users in this array (leave as empty array if not used)
$admin_users = [
//    314900634,
];

// Define all paths for your custom commands in this array (leave as empty array if not used)
$commands_paths = [
//    __DIR__ . '/Commands/',
];

$mysql_credentials = [
    'host' => getenv('HOST'),
    'user' => getenv('USER_DB'),
    'password' => getenv('PASSWORD'),
    'database' => getenv('DATABASE'),
];

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

    // Add commands paths containing your custom commands
    $telegram->addCommandsPaths($commands_paths);

    // Enable admin users
    $telegram->enableAdmins($admin_users);

    // Enable MySQL
    $telegram->enableMySql($mysql_credentials);

    // Logging (Error, Debug and Raw Updates)
    Longman\TelegramBot\TelegramLog::initErrorLog(__DIR__ . "/{$bot_username}_error.log");
    Longman\TelegramBot\TelegramLog::initDebugLog(__DIR__ . "/{$bot_username}_debug.log");
    Longman\TelegramBot\TelegramLog::initUpdateLog(__DIR__ . "/{$bot_username}_update.log");

    // If you are using a custom Monolog instance for logging, use this instead of the above
    //Longman\TelegramBot\TelegramLog::initialize($your_external_monolog_instance);

    // Set custom Upload and Download paths
    $telegram->setDownloadPath(__DIR__ . '/Download');
    $telegram->setUploadPath(__DIR__ . '/Upload');


    // Requests Limiter (tries to prevent reaching Telegram API limits)
    $telegram->enableLimiter();

    // Run user selected commands
    $telegram->runCommands($commands);

} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // Silence is golden!
    //echo $e;
    // Log telegram errors
    Longman\TelegramBot\TelegramLog::error($e);
} catch (Longman\TelegramBot\Exception\TelegramLogException $e) {
    // Silence is golden!
    // Uncomment this to catch log initialisation errors
    //echo $e;
}
