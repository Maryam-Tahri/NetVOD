<?php

namespace iutnc\netVOD\action;

use iutnc\netVOD\repository\NetVODRepo;

class AfficheEpisode extends Action
{
    public function execute(): string
    {

        if (!isset($_GET['id'])) {
            return "<p>Erreur : aucun épisode sélectionné.</p>";
        }

        $idEpisode = (int)$_GET['id'];
        $repo = NetVODRepo::getInstance();

        $episode = $repo->getEpisodeById($idEpisode);

        if ($episode === null) {
            return "<p>Erreur : épisode introuvable.</p>";
        }

        $html = "<div class='episode-detail'>";
        $html .= "<h2>{$episode->titre}</h2>";
        $html .= "<img src='{$episode->cheminImg}' alt='Image de l’épisode'>";
        $html .= "<p><strong>Résumé :</strong> {$episode->resume}</p>";
        $html .= "<p><strong>Durée :</strong> {$episode->duree} min</p>";
        $html .= "</div>";

        return $html;
    }
}