<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use DateTime;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Request;

/**
 * User "/order" command
 *
 * Command that demonstrated the Conversation funtionality in form of a simple survey.
 */
class OrderCommand extends SystemCommand
{
    const BUTTONS_PRODUCT_SELECTION_WITHOUT_STANDARD_EQUIPMENT
        = [
            'Периферия (мышь, клавиатура, наушники)',
            'Свой вариант'
        ];
    const BUTTONS_PRODUCT_SELECTION_WITH_STANDARD_EQUIPMENT
        = [
            ['Компьютер в сборе', 'Ноутбук'],
            ['Монитор', 'Системный блок'],
        ];
    const BUTTONS_DEFAULT_PRODUCT = ['Устраивает', 'Свой вариант'];
    const BUTTONS_CONFIRMATION = ['ОК', 'Начать заново'];

    const STATE_PRODUCT_SELECTION = 'Выбранный продукт';
    const STATE_DEFAULT_PRODUCT = 'По умолчанию';
    const STATE_DESCRIPTION_PRODUCT = 'Описание';
    const STATE_DATE_SELECTION = 'Выбранная дата';
    const STATE_WHY_NEED_IT = 'Зачем это нужно';
    const STATE_FOR_WHOM = 'Для кого';
    const STATE_CUSTOMER = 'Заказчик';
    const STATE_CONFIRMATION = 'Подтверждение';
    const STATE_END = 'End';


    /**
     * @var string
     */
    protected $name = 'order';

    /**
     * @var string
     */
    protected $description = 'Order users';

    /**
     * @var string
     */
    protected $usage = '/order';

    /**
     * @var string
     */
    protected $version = '0.3.0';

    /**
     * @var bool
     */
    protected $need_mysql = true;

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
     * @return array|\Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        $message = $this->getMessage();
        $chat = $message->getChat();
        $user = $message->getFrom();
        $text = trim($message->getText(true)) ? trim($message->getText(true)) : '';
        $chat_id = $chat->getId();
        $user_id = $user->getId();

        $data = [
            'chat_id' => $chat_id,
        ];

        if ($chat->isGroupChat() || $chat->isSuperGroup()) {
            $data['reply_markup'] = Keyboard::forceReply(['selective' => true]);
        }

        $this->conversation = new Conversation($user_id, $chat_id, $this->getName());

        $notes = &$this->conversation->notes;
        !is_array($notes) && $notes = [];

        $state = self::STATE_PRODUCT_SELECTION;

        if (isset($notes['state'])) {
            $state = $notes['state'];
        }

        $result = [];

