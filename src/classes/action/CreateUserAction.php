<?php

namespace iutnc\netVOD\action;

use Exception;
use iutnc\netVOD\action\Action;
use iutnc\netVOD\auth\AuthnProvider;
use iutnc\netVOD\base\TokenGiver;
use iutnc\netVOD\exception\AuthException;
use iutnc\netVOD\exception\TokenException;
use iutnc\netVOD\repository\NetVODRepo;
use PDOException;

class CreateUserAction extends Action
{

    public function execute(): string
    {
        if ($this->http_method === 'GET') {
            // Récupération puis suppression pour une utilisation unique
            $username = htmlspecialchars($_SESSION['form_data_tmp']['username'] ?? '');
            $email = htmlspecialchars($_SESSION['form_data_tmp']['email'] ?? '');
            if(isset($_SESSION['form_data_tmp']))
                unset($_SESSION['form_data_tmp']);

            return <<<HTML
                <h2>Créer un compte utilisateur</h2>
                <form method="post" action="?action=add-user">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" name="username" id="username" value="$username" required><br>
                    
                    <label>Email :</label>
                    <input type="email" name="email" id="email" value="$email" required><br>

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

            // Creation du token pour valider l'email
            try {
                $token = TokenGiver::createToken($id);
            } catch (Exception $e) {
                return $e->getMessage() . "<br><p class='fail'>❌ <b>Impossible</b> d'initialiser un <b>token</b> pour valider votre <b>email</b>.</p><br>
                                           <a href='?action=default' class='btn btn-home'>Retour a l'accueil</a>";
            }

            // Création des trois listes vide associer à l'utilisateur
            try {
                $pdo = NetVODRepo::getInstance()->getPDO();
                $stmt = $pdo->prepare("INSERT INTO Liste (id_user, type_list) VALUES 
                                                    (:id_user, 'preference'),
                                                    (:id_user, 'en_cours'),
                                                    (:id_user, 'deja_visionne')");
                $stmt->execute(['id_user' => $id]);
            } catch (Exception $e) {
                return $e->getMessage() . "<br><p class='fail'>❌ <b>Impossible</b> de créer votre <b>compte utilisateur</b></p><br>
                                           <a href='?action=default' class='btn btn-home'>Retour a l'accueil</a>";
            }

            return "<a href='?action=activate-account&token=$token'  class='btn btn-confirm'>Activer votre compte</a><p>Attention, valable que 5 minutes.</p>";
        } catch (AuthException $e) {
            $_SESSION['form_data_tmp'] = [
                'username' => $_POST['username'] ?? '',
                'email' => $_POST['email'] ?? ''
            ];
            $toShow = "<p>❌ " . htmlspecialchars($e->getMessage()) . " ❌</p>";
            if (strpos($e->getMessage(), "email"))
                $toShow .= "<a href='?action=signin' class='btn btn-signin'>Se connecter</a><br>";
            $toShow .= "<a href='?action=add-user' class='btn btn-retry'>Réessayer</a>";
            return $toShow;
        }
    }
}