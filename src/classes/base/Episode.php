<?php

namespace iutnc\netVOD\base;

use Exception;

class Episode
{
    private int $numEpisode;
    private String $titre;
    private String $resume;
    private int $duree;
    private ?String $cheminImg;
    private String $chemin;

    public function __construct(int $numEpisode, string $titre, string $resume, int $duree, ?String $cheminImg, $chemin){
        $this->numEpisode = $numEpisode;
        $this->titre = $titre;
        $this->resume = $resume;
        $this->duree = $duree;
        $this->cheminImg = $cheminImg;
        $this->chemin = $chemin;

    }

    public function __get($nom){
        if (property_exists($this, $nom)){
            return $this->$nom;
        }else{
            throw new \Exception("La variable $nom n'existe pas");
        }
    }

}