<?php
/**
 * Created by PhpStorm.
 * User: andrewalf
 * Date: 19.01.17
 * Time: 19:11
 */

namespace AmoWrapper\Worksection;

class WorksectionClient
{
    /**
     * Токен.
     * Получать по адресу: http://your-domain.com/account/api/
     *
     * @var string
     */
    protected $token;

    /**
     * Домен.
     * Например, в случае с логомашиной:
     * https://logomachine.worksection.com/api/admin/
     *
     * @var string
     */
    protected $domain;

    /**
     * Дефолтное значение для ответственного.
     * Значение - ведущий дизайнер
     *
     * @var string
     */
    private $defaultResponsible = 'blakconstantin@gmail.com';

    public function __construct($domain, $token)
    {
        $this->token = $token;
        $this->domain = $domain;
    }

    /**
     * Создает новый проект.
     *
     * Обязательные параметры - title и dateend (Срок завершения проекта).
     *
     * Если проект успешно создан - в свойстве url
     * будет возвращена ссылка на новый проект
     *
     * -------------------
     * Список параметров:
     * -------------------
     *
     * title - название проекта
     *
     * responsible - ответственный за проект
     *
     * members – список email сотрудников через запятую которые будут подписаны на проект
     *
     * text – текст проекта
     *
     * datestart – дата старта проекта в формате DD.MM.YYYY
     *
     * dateend – дата  финиша проекта в формате DD.MM.YYYY
     *
     * Пример массива $params
     *
     * [
     *   'title' => 'Название проекта',
     *   'dateend' => '12.12.2018',
     *   'members' => 'mail@one.ru, mail@two.ru',
     *   'responsible' => 'email',
     * ]
     *
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    public function createProject($params)
    {
        if (!isset($params['title']) || !isset($params['dateend'])) {
            throw new \Exception('Не передан title или dateend');
        }

        $responsible = $this->getValueOrDefault($params, 'responsible', $this->defaultResponsible);

        return $this->request([
            'action' => 'post_project',
            'email_user_from' => 'logomachinedrive@gmail.com',
            'email_manager' => $responsible,
            'email_user_to' => $responsible,
            'title' => $params['title'],
            'members' => $this->getValueOrDefault($params, 'members'),
            'dateend' => $params['dateend'],
            'datestart' => $this->getValueOrDefault($params, 'datestart'),
            'hash' => $this->generateHash(['post_project']),
        ]);
    }

    /**
     * Создает проект и сразу добавляет в проект список стандартных задач.
     * В params передается обычный кмассив с конфигурациями для
     * создаваемого проекта. Например:
     *
     * [
     *  'title' => 'Название компании',
     *  'dateend' => '12.12.2018',
     * ]
     *
     * @param $params
     * @param null|string $textForMainTask - текст, котоый будет написан в описании задачи по
     * созданию логотипа
     * @return array - массив, элемент url содержит ссылку на созданный проект
     * @throws \Exception
     */
    public function createProjectWithTasks($params, $textForMainTask = null)
    {
        $project = $this->createProject($params);
        $projectTitle = $params['title'];

        if ($project['status'] !== 'ok') {
            throw new \Exception('Не удалось создать проект');
        }

        $projectUrl = preg_replace('#.*?(?=\/project\/\d+)#iu', '', $project['url']);

        $tasks = [
            [
                'page' => $projectUrl,
                'title' => 'Назначить ответственного за разработку логотипа для "'.$projectTitle.'"',
                'priority' => 10,
                'dateend' => date('d.m.Y'),
                'datestart' => date('d.m.Y'),
            ],
            [
                'page' => $projectUrl,
                'title' => 'Разработка логотипа для "'.$projectTitle.'"',
                'responsible' => '',
                'text' => $textForMainTask ? $textForMainTask : 'Необходимо разработать логотип с презентацией для "'.$projectTitle.'". ТЗ прилагается',
                'dateend' => date('d.m.Y', strtotime('+2 days')),
                'datestart' => date('d.m.Y'),
                'subtasks' => [
                    [
                        'title' => 'Разработка эскиза ("'.$projectTitle.'")',
                        'responsible' => '',
                        'text' => 'Разработка эскиза логотипа по брифу (основная концепция, можно просто на бумаге).',
                    ],
                    [
                        'title' => 'Проверка эскиза ("'.$projectTitle.'")',
                        'text' => 'Проверка разработанного эскиза. Предоставление замечаний.',
                    ],
                    [
                        'title' => 'Отрисовка логотипа ("'.$projectTitle.'")',
                        'responsible' => '',
                        'text' => '1) Отрисовка логотипа, внесение правок по замечаниям от ведущего дизайнера. 2) Подготовка описания логотипа (почему выбрана именно такая концепция и цвета, какой смысл закладывался).',
                    ],
                    [
                        'title' => 'Приемка логотипа ("'.$projectTitle.'")',
                        'text' => 'Окончательная приемка логотипа',
                    ],
                ],
            ],
            [
                'page' => $projectUrl,
                'title' => 'Назначить ответственного за разработку презентации для "'.$projectTitle.'"',
                'priority' => 10,
                'dateend' => date('d.m.Y', strtotime('+2 days')),
                'datestart' => date('d.m.Y', strtotime('+2 days')),
            ],
            [
                'page' => $projectUrl,
                'title' => 'Разработать презентацию для "'.$projectTitle.'"',
                'responsible' => '',
                'text' => 'Разработать презентацию логотипа для "'.$projectTitle.'".',
                'dateend' => date('d.m.Y', strtotime('+4 days')),
                'datestart' => date('d.m.Y', strtotime('+3 days')),
                'subtasks' => [
                    [
                        'title' => 'Поставить задачу копирайтеру ("'.$projectTitle.'")',
                        'text' => 'Поставить задачу копирайтеру по разработке текста для презентации логотипа на основе описания, подготовленного дизайнером, разработавшим логотип.',
                    ],
                    [
                        'title' => 'Разработка копирайта для презентации ("'.$projectTitle.'")',
                        'responsible' => '',
                        'text' => 'Разработка копирайта для презентации',
                    ],
                    [
                        'title' => 'Разработка презентации ("'.$projectTitle.'")',
                        'responsible' => '',
                        'text' => 'Разработка презентации для логотипа. Не забыть получить от копирайтера текст и вставить в презентацию.',
                    ],
                    [
                        'title' => 'Приемка презентации ("'.$projectTitle.'")',
                        'text' => 'Приемка презентации. Внесение правок.',
                    ],
                    [
                        'title' => 'Назначить ответственного за выгрузку проекта на Behance ("'.$projectTitle.'")',
                        'text' => 'Назначить ответственного за выгрузку проекта на Behance, передать ему необходимую информацию и файлы.',
                    ],
                ],
            ],
            [
                'page' => $projectUrl,
                'title' => 'Залить презентацию логотипа "'.$projectTitle.'" на Behance',
                'text' => 'Залить готовую презентацию логотипа на Behance',
                'responsible' => '',
                'dateend' => date('d.m.Y', strtotime('+6 days')),
                'datestart' => date('d.m.Y', strtotime('+5 days')),
            ],
            [
                'page' => $projectUrl,
                'title' => 'Завершение работы по проекту "'.$projectTitle.'"',
                'text' => 'Удостовериться, что все загружено, есть необходимые ссылки, задача закрыта. Закрыть проект.',
                'priority' => 10,
                'dateend' => date('d.m.Y', strtotime('+6 days')),
                'datestart' => date('d.m.Y', strtotime('+6 days')),
            ],
        ];

        foreach ($tasks as $task) {
            $subtasks = null;

            if (isset($task['subtasks'])) {
                $subtasks = $task['subtasks'];
                unset($task['subtasks']);
            }

            $createdTask = $this->createTask($task);

            if ($createdTask['status'] !== 'ok') {
                throw new \Exception('Не удалось добавить задачу');
            }

            if ($subtasks) {
                $subTaskUrl = $task['page'] . preg_replace('#.*\/(?=\d+\/$)#iu', '', $createdTask['url']);

                foreach ($subtasks as $subtask) {
                    $subtask['page'] = $subTaskUrl;
                    $createdTask = $this->createTask($subtask, true);

                    if ($createdTask['status'] !== 'ok') {
                        throw new \Exception('Не удалось добавить задачу');
                    }
                }
            }
        }

        return [
            'status' => 'ok',
            'url' => $projectUrl,
        ];
    }

