<?php

namespace iutnc\netVOD\action;

use iutnc\netVOD\render\ListRender;
use iutnc\netVOD\repository\NetVODRepo;

class AfficheListe extends Action
{
    public function execute(): string
    {
        if (!isset($_GET['id'])) {
            return "<p>Erreur : aucune liste sélectionnée.</p>";
        }

        $idListe = (int)$_GET['id'];
        $repo = NetVODRepo::getInstance();
        $liste = $repo->getListeById($idListe);

        if ($liste === null) {
            return "<p>Erreur : liste introuvable.</p>";
        }

        $renderer = new ListRender();
        $html = '<div class="liste-detail-container">';
        $html .= $renderer->renderSeriesListe($liste);
        $html .= '</div>';

        return $html;
    }
}
