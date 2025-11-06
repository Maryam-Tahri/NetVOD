<?php

namespace iutnc\netVOD\action;

use DateTime;
use iutnc\netVOD\action\Action;
use iutnc\netVOD\auth\AuthnProvider;
use iutnc\netVOD\repository\NetVODRepo;

class ActivateAccountAction extends Action
{
    public function execute(): string
    {
        if ($this->http_method === 'GET') {
            $token = $_GET['token'] ?? '';

            if (!$token) return "<p class='fail'>❌ Lien invalide : aucun token fourni.</p>";

            $pdo = NetVODRepo::getInstance()->getPDO();

            $stmt = $pdo->prepare("SELECT id_user, expiration_token FROM Tokens WHERE token = ?");
            $stmt->execute([$token]);
            $row = $stmt->fetch();

            if (!$row) {
                return "<p class='fail'>❌ Token inconnu ou déjà utilisé.</p>";
            }

            $now = new DateTime();
            $expiration = new DateTime($row['expiration_token']);

            if ($now > $expiration) {
                $delete = $pdo->prepare("DELETE FROM Tokens WHERE token = ?");
                $delete->execute([$token]);

                return "<p class='fail'>Ce lien d’activation a expiré. Merci de vous réinscrire.</p>";
            }

            $id_user = $row['id_user'];
            $update = $pdo->prepare("UPDATE Users SET is_active = 1 WHERE id_user = ?");
            $update->execute([$id_user]);

            $delete = $pdo->prepare("DELETE FROM Tokens WHERE token = ?");
            $delete->execute([$token]);

            return "<p>✅ Inscription réussie (ID $id_user) 🎉. Vous pouvez maintenant vous connecté 👍.</p>
                    <a href='?action=signin' class='btn btn-confirm'>Se connecter</a>
                    <a href='?action=signin' class='btn btn-home'>Retour à l'accueil</a>";
        }
        return "<h2>Erreur</h2><p>Vous venez d'accéder à cette page d'une manière non désirée.</p><a href='?action=default' class='btn btn-home'>Retour à l'accueil</a>";
    }
}