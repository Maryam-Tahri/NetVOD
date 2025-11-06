<?php

namespace iutnc\netVOD\action;

use iutnc\netVOD\render\SerieRenderer;
use iutnc\netVOD\repository\NetVODRepo;

class AfficheSerie extends Action
{
    public function execute(): string
    {
        if (!isset($_GET['id'])) {
            return "<p>Erreur : aucune série sélectionnée.</p>";
        }

        $idSerie = (int)$_GET['id'];
        $repo = NetVODRepo::getInstance();
        $serie = $repo->getSerieById($idSerie);

        if ($serie === null) {
            return "<p>Erreur : série introuvable.</p>";
        }
        $_SESSION['serie'] = serialize($serie);

        $renderer = new SerieRenderer();
        $html = '<div class="serie-detail-container">';
        $html .= $renderer->renderSerieEpisode($serie);
        $html .= '</div>';

        return $html;
    }
}