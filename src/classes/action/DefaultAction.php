<?php

namespace iutnc\netVOD\action;

use iutnc\netVOD\render\ListRender;
use iutnc\netVOD\repository\NetVODRepo;

class DefaultAction extends Action
{
    public function execute(): String
    {
        $html = "<h2>Bienvenue sur netVOD</h2>";

        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['user']['id'])) {
            return $html . "<p>Veuillez vous connecter pour accéder à vos préférences.</p>";
        }

        $repo = NetVODRepo::getInstance();

        // Récupérer la liste de préférences de l'utilisateur
        $liste = $repo->getListePrefByUser($_SESSION['user']['id']);

        if ($liste === null) {
            return $html . "<p>Vous n'avez pas encore de liste de préférences.</p>";
        }

        $renderer = new ListRender();
        $html = '<div class="liste-detail-container">';
        $html .= $renderer->renderSeriesListe($liste);
        $html .= '</div>';

        return $html;
    }
}