<?php

namespace iutnc\netVOD\action;

use iutnc\netVOD\action\Action;
use iutnc\netVOD\base\TokenGiver;
use iutnc\netVOD\exception\TokenException;
use iutnc\netVOD\repository\NetVODRepo;

class ResetPasswdTokenAction extends Action
{

    public function execute(): string
    {
        if ($this->http_method === 'GET') {
            return <<<HTML
                <h2>Réinitialiser son mot de passe</h2>
                <p>Renseigné l'adresse mail</p>
                <form method="post" action="?action=reset-passwd-token">
                    <label>Email :</label>
                    <input type="email" name="email" id="email" placeholder="user@mail.com" required><br>

                    <button type="submit">Réinitialiser le mot de passe</button>
                </form>
            HTML;
        }
        if ($this->http_method === 'POST') {
            $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
            try {
                $pdo = NetVODRepo::getInstance();
            } catch (Exception $e) {
                return "<p>Impossible de créer un jeton de récupération de mot de passe.</p><a href='?action=default' class='btn btn-home'>Retour à l'accueil</a>";
            }
            $id_user = $pdo->getUserIdByEmail($email);
            if ($id_user === -1) {
                return "<p>il existe aucun compte utilisateur avec l'adresse mail <b>$email</b></p>";
            }
            $token = TokenGiver::createToken($id_user);
            $_SESSION['form_data_tmp'] = [
                'email' => $_POST['email'] ?? ''
            ];
            return <<<HTML
                <h2>Réinitialiser son mot de passe</h2>
                <p>Cliqué sur le lien ci-dessous pour réinitialiser votre mot de passe.</p>
                <a href="?action=reset-passwd&token=$token" class="btn btn-confirm">Réinitialiser son mot de passe</a>
            HTML;
        }

        return "<p class='error'>Méthode HTTP non supportée.</p>";
    }
}