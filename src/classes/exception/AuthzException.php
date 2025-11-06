<?php

namespace iutnc\netVOD\exception;

use Exception;

class AuthzException extends Exception {
    function __construct(String $nom){
        parent::__construct(" $nom");
    }
}