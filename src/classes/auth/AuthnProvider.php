<?php

namespace iutnc\netVOD\auth;

use iutnc\netVOD\repository\NetVODRepo;
use iutnc\netVOD\exception\AuthException;

class AuthnProvider
{
    public static function signin(string $email, string $passwd2check): bool
    {
        $bdd = NetVODRepo::getInstance()->getPDO();
        $user = $bdd->prepare("SELECT id_user, password, role FROM Users WHERE email = ?");
        $user->bindParam(1, $email);
        $user->execute();
        $row = $user->fetch();

        if (isset($row['password'])) {
            if (!password_verify($passwd2check, $row['password'])) {
                throw new AuthException("Auth error : invalid credentials");
            }
        } else {
            throw new AuthException("Auth error : invalid credentials");
        }

        $_SESSION['user'] = [
            'id' => $row['id_user'],
            'email' => $email,
            'role' => $row['role']
        ];

        return true;
    }

    public static function register(string $email, string $passwd): int
    {
        $bdd = NetVODRepo::getInstance()->getPDO();

        // Vérifie si l'email existe déjà
        $user = $bdd->prepare("SELECT email FROM Users WHERE email = ?");
        $user->bindParam(1, $email);
        $user->execute();
        $row = $user->fetch();

        if ($row && isset($row['email'])) {
            throw new AuthException("Auth error : email already exists");
        }

        // Vérification de la complexité du mot de passe
        $digit = preg_match("#[\d]#", $passwd);
        $special = preg_match("#[\W]#", $passwd);
        $lower = preg_match("#[a-z]#", $passwd);
        $upper = preg_match("#[A-Z]#", $passwd);

        if (!$digit) throw new AuthException("Pas de chiffre");
        if (!$special) throw new AuthException("Pas de caractère spécial");
        if (!$lower) throw new AuthException("Pas de minuscule");
        if (!$upper) throw new AuthException("Pas de majuscule");
        if (strlen($passwd) < 10) throw new AuthException("Mot de passe d'une longueur de 10 minimum");

        // Insertion de l'utilisateur
        $hashed = password_hash($passwd, PASSWORD_DEFAULT, ['cost' => 12]);
        $insert = $bdd->prepare("INSERT INTO Users (email, password, role) VALUES (?, ?, 1)");
        $insert->bindParam(1, $email);
        $insert->bindParam(2, $hashed);
        $insert->execute();

        // Création automatique d'une liste de favoris vide
        $id_user = (int)$bdd->lastInsertId();
        $liste = $bdd->prepare("INSERT INTO Liste (id_user, type_list) VALUES (?, 'preference')");
        $liste->bindParam(1, $id_user);
        $liste->execute();

        return $id_user;
    }

    public static function getSignedInUser()
    {
        if (isset($_SESSION['user'])) {
            return $_SESSION['user']['id'];
        } else {
            throw new AuthException("Vous n'êtes pas connecté !");
        }
    }
}