        switch ($state) {
            case self::STATE_PRODUCT_SELECTION:
                $buttons = array_merge(self::BUTTONS_PRODUCT_SELECTION_WITH_STANDARD_EQUIPMENT,
                    self::BUTTONS_PRODUCT_SELECTION_WITHOUT_STANDARD_EQUIPMENT);

                if ($text === '' || !in_array($text, collect($buttons)->flatten()->toArray())) {
                    $notes['state'] = self::STATE_PRODUCT_SELECTION;
                    $this->conversation->update();

                    $data['reply_markup'] = (new Keyboard(...$buttons))
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->setSelective(true);

                    $data['text'] = $text !== '' ? 'Выберите из предложеных вариантов'
                        : 'Отлично, что тебе нужно заказать?';

                    $result[] = Request::sendMessage($data);
                    break;
                }

                $notes[self::STATE_PRODUCT_SELECTION] = $text;

                $text = in_array($text, self::BUTTONS_PRODUCT_SELECTION_WITHOUT_STANDARD_EQUIPMENT)
                    ?
                    self::BUTTONS_DEFAULT_PRODUCT[1] : '';

            case self::STATE_DEFAULT_PRODUCT:
                if ($text === '' || !in_array($text, self::BUTTONS_DEFAULT_PRODUCT)) {
                    $notes['state'] = self::STATE_DEFAULT_PRODUCT;
                    $this->conversation->update();

                    $data['reply_markup'] = (new Keyboard(self::BUTTONS_DEFAULT_PRODUCT))
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->setSelective(true);

                    $data['text'] = $text !== '' ? 'Выберите из предложеных вариантов'
                        : $this->standardEquipment($notes[self::STATE_PRODUCT_SELECTION]);

                    $result[] = Request::sendMessage($data);
                    break;
                }

                $notes[self::STATE_DEFAULT_PRODUCT] = $text == 'Устраивает' ?
                    $this->standardEquipment($notes[self::STATE_PRODUCT_SELECTION])
                    : 'Свой вариант';

                $text = $text == 'Устраивает' ? 'Стандартная комплекация' : '';

            case self::STATE_DESCRIPTION_PRODUCT:
                if ($text == '' || mb_strlen(trim($text)) < 8) {
                    $notes['state'] = self::STATE_DESCRIPTION_PRODUCT;
                    $this->conversation->update();

                    $data['text'] = $text !== '' ? 'Пожалуйста более подробно' : 'Можно подробнее?';

                    $result[] = Request::sendMessage($data);
                    break;
                }

                $notes[self::STATE_DESCRIPTION_PRODUCT] = $text;
                $text = '';

            case self::STATE_DATE_SELECTION:
                $date = new DateTime();

                $dateSelection = $date->createFromFormat('d m Y', $text);

                if ($text === '' || !$dateSelection || $date > $dateSelection) {
                    $notes['state'] = self::STATE_DATE_SELECTION;
                    $this->conversation->update();

                    $data['text'] = $text !== '' ? 'Некоректная дата, нужно в формате `31 01 2019`'
                        : 'Когда надо? (в формате `31 01 2019`)';

                    $result[] = Request::sendMessage($data);
                    break;
                }

                $notes[self::STATE_DATE_SELECTION] = $dateSelection->format("d-m-Y");
                $text = '';

            case self::STATE_WHY_NEED_IT:
                if ($text == '' || mb_strlen(trim($text)) < 8) {
                    $notes['state'] = self::STATE_WHY_NEED_IT;
                    $this->conversation->update();

                    $data['text'] = $text !== '' ? 'Пожалуйста более подробно'
                        : 'Напиши для чего тебе это?';

                    $result[] = Request::sendMessage($data);
                    break;
                }

                $notes[self::STATE_WHY_NEED_IT] = $text;
                $text = '';

            case self::STATE_FOR_WHOM:
                if ($text == '' || strlen($text) < 15) {
                    $notes['state'] = self::STATE_FOR_WHOM;
                    $this->conversation->update();

                    $data['text'] = $text !== ''
                        ? 'Пожалуйста более подробно'
                        :
                        'Для кого тебе это нужно?'
                        . PHP_EOL . 'ФИО'
                        . PHP_EOL . 'Отдел'
                        . PHP_EOL . 'Должность';

                    $result[] = Request::sendMessage($data);
                    break;
                }

                $notes[self::STATE_FOR_WHOM] = $text;
                $text = '';

            case self::STATE_CUSTOMER:
                if ($text == '' || mb_strlen(trim($text)) < 15) {
                    $notes['state'] = self::STATE_CUSTOMER;
                    $this->conversation->update();

                    $data['text'] = $text !== ''
                        ? 'Пожалуйста более подробно'
                        :
                        'Обещаю, это последний пункт'
                        . PHP_EOL . 'Кто заказчик?'
                        . PHP_EOL . 'ФИО'
                        . PHP_EOL . 'Отдел';

                    $result[] = Request::sendMessage($data);
                    break;
                }

                $notes[self::STATE_CUSTOMER] = $text;
                $text = '';

            case self::STATE_CONFIRMATION:
                if ($text === '' || !in_array($text, self::BUTTONS_CONFIRMATION)) {
                    $notes['state'] = self::STATE_CONFIRMATION;
                    $this->conversation->update();
                    $outText = 'Итак, тебе надо:' . PHP_EOL;

                    foreach ($notes as $key => $value) {
                        $outText .= $key !== 'state' && $key !== self::STATE_DEFAULT_PRODUCT
                            ? PHP_EOL . "{$key} : {$value}" : '';
                    }

                    $data['reply_markup'] = (new Keyboard(self::BUTTONS_CONFIRMATION))
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->setSelective(true);

                    $data['text'] = $text !== '' ? 'Выберите из предложеных вариантов'
                        : $outText . PHP_EOL . 'Подтвердите или начните заново';
                    $result[] = Request::sendMessage($data);
                    break;
                }

                if (strtoupper($text) !== self::BUTTONS_CONFIRMATION[0]) {
                    $this->update->message['text'] = '';
                    unset($notes['state']);
                    $this->conversation->update();
                    $result = (new OrderCommand($this->telegram, $this->update))->preExecute();
                    break;
                }

                $notes[self::STATE_CONFIRMATION] = $text;

            case self::STATE_END:
                unset($notes['state']);

                $outText = "Тут {$user->getFirstName()} {$user->getLastName()} технику заказал(а)"
                    . PHP_EOL;

                foreach ($notes as $key => $value) {
                    $outText .= PHP_EOL . "{$key} : {$value}";
                }

                $managementData = [
                    'chat_id' => (int)getenv('ADMIN_ID'),
                    'text' => $outText
                ];

                $data['text'] = 'Я сделалЬ.' .
                    PHP_EOL . 'Появятся вопросы по заказу, можешь задать их' .
                    PHP_EOL . '@h5_h5_h5_h5_h5' .
                    PHP_EOL . 'Что бы начать заново введи команду /start';

                $result[] = Request::sendMessage($managementData);
                $result[] = Request::sendMessage($data);

                $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                $this->conversation->stop();

            default:
                $result[] = Request::emptyResponse();
        }

