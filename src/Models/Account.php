<?php

namespace AmoWrapper\Models;

use AmoCRM\Exception;
use AmoCRM\Models\Account as VendorAccount;

/**
 * {@inheritdoc}
 */
class Account extends VendorAccount
{
    /**
     * Отправка запроса не авторизацию.
     *
     * В случае успеха возвращает массив,
     * иначе выбрасывает исключение.
     *
     * @param array $parameters - массив с логином и токеном, переданные
     * конструкору класса Client.
     * @return array
     * @throws Exception - при ошибке запроса
     */
    public function apiLogin($parameters)
    {
        return $this->postRequest('/private/api/auth.php', $parameters);
    }

    /**
     * Возвращает список пользователей аккаунта с их правами
     *
     * @return mixed
     */
    public function apiGetUsers()
    {
        $data = $this->apiCurrent();
        return $data['users'];
    }
}