<?php

namespace iutnc\netVOD\action;

use Exception;
use iutnc\netVOD\action\Action;
use iutnc\netVOD\repository\NetVODRepo;

class UserProfileSettingsAction extends Action
{

    public function execute(): string
    {
        if ($this->http_method === 'GET') {
            if(!isset($_SESSION['user'])) {
                return "<h3 class='fail'>Erreur !</h3><p>Vous n'êtes actuellement pas connecté. Vous ne pouvez donc pas modifier votre profil</p><a href='?action=signin' class='btn btn-signin'>Se connecter</a>";
            }


            try {
                $userinfo = NetVODRepo::getInstance()->getUserInfosByID($_SESSION['user']['id']);
            } catch (\Exception $e) {
                return "<p class='fail'>Impossible de charger vos informations utilisateur</p><a href='?action=default' class='btn btn-home'>Retour à l'accueil</a>";
            }

            $username = $userinfo['username'] ?? '';
            $nom = $userinfo['nom'] ?? '';
            $prenom = $userinfo['prenom'] ?? '';
            $genre = $userinfo['genre'] ?? '';
            $public_vise = $userinfo['public_vise'] ?? '';

            return <<<HTML
                <h2>Modifier votre compte utilisateur compte utilisateur</h2>
                <form method="post" action="?action=change-user-info">
                    <label for="username">Nom d'utilisateur : </label>
                    <input type="text" name="username" id="username" value="$username" placeholder="GaffeurProfessionnel" required><br>
                    
                    <label>Nom : </label>
                    <input type="text" name="nom" id="nom" value="$nom" placeholder="Lagaffe" required><br>

                    <label>Prénom : </label>
                    <input type="text" name="prenom" id="prenom" value="$prenom" placeholder="Gaston" required><br>
                    
                    <label>Genre préféré : </label>
                    <input type="text" name="genre" id="genre" value="$genre" placeholder="Comedie" required><br>
                    
                    <label>Type de public préféré : </label>
                    <input type="text" name="public_vise" id="public_vide" value="$public_vise" placeholder="Grand Public" required><br>

                    <button type="submit">Modifier</button>
                </form>
            HTML;
        }
        if ($this->http_method === 'POST') {
            $username = filter_var($_POST['username'] ?? '', FILTER_SANITIZE_STRING);
            $nom = filter_var($_POST['nom'] ?? '', FILTER_SANITIZE_STRING);
            $prenom = filter_var($_POST['prenom'] ?? '', FILTER_SANITIZE_STRING);
            $genre = filter_var($_POST['genre'] ?? '', FILTER_SANITIZE_STRING);
            $public_vise = filter_var($_POST['public_vise'] ?? '', FILTER_SANITIZE_STRING);
            try {
                $pdo = NetVODRepo::getInstance()->getPDO();
                $stmt = $pdo->prepare("UPDATE users_infos SET username = ?, nom = ?, prenom = ?, genre = ?, public_vise = ? WHERE id_user = ?");
                $stmt->execute([$username, $nom, $prenom, $genre, $public_vise, $_SESSION['user']['id']]);
            } catch (Exception $e) {
                return $e->getMessage() . "<br><p class='fail'>❌ <b>Impossible</b> de modifier votre <b>compte utilisateur</b></p><br>
                                           <a href='?action=default' class='btn btn-home'>Retour a l'accueil</a>";
            }
            return "<h2>Modifier votre compte utilisateur compte utilisateur</h2><p class='success'>Vos informations ont bien été modifié<br>Nom d'utilisateur : $username<br>Nom : $nom<br>Prénom : $prenom<br>Genre préféré : $genre<br>type de public préféré : $public_vise</p><a href='?action=change-user-info' class='btn'>Changer ses informations</a><br><a href='?action=default' class='btn btn-home'>Retour a l'accueil</a>";
        }
        return "<h2>Erreur</h2><p>Vous venez d'accéder à cette page d'une manière non désirée.</p><br><a href='?action=default' class='btn btn-home'>Retour à l'accueil</a>";
    }
}