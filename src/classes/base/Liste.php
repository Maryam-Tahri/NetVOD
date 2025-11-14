<?php

namespace iutnc\netVOD\base;

use Exception;

class Liste
{

    private int $id_liste;
    private int $id_user;
    private String $type_list;

    public function __construct($id_liste, $id_user, $type_list)
    {
        $this->id_liste = $id_liste;
        $this->id_user = $id_user;
        $this->type_list = $type_list;
    }


    public function __get($nom): mixed
    {
        if (property_exists($this, $nom)) {
            return $this->$nom;
        } else {
            throw new Exception("La variable $nom n'existe pas");
        }
    }
}