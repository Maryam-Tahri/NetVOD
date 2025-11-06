<?php

namespace iutnc\netVOD\render;

use iutnc\netVOD\base\Serie;
use iutnc\netVOD\repository\NetVODRepo;
use PDO;

class SerieRenderer
{
    public function render(Serie $serie): string
    {
        $stmt = NetVODRepo::getInstance()->getPDO()->prepare("SELECT avg(note) FROM commentaire INNER JOIN episode ON episode.id_ep = commentaire.id_ep
                                                                      INNER JOIN serie ON episode.id_serie = serie.id_serie
                                                                      WHERE serie.titre_serie = :titre");
        $titre = htmlspecialchars($serie->titre);
        $img = htmlspecialchars($serie->cheminImg);
        $id = $serie->id;
        $stmt->bindParam(':titre',$titre);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $note = $row['avg(note)']=="" ? 'pas de note' : $row['avg(note)'];



        $html = <<<HTML
        <div class="serie-card">
            <img class="serie" src="$img" alt="$titre">
            <h2>$titre ($note)</h2>
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
            $html .= <<<HTML
            <div class='episode'>
            <a href='?action=episode&id={$ep->numEpisode}'>
                <img src='{$ep->cheminImg}' alt='Image de l’épisode'>
                <p><strong>Épisode {$ep->numEpisode} : {$ep->titre}</strong></p>
                <p>Durée : {$ep->duree} min</p>
            </a>
        </div>
    HTML;
        }

        return $html;
    }
}