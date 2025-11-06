<?php

namespace iutnc\netVOD\action;

use DateTime;
use iutnc\netVOD\action\Action;
use iutnc\netVOD\auth\AuthnProvider;
use iutnc\netVOD\exception\AuthException;
use iutnc\netVOD\repository\NetVODRepo;

class ActivateAccountAction extends Action
{
    public function execute(): string
    {
        if ($this->http_method === 'GET') {
            $token = $_GET['token'] ?? '';

            if (!$token) return "<p class='fail'>❌ Lien invalide : aucun token fourni.</p><br><a href='?action=default' class='btn btn-home'>Retour à l'accueil</a>";

            $pdo = NetVODRepo::getInstance()->getPDO();

            $stmt = $pdo->prepare("SELECT id_user, expiration_token FROM Tokens WHERE token = ?");
            $stmt->execute([$token]);
            $row = $stmt->fetch();

            if (!$row) {
                return "<p class='fail'>❌ Lien invalide : le token fourni n'exite plus.</p><a href='?action=default' class='btn btn-home'>Retour à l'accueil</a>";
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

            return "<p>✅ Inscription réussie (ID $id_user) 🎉. Vous pouvez maintenant vous connecté 👍.</p>
                    <a href='?action=signin' class='btn btn-confirm'>Se connecter</a>
                    <a href='?action=signin' class='btn btn-home'>Retour à l'accueil</a>";
        }
        return "<h2>Erreur</h2><p>Vous venez d'accéder à cette page d'une manière non désirée.</p><br><a href='?action=default' class='btn btn-home'>Retour à l'accueil</a>";
    }
}