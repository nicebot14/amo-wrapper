# amoCRM wrapper for php

## Использование

```php
<?php

// Создать клиента:
$client = new \AmoWrapper\Client('login', 'email', 'key');

// Проверить корректно ли может залогиниться пользователь:
$client->login();

// Теперь можно обращаться ко всем другим сущностям через этого клиента.
// Например, мы хотим получиь список лидов.
$client->lead->apiList();

// А теперь список контактов
$client->contact->apiList();

```

Все публичные методы подробно задокументированы в phpDoc комментариях.

## Примеры использования

* Залогинимся и получим списки дополнительных атрибутов для контактов и лидов.

```php
    <?php
    
    $client = new \AmoWrapper\Client('login', 'email', 'key');
    if (!$client->login()) {
        // неверные данные для входа
    }
    
    // здесь мы можем найти кастомные атрибуты, которые мы будем
    // использовать ниже для добавленения сущностей
    $contactCustomAttributes = $client->contact->apiGetCustomFields($client);
    $leadCustomAttributes = $client->lead->apiGetCustomFields($client);
    
    // также получим все возмодные статусы для сделок
    $leadStatuses = $client->lead->apiGetStatuses($client);
    
```

* Добавим новый контакт

```php
    <?php
    
    // все кастомные поля, т.е. в данном примере все, кроме name
    // мы получили выше. enum варианты значений мы получили там же.
    $contactInfo = [
        'name' => 'ФИО',
        'email' => [
            'id' => 1722184,
            'value' => 'test@from.php',
        ],
        'phone' => [
            'id' => 1307690,
            'enum' => 'MOB',
            'value' => '11111',
        ],
        'phone_two' => [
            'id' => 1307690,
            'enum' => 'WORK',
            'value' => '22222',
        ],
        'phone_three' => [
            'id' => 1307690,
            'enum' => 'HOME',
            'value' => '33333',
        ],
    ];
    
    // получили id созданного контакта
    $contactId = $client->contact->apiCreateEntity($contactInfo);
```

* Создадим новый лид по тому же приниципу

```php
    <?php

    // id статуса и id кастомных полей мцы получили в пункте 1
    $leadInfo = [
        'name' => 'Тестовая сделка',
        'status_id' => 12473673,
        'activity_area' => [
            'id' => 1721012,
            'value' => 'Интернет-бизнес',
        ],
    ];
    
    // получили id созданного лида
    $leadId = $client->lead->apiCreateEntity($leadInfo);
```

* Привяжем лид к контакту и затем поменяем статус лида

```php
    <?php
    
    $client->contact->apiAddLeadsToContact($contactId, [$leadId]);
    $client->lead->apiChangeStatus($leadId, 1234567);
```

* Создадим для текущего лида задачу по проверке брифа

```php
    <?php
    
    $taskId = $client->task->apiCreateCheckOrderTask($leadId, $responsibleUserId);
```

## Использование WorksectionClient для работы с Worksection.

```php
    <?php
    
    // создадим новый проект со стандартным списком задач
    
    $ws = new \AmoWrapper\Worksection\WorksectionClient('https://login.worksection.com/api/admin/', 'key');
    $params = [
        'title' => 'Новый проект',
        'dateend' => '12.12.2018',
        'members' => 'mail@one.ru, mail@two.ru',
    ];
    
    $result = $ws->createProjectWithTasks($params, 'Текст для описания главной задачи по разработке логотипа');

    // В $result['url'] находится ссылка на созданный проект. Внутри проекта созданы задачи
    // по определению исполнителей и разработке
```


```php
    <?php
    
    // токен получить можно тут: https://logomachine.worksection.com/account/api/
    $ws = new \AmoWrapper\Worksection\WorksectionClient('https://login.worksection.com/api/admin/', 'key');
    
    // создадим новый проект
    $params = [
        'title' => 'Новый проект',
        'dateend' => '12.12.2018',
        'members' => 'mail@one.ru, mail@two.ru',
    ];
    
    $result = $ws->createProject($params);
    
    // получим список всех проектов
    $projects = $ws->getProjects();
    
    // добавим задачу к проекту
    $params = [
        'page' => '/project/162323/',
        'title' => 'Новая задача задача',
        'text' => 'Описание задачи',
    ];
    
    $result = $ws->createTask($params);
```


