<?php

namespace iutnc\netVOD\action;

use iutnc\netVOD\action\Action;
use iutnc\netVOD\auth\AuthnProvider;
use iutnc\netVOD\exception\AuthException;

class CreateUser extends Action
{

    public function execute(): string
    {
        if ($this->http_method === 'GET') {
            return <<<HTML
                <h2>Créer un compte utilisateur</h2>
                <form method="post" action="?action=add-user">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" name="username" id="username" required>
                    
                    <label>Email :</label>
                    <input type="email" name="email" id="email" required><br>

                    <label>Mot de passe :</label>
                    <input type="password" name="passwd" id="passwd" placeholder="MotDeP@sse123" title="1 Majuscule, 1 minuscule, 1 chiffre et 1 charactère spécial minimum + Taille mot de passe 10 minimum" required><br>

                    <button type="submit">Inscription</button>
                </form>
            HTML;
        }

        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $passwd = $_POST['passwd'] ?? '';

        try {
            $id = AuthnProvider::register($email, $passwd);
            AuthnProvider::signin($email, $passwd);
            return "<p>✅ Inscription réussie (ID $id) 🎉. Vous êtes maintenant connecté 👍.</p>
                    <a href='?action=default' class='btn btn-blue'>Retour à l'accueil</a>";
        } catch (AuthException $e) {
            return "<p>❌ " . htmlspecialchars($e->getMessage()) . " ❌</p><a href='?action=add-user' class='btn btn-retry'>Réessayer</a>";
        }
    }
}