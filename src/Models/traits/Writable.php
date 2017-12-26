<?php

namespace AmoWrapper\Models\traits;

use AmoCRM\Exception;

/**
 * Class WritablePopulatable
 * @package AmoWrapper\Models\traits
 *
 * @property $values
 * @property $fields
 * @method apiAdd
 * @method apiUpdate($id)
 */
trait Writable
{
    /**
     * Переопределить функцию, если нужна проверка на уникальность сущности.
     * По молчанию говорит, что запись не занята. Это для сущностей,
     * для которых уникальность не важна.
     *
     * @param $fields - список параметров
     * @return bool
     */
    protected function apiCheckUnique($fields){
        return false;
    }

    /**
     * Добавляет сущность.
     *
     * Массив аргументов передается в слудеющем виде:
     * Всю информацию о кастомных полях можно получить
     * методом: $this::apiGetCustomFields
     *
     * Информацию о всех пользователя, например,
     * чтобы назначить ответственного на лид, можно получить
     * тут: Account::getUsers
     *
     * $fields = [
     *    'default_attributeName' => 'value',
     *    'custom_attributeName' => [
     *       'id' => 'id атрибута',
     *       'value' => 'value',
     *        // опциональное поле - enum
     *        // Например атрибут - телефон
     *        // enum : домашний, рабочий и т.д.
     *       'enum' => 'string',
     *    ],
     * ]
     *
     * ВНИМАНИЕ!
     * Если у какого-то атрибута тип - дропдаун,
     * т.е. есть предопределенный список значение, то
     * в value указывать string (одно значение из списка).
     * Доступные значения все также можно найти в
     * методе ($this::apiGetCustomFields)['enum']
     * Доступные значения будут соответствовать значениям массива.
     *
     * @param array $fields
     * @throws Exception - при какой-либо ошибке
     * @return int - возвращает id созданного контакта
     */
    public function apiCreateEntity($fields)
    {
        if ($this->apiCheckUnique($fields)) {
            throw new Exception('Сущность с таким именем уже существует');
        }
        $this->populateWithData($fields);
        $result = $this->apiAdd();
        $this->cleanValues();
        return $result;
    }

    /**
     * Добавляет новую информацию к существующеей сущности
     * или изменяет старую.
     *
     *
     * @param $id - id изменяемой сущности
     * @param $fields
     * @throws Exception - при какой-либо ошибке
     * @return bool
     */
    public function apiUpdateEntity($id, $fields)
    {
        if (isset($fields['name']) && $this->apiCheckUnique($fields)) {
            throw new Exception('Сущность с таким именем уже существует');
        }
        $this->populateWithData($fields);
        $result = $this->apiUpdate($id);
        $this->cleanValues();
        return $result;
    }

    /**
     * Заполняет модель переданными атрибутами.
     * Если id атрибута передан неверно - выбрасывает исключение.
     *
     * @param $fields
     * @throws Exception - если неверно передан id какого-то атрибута
     */
    protected function populateWithData($fields)
    {
        foreach ($fields as $name => $value) {
            if ($this->isFieldSet($name)) {
                $this[$name] = $value;
            } else {
                $customAttributeId = $value['id'];

                if (!array_key_exists($customAttributeId, $this->customFields)) {
                    throw new Exception('Неверно задан id атрибута');
                }

                $customAttributeValue = $value['value'];
                $customAttributeEnum = isset($value['enum']) ? $value['enum'] : false;
                $this->addCustomField($customAttributeId, $customAttributeValue, $customAttributeEnum);
            }
        }
    }

    /**
     * Очищает атрибуты модели,на случай, если эта же
     * модель будет использоваться для апдейтов или других добавлений.
     */
    public function cleanValues()
    {
        $this->values = [];
    }

    /**
     * Проверяет дефолтный ли это параметр.
     * Если да - возвращает true, иначе - false
     *
     * @param $name
     * @return bool
     */
    protected function isFieldSet($name)
    {
        if (in_array($name, $this->fields)) {
            return true;
        }

        return false;
    }
}