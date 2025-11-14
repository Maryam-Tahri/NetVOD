<?php

namespace iutnc\netVOD\auth;

use iutnc\netVOD\repository\NetVODRepo;
use iutnc\netVOD\exception\AuthException;

class AuthnProvider
{
    public static function signin(string $email, string $passwd2check): bool
    {
        $bdd = NetVODRepo::getInstance()->getPDO();
        $user = $bdd->prepare("SELECT id_user, password, role, is_active FROM Users WHERE email = ?");
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

        if (!$row) {
            throw new AuthException("Auth error : Aucun email correspondant");
        }

        if (!$row['is_active']) {
            throw new AuthException("Auth error : Votre compte n'est pas activé avec l'email " . $email, 1);
        } else {
            if (isset($row['password'])) {
                if (!password_verify($passwd2check, $row['password'])) {
                    throw new AuthException("Auth error : invalid credentials");
                }
            }else{
                throw new AuthException("Auth error : invalid credentials");
            }
            $_SESSION['user'] = [
                'id' => $row['id_user'],
                'email' => $email,
                'role' => $row['role']
            ];
            return true;
        }
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
        } else {
            try {
                if (AuthnProvider::passwdVerify($passwd)) {
                    $hashed = AuthnProvider::provideHashedPassword($passwd);
                    $user = $bdd->prepare("INSERT INTO Users (email, password, role) VALUES (?, ?, 1)");
                    // TODO : Ajouter le nom d'utilisateur a la base de donnée
                    $user->execute([$email, $hashed]);
                    return (int)$bdd->lastInsertId();
                } else {
                    throw new AuthException("Auth error : Une erreur est survenu");
                }
            } catch (AuthException $e) {
                throw new AuthException($e->getMessage());
            }
        }
    }

    public static function provideHashedPassword(string $passwd): string {
        return password_hash($passwd, PASSWORD_DEFAULT, ['cost' => 12]);
    }

    public static function passwdVerify(string $passwd): bool {
        $digit = preg_match("#[\d]#", $passwd); // au moins un digit
        $special = preg_match("#[\W]#", $passwd); // au moins un car. spécial
        $lower = preg_match("#[a-z]#", $passwd); // au moins une minuscule
        $upper = preg_match("#[A-Z]#", $passwd); // au moins une majuscule
        if ($digit) {
            if ($special) {
                if ($lower) {
                    if ($upper) {
                        if (strlen($passwd) < 10) {
                            throw new AuthException("Mot de passe d'une longueur de 10 minimum");
                        } else {
                            return true;
                        }
                    } else {
                        throw new AuthException("Pas de Majuscule");
                    }
                } else {
                    throw new AuthException("Pas de miniscule");
                }
            } else {
                throw new AuthException("Pas de charactère special");
            }
        } else {
            throw new AuthException("Pas de chiffre");
        }
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