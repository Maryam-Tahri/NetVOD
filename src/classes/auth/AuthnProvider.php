<?php

namespace iutnc\netVOD\auth;

use iutnc\netVOD\repository\NetVODRepo;
use iutnc\netVOD\exception\AuthException;
class AuthnProvider {

    public static function signin(string $email,
                                  string $passwd2check): bool {
        $bdd = NetVODRepo::getInstance()->getPDO();
        $user = $bdd->prepare("SELECT id_user, password, role FROM Users WHERE email = ?");
        $user->bindParam(1, $email);
        $user->execute();
        $row = $user->fetch();
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

    public static function register(string $email,string $passwd): int {
        $bdd = NetVODRepo::getInstance()->getPDO();
        $user = $bdd->prepare("SELECT email FROM Users WHERE email = ?");
        $user->bindParam(1, $email);
        $user->execute();
        $row = $user->fetch();
        if ($row && isset($row['email'])) {
            throw new AuthException("Auth error : email already exists");
        } else {
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
                                $hashed = password_hash($passwd, PASSWORD_DEFAULT, ['cost' => 12]);
                                $user = $bdd->prepare("INSERT INTO Users (email, password, role) VALUES (?, ?, 1)");
                                $user->bindParam(1, $email);
                                $user->bindParam(2, $hashed);
                                // TODO : Ajouter le nom d'utilisateur a la base de donnée
                                $user->execute();
                                return (int)$bdd->lastInsertId();

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
    }

    public static function getSignedInUser(){
        if (isset($_SESSION['user'])) {
            return $_SESSION['user']['id'];
        }else{
            throw new AuthException("Vous n'êtes pas connecter !");
        }
    }
}