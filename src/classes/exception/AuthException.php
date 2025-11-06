<?php

namespace iutnc\netVOD\exception;

use Exception;

class AuthException extends Exception{

    /*
     * 0 = Pas d'erreur spécifiée
     * 1 = Email pas activé
     */
    private int $typeError;
    function __construct(String $nom, int $typeError = 0){
        parent::__construct(" $nom");
        $this->typeError = $typeError;
    }

    public function getTypeError(){
        return $this->typeError;
    }
}