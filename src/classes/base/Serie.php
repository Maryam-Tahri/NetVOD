<?php

namespace iutnc\netVOD\base;

use Exception;

class Serie
{
    private String $titre;
    private String $descriptif;
    private int $annee;
    private ?String $genre;
    private ?String $public;
    private String $cheminImg;
    private array $listeEpisodes;

    public function __construct($titre, $descriptif, $annee, $genre, $public, $cheminImg)
    {
        $this->titre = $titre;
        $this->descriptif = $descriptif;
        $this->annee = $annee;
        $this->genre = $genre;
        $this->public = $public;
        $this->cheminImg = $cheminImg;
        $this->listeEpisodes = array();
    }


    public function __get($nom) : mixed{
        if (property_exists($this, $nom)){
            return $this->$nom;
        }else{
            throw new Exception("La variable $nom n'existe pas");
        }
    }
    public function addEpisode(Episode $episode){
        $this->listeEpisodes[] = $episode;
    }

}