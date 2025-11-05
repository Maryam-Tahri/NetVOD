<?php

namespace iutnc\netVOD\action;

use iutnc\netVOD\repository\NetVODRepo;
use Exception;

class AjouterFavoris extends Action
{
    public function execute(): string
    {
        // Vérifier que c'est une requête POST
        if ($this->http_method !== 'POST') {
            return "<div class='error'>Méthode non autorisée</div>";
        }

        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            return "<div class='error'>Vous devez être connecté pour ajouter aux favoris</div>";
        }

        // Récupérer et valider l'ID de l'épisode
        $id_episode = filter_var($_POST['id_episode'] ?? 0, FILTER_VALIDATE_INT);

        if (!$id_episode || $id_episode <= 0) {
            return "<div class='error'>ID d'épisode invalide</div>";
        }

        // Appeler la méthode SaveFavourite
        try {
            $repo = NetVODRepo::getInstance();
            $repo->SaveFavourite($_SESSION['user_id'], $id_episode);

            return "<div class='success'>Épisode ajouté aux favoris !</div>";
        } catch (Exception $e) {
            return "<div class='error'>Erreur : " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}
