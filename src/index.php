<?php

require_once 'vendor/autoload.php';
use iutnc\netVOD\dispatch\Dispatcher;
use iutnc\netVOD\repository\NetVODRepo;

try{
    NetVODRepo::setConfig('../../../../db.config.ini');
}catch(Exception $e){
    echo $e->getMessage();
}
session_start();

$dispatcher = new Dispatcher();

$dispatcher->run();


