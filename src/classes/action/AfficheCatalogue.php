<?php

namespace iutnc\netVOD\action;

use iutnc\netVOD\render\SerieRenderer;
use iutnc\netVOD\repository\NetVODRepo;

class AfficheCatalogue extends Action
{
    public function execute(): string
    {


        $repo = NetVODRepo::getInstance();
        $series = $repo->getAllSeries();

        $html = '<div class="catalogue-container">';
        $html .= '<h1>Catalogue des séries</h1>';

        if (empty($series)) {
            $html .= '<p class="no-content">Aucune série disponible pour le moment.</p>';
        } else {
            $html .= '<div class="series-grid">';
            foreach ($series as $serie) {
                $renderer = new SerieRenderer();
                $html .= $renderer->render($serie);
            }
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }
}