    /**
     * Возвращает список всех проектов.
     *
     * Если ошибка запроса - возвратит описание ошибки и
     * статус - error
     *
     * @return mixed
     */
    public function getProjects()
    {
        $result = $this->request([
            'action' => 'get_projects',
            'hash' => $this->generateHash(['get_projects']),
        ]);

        if ($result['status'] === 'ok') {
            return $result['data'];
        }

        return $result;
    }

    public function getComments($task)
    {
        $result = $this->request([
            'action' => 'get_comments',
            'page' => $task,
            'hash' => $this->generateHash([$task, 'get_comments']),
        ]);

        return $result;
    }

    public function getTasks($project)
    {
        $result = $this->request([
            'action' => 'get_tasks',
            'page' => $project,
            'hash' => $this->generateHash([$project, 'get_tasks']),
        ]);

        return $result;
    }

    /**
     * Добавление задачи к проекту.
     *
     * Обязательные параметры - title и page (ccылка на проект,
     *  например /project/162323/).
     *
     * Если проект успешно создан - в свойстве url
     * будет возвращена ссылка на новую задачу
     *
     * -------------------
     * Список параметров:
     * -------------------
     *
     * title - название задачи
     *
     * page - ссылка на проект
     *
     * responsible - ответственный за задачу
     *
     * text – текст задачи
     *
     * priority – приоритет, число от 0 до 10
     *
     * datestart – дата старта задачи в формате DD.MM.YYYY
     *
     * dateend – дата  финиша задачи в формате DD.MM.YYYY
     *
     * subscribe – список email сотрудников через запятую которые будут подписаны на задачу
     *
     * @param $params
     * @param $isSubtask - если true, то создается подзадача
     * @return mixed
     * @throws \Exception
     */
    public function createTask($params, $isSubtask = false)
    {
        if (!isset($params['title']) || !isset($params['page'])) {
            throw new \Exception('Не передан title или page');
        }

        $responsible = $this->getValueOrDefault($params, 'responsible', $this->defaultResponsible);
        $action = $isSubtask? 'post_subtask' : 'post_task';

        return $this->request([
            'action' => $action,
            'page' => $params['page'],
            'title' => $params['title'],
            'email_user_from' => $this->defaultResponsible,
            'email_user_to' => $responsible,
            'text' => $this->getValueOrDefault($params, 'text'),
            'priority' => $this->getValueOrDefault($params, 'priority'),
            'datestart' => $this->getValueOrDefault($params, 'datestart'),
            'dateend' => $this->getValueOrDefault($params, 'dateend'),
            'subscribe' => $this->getValueOrDefault($params, 'subscribe'),
            'hash' => $this->generateHash([$params['page'], $action]),
        ]);
    }

