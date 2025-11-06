<?php

namespace iutnc\netVOD\action;

use iutnc\netVOD\render\SerieRenderer;
use iutnc\netVOD\repository\NetVODRepo;

class AfficheCatalogue extends Action
{
    public function execute(): string
    {
        $html = '<div class="catalogue-container">';
        $html .= '<h1>Catalogue des séries</h1>';


        $searchValue = $_GET['search'] ?? '';
        $html .= <<<HTML
<form method="POST" action="?action=catalogue">
    <input type="text" name="search" placeholder="Rechercher" value="$searchValue">
    <button type="submit">Rechercher</button>
</form>
HTML;

        $repo = NetVODRepo::getInstance();


        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $series = $repo->getAllSeries();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $search = $_POST['search'] ?? '';
            $series = $repo->getAllSeries($search);
        }

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
