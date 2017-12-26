<?php

namespace AmoWrapper\Models\traits;

use AmoCRM\Request\ParamsBag;
use AmoWrapper\Client;

/**
 * Class GetCustomFields
 * @package AmoWrapper\Models\traits
 */
trait GetCustomFields
{
    protected $customFields;

    /**
     * Возвращает имя элемента массива custom_fields.
     * Например, кастомные поля могут быть у контактов и у лидов.
     * В случае с лидами метод должен возвращать 'leads',
     * в случае с контактами 'contacts'
     *
     * @return string
     */
    abstract protected function getCustomFieldName();

    public function __construct(ParamsBag $parameters)
    {
        /**
         * @var Client
         */
        $client = $parameters->getAuth('client');
        $this->customFields = $this->apiGetCustomFields($client);

        return parent::__construct($parameters);
    }

    /**
     * Возвращает список всех кастомных атрибутов.
     * Вид массива ответа:
     * [
     *   id => [
     *     'name' => string,
     *     // опциональное поле
     *     'enum' => [
     *        0 => string,
     *        ...
     *      ]
     *   ],
     *   ...
     * ]
     *
     * enum - тип значения. Например атрибут - телефон.
     * enum - рабочий, домашний и т.п.
     *
     * @param Client $client
     * @return array
     */
    public function apiGetCustomFields(Client $client)
    {
        $accountInfo = $client->account->apiCurrent();
        $fieldName = $this->getCustomFieldName();
        $resultArray = [];

        foreach ($accountInfo['custom_fields'][$fieldName] as $field) {
            $resultArray[$field['id']]['name'] = $field['name'];
            if (isset($field['enums'])) {
                $resultArray[$field['id']]['enum'] = $field['enums'];
            }
        }

        return $resultArray;
    }
}