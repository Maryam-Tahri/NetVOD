<?php

namespace iutnc\netVOD\exception;

use Exception;

class TokenException extends Exception {
    function __construct(String $nom){
        parent::__construct(" $nom");
    }
}