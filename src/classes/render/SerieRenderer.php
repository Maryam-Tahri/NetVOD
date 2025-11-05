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
        $titre = $serie->__get('titre');
        $stmt->bindParam(':titre',$titre);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $note = $row['avg(note)']=="" ? 'pas de note' : $row['avg(note)'];

        $titre = $serie->titre;
        $img =$serie->cheminImg;

        $html = <<<HTML
        <div class="serie-card">
            <img class="serie" src="$img" alt="$titre">
            <h2>$titre ($note)</h2>
        </div>
        HTML;

        return $html;
    }
}