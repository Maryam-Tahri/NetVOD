<?php

namespace iutnc\netVOD\action;

class DefaultAction extends Action
{
    public  function execute(): String {
        $html = "<h2> Bienvenue sur la page </h2>";
        if (!isset($_SESSION['user']['id'])) {
            $html .=
        }
        return $html;
    }



}