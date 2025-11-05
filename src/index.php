<?php

require_once 'vendor/autoload.php';

use iutnc\netVOD\base\Serie;
use iutnc\netVOD\dispatch\Dispatcher;
use iutnc\netVOD\render\SerieRenderer;
use iutnc\netVOD\repository\NetVODRepo;

try{
    NetVODRepo::setConfig(__DIR__ . '/db.config.ini');
}catch(Exception $e){
    echo $e->getMessage();
}
session_start();

$dispatcher = new Dispatcher();

$dispatcher->run();

$test = new \iutnc\netVOD\base\Serie("joie", "que je doie", 2025, "comédie", "adulte", "img/joie.jpg");
$renderer = new SerieRenderer();
echo $renderer->render($test);