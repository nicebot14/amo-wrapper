<?php
/**
 * Created by PhpStorm.
 * User: frexin
 * Date: 25.01.2017
 * Time: 17:19
 */

require_once "../src/Worksection/WorksectionClient.php";

$ws = new \AmoWrapper\Worksection\WorksectionClient('https://logomachine.worksection.com/api/admin/', '0801e92fbdc7f405b4facc5413142638');
/*$tasks = $ws->createProjectWithTasks(
    $params = [
        'title' => 'СуперПроект 2',
        'dateend' => '12.12.2018',
    ]);*/

//$data = $ws->getTasks('/project/164811/');
//var_dump($data);
$data = $ws->getComments('/project/164811/4469762/');

print json_encode($data);
