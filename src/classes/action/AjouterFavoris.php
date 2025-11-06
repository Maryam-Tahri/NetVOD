<?php

namespace iutnc\netVOD\action;

use iutnc\netVOD\repository\NetVODRepo;
use Exception;

class AjouterFavoris extends Action
{
    public function execute(): string
    {
        if ($this->http_method !== 'POST') {
            return "<div class='error'>Méthode non autorisée</div>";
        }

        if (!isset($_SESSION['user']['id'])) {
            return "<div class='error'>Vous devez être connecté pour ajouter aux favoris</div>";
        }

        $id_serie = filter_var($_POST['id_serie'] ?? 0, FILTER_VALIDATE_INT);

        if (!$id_serie || $id_serie <= 0) {
            return "<div class='error'>ID de série invalide</div>";
        }

        try {
            $repo = NetVODRepo::getInstance();
            $bdd = $repo->getPDO();

            $checkSerie = $bdd->prepare("SELECT id_serie, titre_serie FROM serie WHERE id_serie = ?");
            $checkSerie->execute([$id_serie]);
            $serie = $checkSerie->fetch(\PDO::FETCH_ASSOC);

            if (!$serie) {
                return "<div class='error'>Cette série n'existe pas</div>";
            }

            $getList = $bdd->prepare("SELECT id_list FROM Liste WHERE id_user = ? AND type_list = 'preference'");
            $getList->execute([$_SESSION['user']['id']]);
            $liste = $getList->fetch(\PDO::FETCH_ASSOC);

            if (!$liste) {
                return "<div class='error'>Liste de favoris non trouvée</div>";
            }

            $id_list = $liste['id_list'];

            $checkExist = $bdd->prepare("SELECT * FROM list2serie WHERE id_list = ? AND id_serie = ?");
            $checkExist->execute([$id_list, $id_serie]);

            if ($checkExist->fetch()) {
                return "<div class='error'>Cette série est déjà dans vos favoris</div>";
            }

            $insert = $bdd->prepare("INSERT INTO list2serie (id_list, id_serie) VALUES (?, ?)");
            $insert->execute([$id_list, $id_serie]);

            return "<div class='success'>Série \"" . htmlspecialchars($serie['titre_serie']) . "\" ajoutée aux favoris !</div>";

        } catch (Exception $e) {
            return "<div class='error'>Erreur : " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}