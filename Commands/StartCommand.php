<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Commands\UserCommands\OrderCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Request;

/**
 * Start command
 *
 * Gets executed when a user first starts using the bot.
 */
class StartCommand extends SystemCommand
{

    const TYPE_ORDER = 'Заказать технику';
    const TYPE_DOES_NOT_WORK = 'Не работает: (интернет, ПК, стул, стол, наушники и т.д)';
    const TYPE_ACCESS_REQUIRED = 'Нужен доступ к (впн, жира, еще что нить куда нужен доступ)';
    const TYPE_HELP_ADMIN = 'Нужна помощь админов (отедл ИТ)';
    const TYPE_INFINITES_DO_NOT_WORK = 'Беда с инфинити';
    const TYPE_LEAVE = 'Увольняюсь';
    /**
     * @var string
     */
    protected $name = 'start';

    /**
     * @var string
     */
    protected $description = 'Start command';

    /**
     * @var string
     */
    protected $usage = '/start';

    /**
     * @var string
     */
    protected $version = '1.1.0';

    /**
     * @var bool
     */
    protected $private_only = true;

    /**
     * Conversation Object
     *
     * @var \Longman\TelegramBot\Conversation
     */
    protected $conversation;

    /**
     * Command execute method
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        $message = $this->getMessage();
        $chat = $message->getChat();
        $user = $message->getFrom();
        $chat_id = $chat->getId();
        $user_id = $user->getId();
        $text = trim($message->getText(true));

        $this->conversation = new Conversation($user_id, $chat_id, $this->getName());

        $notes = &$this->conversation->notes;

        !is_array($notes) && $notes = [];

        $greetingText = 'Привет, я бот!' . PHP_EOL . 'Чем я могу тебе помочь?' . PHP_EOL . 'Все команды /help';


        $keyboards = new Keyboard([
            ['text' => self::TYPE_ORDER],
            ['text' => self::TYPE_DOES_NOT_WORK],
        ], [
            ['text' => self::TYPE_ACCESS_REQUIRED],
            ['text' => self::TYPE_HELP_ADMIN],
        ], [
            ['text' => self::TYPE_INFINITES_DO_NOT_WORK],
            ['text' => self::TYPE_LEAVE],
        ]);


        $keyboards->setResizeKeyboard(false)
            ->setOneTimeKeyboard(true)
            ->setSelective(false);;

        $data = [
            'chat_id' => $chat_id,
            'text' => $greetingText,
            'reply_markup' => $keyboards,
        ];

        switch ($text) {
            case self::TYPE_ORDER:
                $this->update->message['text'] = '';
                $orderCommand = new OrderCommand($this->telegram, $this->update);
                return $orderCommand->preExecute();
                break;
            case self::TYPE_DOES_NOT_WORK:
                $data['text'] = 'Функция в разработке';
                break;
            case self::TYPE_ACCESS_REQUIRED:
                $data['text'] = 'Функция в разработке';
                break;
            case self::TYPE_HELP_ADMIN:
                $data['text'] = 'Функция в разработке';
                break;
            case self::TYPE_INFINITES_DO_NOT_WORK:
                $data['text'] = 'Функция в разработке';
                break;
            case self::TYPE_LEAVE:
                $data['text'] = 'Функция в разработке';
                break;
        }

////            $this->getTelegram()->executeCommand('survey');
////            $this->getTelegram()->executeCommand('hidekeyboard');

        return Request::sendMessage($data);
    }
}
