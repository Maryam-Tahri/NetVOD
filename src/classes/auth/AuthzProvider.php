<?php

namespace iutnc\netVOD\auth;

use iutnc\netVOD\exception\AuthzException;
use iutnc\netVOD\repository\NetVODRepo;
use iutnc\netVOD\exception\AuthException;
use PDO;
use PDOException;

class AuthzProvider
{
    public static int $ADMIN = 100;
    public static int $USER = 1;

    public static function checkRole(int $role) : int {
        $bdd = NetVODRepo::getInstance()->getPDO();
        $stmt = $bdd->prepare("SELECT role FROM Users WHERE id = :id");
        $stmt->bindValue(':id', $_SESSION['user']['id']);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['role'] == $role;
    }

    public static function checkListOwner(int $listId) : void{
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!isset($_SESSION['user'])) throw new AuthzException("Non connecté.");

        $user = $_SESSION['user'];
        $userId = (int)$user['id'];
        $role = (int)$user['role'];

        $repo = NetVODRepo::getInstance();
        $owner = $repo->getListOwner($listId);

        if ($owner === null) throw new AuthzException("Liste inconnue.");
        if ($owner !== $userId && $role !== 100)
            throw new AuthzException("Accès refusé : vous n'êtes pas propriétaire.");
    }
}