    /**
     * Если в массиве params есть элемент с ключом name,
     * то возвращает значение этого элемента.
     * Если нет, то возвращает $defaultValue.
     *
     * @param $params
     * @param $name
     * @param string $defaultValue
     * @return string
     */
    protected function getValueOrDefault($params, $name, $defaultValue = '')
    {
        return isset($params[$name]) ? $params[$name] : $defaultValue;
    }

    /**
     * Просто сеттер токена.
     * Если конструктору передан неверный токен -
     * можно переопределить его
     *
     * @param $token
     */
    public function resetToken($token)
    {
        $this->token = $token;
    }

    /**
     * Генерирует хеш, который обязательно должен быть
     * передан GET-параметром
     * Формируется как MD5 от трех склеенных параметров
     * page, action и вашего ключа apikey
     *
     * @param array $params
     * @return string
     */
    protected function generateHash(array $params)
    {
        if (!in_array($this->token, $params)) {
            $params[] = $this->token;
        }

        return md5(implode('', $params));
    }

    /**
     * Отсылает GET-запрос cURL-ом
     *
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    protected function request(array $params)
    {
        $paramsString = '';

        foreach ($params as $key => $value) {
            $paramsString .= '&' . strtolower($key) . '=' . strtolower($value);
        }

        $paramsString = trim($paramsString, '&');
        $url = $this->domain . '?' . $paramsString;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $result = curl_exec($ch);
        $error = curl_error($ch);

        curl_close($ch);
        if ($error) {
            throw new \Exception($error);
        }

        $result = json_decode($result, true);
        return $result;
    }
}