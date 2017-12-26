<?php

namespace AmoWrapper\Models;

use AmoCRM\Exception;
use AmoCRM\Models\Lead as VendorLead;
use AmoCRM\Request\ParamsBag;
use AmoWrapper\Client;
use AmoWrapper\Models\traits\GetCustomFields;
use AmoWrapper\Models\traits\Writable;

class Lead extends VendorLead
{
    use GetCustomFields, Writable;

    protected function getCustomFieldName()
    {
        return 'leads';
    }

    /**
     * Ассоциативный массив, где ключ - id статуса лида,
     * а значение - это текстовое название статуса
     *
     * @var array
     */
    protected $statuses;

    public function __construct(ParamsBag $parameters)
    {
        /**
         * @var Client
         */
        $client = $parameters->getAuth('client');
        $this->statuses = $this->apiGetStatuses($client);
        $this->customFields = $this->apiGetCustomFields($client);

        return parent::__construct($parameters);
    }

    /**
     * Список сделок
     *
     * Метод для получения списка сделок с возможностью фильтрации и постраничной выборки.
     * Ограничение по возвращаемым на одной странице (offset) данным - 500 сделок
     *
     * @link https://developers.amocrm.ru/rest_api/leads_list.php
     * @param null|array $parameters Массив параметров к amoCRM API
     * @param null|string $modified Дополнительная фильтрация по (изменено с)
     *
     * @return array Ответ amoCRM API
     * @throws Exception - при ошибке запроса
     */
    public function apiList($parameters = null, $modified = null)
    {
        return parent::apiList($parameters, $modified);
    }

    /**
     * Меняет статус указанного лида.
     * Список всех статусов можно получить
     * методом getStatuses.
     *
     * ВНИМАНИЕ!!!
     * При смене названия статуса его id тоже меняется.
     *
     * @param $id - id лида.
     * @param $status - id статуса
     * @return bool
     * @throws Exception - если статус зада неверно.
     */
    public function apiChangeStatus($id, $status)
    {
        if (!array_key_exists($status, $this->statuses)) {
            throw new Exception('Неверно задан статус для лида');
        }

        $this['status_id'] = $status;
        return $this->apiUpdate($id);
    }

    /**
     * Возвращает ассоциативный массив доступных статусов
     * сделки для текущего аккаунта.
     * Ключ - id, значение - текстовое название.
     *
     * @param Client $client
     * @return array
     * @throws Exception - при ошибке запроса
     */
    public function apiGetStatuses(Client $client)
    {
        $accountInfo = $client->account->apiCurrent();
        $resultArray = [];

        foreach ($accountInfo['leads_statuses'] as $status) {
            $resultArray[$status['id']] = $status['name'];
        }

        return $resultArray;
    }
}