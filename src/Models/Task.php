<?php

namespace AmoWrapper\Models;

use AmoCRM\Exception;
use AmoCRM\Models\Task as VendorTask;
use AmoCRM\Request\ParamsBag;
use AmoWrapper\Client;
use AmoWrapper\Models\traits\Writable;

class Task extends VendorTask
{
    use Writable;

    protected $taskTypes;

    public function __construct(ParamsBag $parameters)
    {
        /**
         * @var Client
         */
        $client = $parameters->getAuth('client');
        $this->taskTypes = $this->apiGetTaskTypes($client);

        return parent::__construct($parameters);
    }

    /**
     * Возвращает ассоциативный массив доступных типов заданий
     * для текущего аккаунта.
     * Ключ - id, значение - текстовое название.
     *
     * @param Client $client
     * @return array
     * @throws Exception - при ошибке запроса
     */
    public function apiGetTaskTypes(Client $client)
    {
        $accountInfo = $client->account->apiCurrent();
        $resultArray = [];

        foreach ($accountInfo['task_types'] as $status) {
            $resultArray[$status['id']] = $status['name'];
        }

        return $resultArray;
    }

    /**
     * Создает для лида заадачу по проверке новой заявки.
     * Возвращает id созданной задачи.
     *
     *
     * @param $taskId - id лида
     * @param string $message - сообщение, прикрепленное к задаче
     * @param int $responsible - id ответственного юзера
     *
     * @return int
     * @throws Exception
     */
    public function apiCreateCheckOrderTask($taskId, $responsible = null, $message = 'Проверка брифа')
    {
        if (!$taskId) {
            throw new Exception('Не передан обязательный аргумент $taskId');
        }

        $taskInfo = [
            'element_id' => $taskId,
            'element_type' => 2,
            'task_type' => array_search('Проверка заявки', $this->taskTypes),
            'text' => $message,
            'complete_till' => '+2 hours',
        ];

        if ($responsible && is_numeric($responsible)) {
            $taskInfo['responsible_user_id'] = $responsible;
        }

        return $this->apiCreateEntity($taskInfo);
    }
}