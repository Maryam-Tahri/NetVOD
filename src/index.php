<?php

require_once 'vendor/autoload.php';
use iutnc\deefy\dispatch\Dispatcher;
use iutnc\deefy\repository\DeefyRepository;

try{
    DeefyRepository::setConfig('../../../../db.config.ini');
}catch(Exception $e){
    echo $e->getMessage();
}
session_start();

$dispatcher = new Dispatcher();

$dispatcher->run();


