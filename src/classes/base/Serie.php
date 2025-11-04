<?php

namespace iutnc\netVOD\base;

class Serie
{
    private String $titre;
    private String $descriptif;
    private int $annee;
    private String $genre;
    private String $public;
    private String $cheminImg;

    public function __construct($titre, $descriptif, $annee, $genre, $public, $cheminImg)
    {
        $this->titre = $titre;
        $this->descriptif = $descriptif;
        $this->annee = $annee;
        $this->genre = $genre;
        $this->public = $public;
        $this->cheminImg = $cheminImg;
    }


}