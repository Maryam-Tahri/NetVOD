<?php

namespace iutnc\netVOD\render;

use iutnc\netVOD\base\Serie;
use iutnc\netVOD\base\Liste;
use iutnc\netVOD\repository\NetVODRepo;
use iutnc\netVOD\render\SerieRenderer;

class ListRender
{
    public function renderListe(Liste $liste): string
    {
        $type = htmlspecialchars($liste->type_list);
        $id_liste = $liste->id_liste;

        $html = <<<HTML
        <div class="list-card">
                    <a href="?action=liste&id=$id_liste">
                <h2>$type</h2>
            </a>
        </div>
        HTML;

        return $html;
    }


    public function renderSeriesListe(Liste $liste): string
    {
        $typeListe = htmlspecialchars($liste->type_list);
        $idListe = $liste->id_liste;

        // Récupérer les séries de cette liste depuis le repository
        $repo = NetVODRepo::getInstance();
        $series = $repo->getSeriesByListe($idListe);

        // Si la liste est vide
        if (empty($series)) {
            return <<<HTML
        <div class='liste-detail'>
            <h2>Liste : $typeListe</h2>
            <p>Cette liste ne contient aucune série pour le moment.</p>
        </div>
        HTML;
        }

        // Construire le HTML pour chaque série
        $seriesHtml = '';
        foreach ($series as $serie) {
            $renderer = new SerieRenderer();
            $seriesHtml .= $renderer->render($serie);
        }

        $html = <<<HTML
    <div class='liste-detail'>
        <h2>Ma liste : $typeListe</h2>
        <div class='series-grid'>
            $seriesHtml
        </div>
    </div>
    HTML;

        return $html;
    }
}