        return $result;
    }

    private function standardEquipment($answer)
    {
        switch ($answer) {
            case self::BUTTONS_PRODUCT_SELECTION_WITH_STANDARD_EQUIPMENT[0][0]:
                return "Компьютер в сборе сосоит из" .
                    PHP_EOL . "Корпус Aerocool AERO-300 черный" .
                    PHP_EOL . "Блок питания Aerocool KCAS 500W [KCAS-500W]" .
                    PHP_EOL . "Оперативная память Crucial [CT8G4DFS824A] 8 ГБ (Прогерам 16)" .
                    PHP_EOL . "120 ГБ SSD - накопитель WD Green [WDS120G2G0A]" .
                    PHP_EOL . "Материнская плата MSI H310M PRO - M2" .
                    PHP_EOL . "Процессор Intel Core i3-8100 BOX Код товара: 1153709" .
                    PHP_EOL . "23.8\"\" Монитор HP 24es [T3M78AA]";
            case self::BUTTONS_PRODUCT_SELECTION_WITH_STANDARD_EQUIPMENT[0][1]:
                return "Стандартный ноутбук Оперативка <8 GB DDR4" .
                    PHP_EOL . "IPS Матрица, матовый экран" .
                    PHP_EOL . "SSD 250Gb" .
                    PHP_EOL . "Процессор не ниже 7 поколения(KabyLake)";
            case self::BUTTONS_PRODUCT_SELECTION_WITH_STANDARD_EQUIPMENT[1][0]:
                return 'Стандартный монитор "23.8"" Монитор HP 24es [T3M78AA]';
            case self::BUTTONS_PRODUCT_SELECTION_WITH_STANDARD_EQUIPMENT[1][1]:
                return "Стандартный системный блок" .
                    PHP_EOL . "Корпус Aerocool AERO-300 черный" .
                    PHP_EOL . "Блок питания Aerocool KCAS 500W [KCAS-500W]" .
                    PHP_EOL . "Оперативная память Crucial [CT8G4DFS824A] 8 ГБ (Прогерам 16)" .
                    PHP_EOL . "120 ГБ SSD - накопитель WD Green [WDS120G2G0A]" .
                    PHP_EOL . "Материнская плата MSI H310M PRO - M2" .
                    PHP_EOL . "Процессор Intel Core i3-8100 BOX Код товара: 1153709" ;
            default :
                return 'Без описания';
        }
    }
}
