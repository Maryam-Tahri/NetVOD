<?php

namespace iutnc\netVOD\action;

class DefaultAction extends Action
{
    public  function execute(): String {
        return "<h2> Bienvenue sur la page </h2>";
    }

}