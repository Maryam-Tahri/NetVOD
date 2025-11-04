<?php

require_once 'vendor/autoload.php';

use iutnc\netVOD\base\Serie;
use iutnc\netVOD\dispatch\Dispatcher;
use iutnc\netVOD\repository\DeefyRepository;

try{
    DeefyRepository::setConfig('../../../../db.config.ini');
}catch(Exception $e){
    echo $e->getMessage();
}
session_start();

$dispatcher = new Dispatcher();

$dispatcher->run();





