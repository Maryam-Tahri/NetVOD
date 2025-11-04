<?php

namespace iutnc\netVOD\auth;

use iutnc\netVOD\repository\DeefyRepository;
use iutnc\netVOD\exception\AuthException;
use PDO;
use PDOException;

class AuthzProvider
{
    public static int $ADMIN = 100;
    public static int $USER = 1;

    public static function checkRole(int $role){
        $bdd = DeefyRepository::getInstance()->getPDO();
        $stmt = $bdd->prepare("SELECT role FROM User WHERE id = :id");
        $stmt->bindValue(':id', $_SESSION['user']['id']);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['role'] == $role;
    }

    public static function checkPlaylistOwner(string $playlistId){
        $bdd = DeefyRepository::getInstance()->getPDO();
        $stmt = $bdd->prepare("SELECT id_user FROM user2playlist WHERE id_pl = :id_pl");
        $stmt->bindValue(':id_pl', $playlistId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        try{
            $idUser= AuthnProvider::getSignedInUser();
        }catch (AuthException $e){
            return false;
        }
        return $row['id_user'] == $idUser || self::checkRole(self::$ADMIN);

    }
}