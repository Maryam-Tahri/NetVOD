<?php

namespace iutnc\netVOD\action;

use DateTime;
use iutnc\netVOD\action\Action;
use iutnc\netVOD\auth\AuthnProvider;
use iutnc\netVOD\exception\AuthException;
use iutnc\netVOD\repository\NetVODRepo;

class ResetPasswdAction extends Action
{

    public function execute(): string
    {
        if ($this->http_method === 'GET') {
            $token = $_GET['token'] ?? '';
            if (!$token) return "<p class='fail'>Lien invalide : aucun token fourni.</p><br><a href='?action=default' class='btn btn-home'>Retour à l'accueil</a>";

            return <<<HTML
                <h2>Réinitialiser son mot de passe</h2>
                <form method="post" action="?action=reset-passwd&token=$token">
                    <label>Mot de passe :</label>
                    <input type="password" name="passwd" id="passwd" placeholder="MotDeP@sse123" title="1 Majuscule, 1 minuscule, 1 chiffre et 1 charactère spécial minimum + Taille mot de passe 10 minimum" required><br>

                    <button type="submit">Réinitialiser</button>
                </form>
            HTML;
        }

        if ($this->http_method === 'POST') {
            $token = $_GET['token'] ?? '';
            $email = $_SESSION['form_data_tmp']['email'] ?? '';

            if (!$token) return "<p class='fail'>Lien invalide : aucun token fourni.</p><br><a href='?action=default' class='btn btn-home'>Retour à l'accueil</a>";

            $pdo = NetVODRepo::getInstance()->getPDO();

            $stmt = $pdo->prepare("SELECT id_user, expiration_token FROM Tokens WHERE token = ?");
            $stmt->execute([$token]);
            $row = $stmt->fetch();

            if (!$row) {
                return "<p class='fail'>Lien invalide : le token fourni n'exite plus.</p><a href='?action=default' class='btn btn-home'>Retour à l'accueil</a>";
            }

            $id_user = $row['id_user'];

            $now = new DateTime();
            $expiration = new DateTime($row['expiration_token']);

            if ($now > $expiration) {
                $deleteUser = $pdo->prepare("DELETE FROM Users WHERE id_user = ?");
                $deleteUser->execute([$row['id_user']]);

                $delete = $pdo->prepare("DELETE FROM Tokens WHERE token = ?");
                $delete->execute([$token]);

                return "<p class='fail'>Ce lien d’activation a expiré. Merci de vous réinscrire.</p><br><a href='?action=add-user' class='btn'>Inscription - Réessayer</a>";
            }

            $update = $pdo->prepare("UPDATE Users SET is_active = 1 WHERE id_user = ?");
            $update->execute([$id_user]);

            $delete = $pdo->prepare("DELETE FROM Tokens WHERE token = ?");
            $delete->execute([$token]);

            $passwd = $_POST['passwd'] ?? '';

            try {
                if (isset($_SESSION['form_data_tmp'])) {
                    if (AuthnProvider::passwdVerify($passwd))
                    unset($_SESSION['form_data_tmp']);
                    $hash = AuthnProvider::provideHashedPassword($passwd);
                    $majPasswd = $pdo->prepare("UPDATE Users SET password = ? WHERE id_user = ?");
                    $majPasswd->execute([$hash, $id_user]);
                }
            } catch (AuthException $e) {
                $toShow = "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
                if (strpos($e->getMessage(), "email"))
                    $toShow .= "<a href='?action=signin' class='btn btn-signin'>Se connecter</a><br>";
                $toShow .= "<a href='?action=add-user' class='btn btn-retry'>Réessayer</a>";
                return $toShow;
            }

            return "<p class='success'>Mot de Passe modifié. Vous pouvez maintenant vous connecter.</p>
                    <a href='?action=signin' class='btn btn-confirm'>Se connecter</a>
                    <a href='?action=signin' class='btn btn-home'>Retour à l'accueil</a>";
        }
        return "<h2>Erreur</h2><p>Vous venez d'accéder à cette page d'une manière non désirée.</p><br><a href='?action=default' class='btn btn-home'>Retour à l'accueil</a>";
    }
}