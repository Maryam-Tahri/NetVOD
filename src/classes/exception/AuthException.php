<?php

namespace iutnc\netVOD\exception;

class AuthException extends Exception{
    function __construct(String $nom){
        parent::__construct(" $nom");
    }
}