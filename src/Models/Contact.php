<?php

namespace AmoWrapper\Models;

use AmoCRM\Exception;
use AmoCRM\Models\Contact as VendorContact;
use AmoWrapper\Models\traits\GetCustomFields;
use AmoWrapper\Models\traits\Writable;

class Contact extends VendorContact
{
    use GetCustomFields, Writable;

    protected function getCustomFieldName()
    {
        return 'contacts';
    }

    /**
     * Список контактов
     *
     * Метод для получения списка контактов с возможностью фильтрации и постраничной выборки.
     * Ограничение по возвращаемым на одной странице (offset) данным - 500 контактов.
     *
     * Если не передавать первый аргумент, то будут возвращены все контакты.
     *
     * @link https://developers.amocrm.ru/rest_api/contacts_list.php
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
     * ПРивязывает сделки к контакту
     *
     * @param int $id - id контакта
     * @param array $leadIds - массив id лидов
     * @return bool
     * @throws Exception - если при запросе произошла ошибка, или указан неверный id контакта.
     */
    public function apiAddLeadsToContact($id, array $leadIds)
    {
        $contact = $this->apiList([
            'id' => $id
        ]);

        if (empty($contact)) {
            throw new Exception('Контакта с заданным id не найдено');
        }

        $leads = array_merge($contact[0]['linked_leads_id'], $leadIds);
        return $this->apiUpdateEntity($id, [
            'linked_leads_id' => $leads
        ]);
    }

    /**
     * Проверяет нет ли уже имени с таким
     * контактом.
     *
     * @param $fields
     * @throws Exception - при ошибке запроса к API
     * @return bool
     */
    protected function apiCheckUnique($fields)
    {
        $result = $this->apiList([
            'query' => $fields['name'],
        ]);

        foreach ($result as $contact) {
            if ($contact['name'] === $fields['name']) {
                return true;
            }
        }

        return false;
    }
}