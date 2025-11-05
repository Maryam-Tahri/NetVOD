<?php

namespace iutnc\netVOD\render;

use iutnc\netVOD\base\Serie;

class SerieRenderer
{
    public function render(Serie $serie): string
    {
        $titre = $serie->titre;
        $img =$serie->cheminImg;

        $html = <<<HTML
        <div class="serie-card">
            <img class="serie" src="$img" alt="$titre">
            <h2>$titre</h2>
        </div>
        HTML;

        return $html;
    }

    public function renderSerieEpisode(Serie $serie): string {
        $html = "<div class='serie-detail'>";
        $html .= "<h2>{$serie->titre}</h2>";
        $html .= "<p><strong>Genre :</strong> {$serie->genre}</p>";
        $html .= "<p><strong>Public visé :</strong> {$serie->public}</p>";
        $html .= "<p><strong>Descriptif :</strong> {$serie->descriptif}</p>";
        $html .= "<p><strong>Année de sortie :</strong> {$serie->annee}</p>";
        $html .= "<p><strong>Nombre d’épisodes :</strong> " . count($serie->listeEpisodes) . "</p>";

        $html .= "<h3>Liste des épisodes :</h3><div class='episodes'>";
        foreach ($serie->listeEpisodes as $ep) {
            $html .= "
                <div class='episode'>
                    <img src='{$ep->cheminImg}' alt='Image de l’épisode'>
                    <p><strong>Épisode {$ep->numEpisode} : {$ep->titre}</strong></p>
                    <p>Durée : {$ep->duree} min</p>
                </div>
            ";
        }
        $html .= "</div></div>";

        return $html;
    }
}