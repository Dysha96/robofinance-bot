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
use Longman\TelegramBot\Entities\InlineQuery\InlineQueryResultArticle;
use Longman\TelegramBot\Entities\InputMessageContent\InputTextMessageContent;
use Longman\TelegramBot\Request;

/**
 * Inline query command
 *
 * Command that handles inline queries.
 */
class InlinequeryCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'inlinequery';

    /**
     * @var string
     */
    protected $description = 'Reply to inline query';

    /**
     * @var string
     */
    protected $version = '1.1.1';

    /**
     * Command execute method
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        $inline_query = $this->getInlineQuery();
        $query = $inline_query->getQuery();

        $data = ['inline_query_id' => $inline_query->getId()];
        $results = [];

        if ($query == 'help') {
            $article =
                [
                    'id' => '001',
                    'title' => 'Отправь меня',
                    'description' => 'Нажми, и ему придёт инструкция',
                    'input_message_content' => new InputTextMessageContent(['message_text' => ' ' . '@RoboFinance_Bot' . PHP_EOL
                        . 'Выбери что тебе нужно и ответь на необходимые вопросы' . PHP_EOL
                        . 'После всех шагов проверь и подтверди, всё просто']),
                ];


            $results [] = new InlineQueryResultArticle($article);

        }

        $data['results'] = '[' . implode(',', $results) . ']';

        return Request::answerInlineQuery($data);
    }
}
