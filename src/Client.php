<?php

namespace AmoWrapper;

use AmoCRM\Client as VendorClient;
use AmoCRM\Exception;
use AmoCRM\ModelException;
use AmoWrapper\Models\Account;
use AmoWrapper\Models\Contact;
use AmoWrapper\Models\Lead;
use AmoWrapper\Models\Task;

/**
 * {@inheritdoc}
 * @property Account $account
 * @property Contact $contact
 * @property Lead $lead
 * @property Task $task
 */
class Client extends VendorClient
{
    /**
     * Названия моделей из пакета dotzero/amocrm,
     * которые были расширены в данном пакете.
     *
     * @var array
     */
    protected $extendedModels = [
        'account',
        'contact',
        'lead',
        'task'
    ];

    /**
     * Параметры для логина берет переданные при создании класса.
     * Само отправление запроса происходит в метоже Account::apiLogin
     *
     * Если пользователь успешно залогинен, то будет возвращен
     * массив c ответом API.
     * Если залогиниться не удалось - будет выброшено исключение.
     *
     * @return array
     * @throws Exception
     */
    public function login()
    {
        $account = $this->account;

        $parameters = [
            'USER_LOGIN' => $this->parameters->getAuth('login'),
            'USER_HASH' => $this->parameters->getAuth('apikey'),
        ];

        return $account->apiLogin($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        if (in_array($name, $this->extendedModels)) {
            $classname = '\\AmoWrapper\\Models\\' . ucfirst($name);
        } else {
            $classname = '\\AmoCRM\\Models\\' . ucfirst($name);
        }

        if (!class_exists($classname)) {
            throw new ModelException('Model not exists: ' . $name);
        }

        // Чистим GET и POST от предыдущих вызовов
        $this->parameters->clearGet()->clearPost();
        $this->parameters->addGet('type', 'json');

        // TODO лучше инжектить через конструктор.
        // Если все таки придется переопределять Request и т.п. - то лучше инжектить.
        $this->parameters->addAuth('client', $this);

        return new $classname($this->parameters);
    }
}