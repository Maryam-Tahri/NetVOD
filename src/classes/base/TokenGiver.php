<?php

namespace iutnc\netVOD\base;

use Exception;
use iutnc\netVOD\action\Action;
use iutnc\netVOD\exception\TokenException;
use iutnc\netVOD\repository\NetVODRepo;
use PDO;

class TokenGiver {
    public static function createToken(int $id_user, int $delay = 300) : string {
        if (gettype($delay) != 'integer') {
            $delay = 300; // Remise par défaut si ce n'est pas un int : protection
        }

        try {
            $pdo = NetVODRepo::getInstance()->getPDO();
        } catch (Exception $e) {
            throw new TokenException("TokenGiver Exception : Impossible de créer le token: " . $e->getMessage());
        }

        $suppToken = $pdo->prepare("DELETE FROM tokens WHERE id_user = ?");
        $suppToken->execute([$id_user]);

        // $token = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT); // Pas besoin
        try {
            $token = bin2hex(random_bytes(16));
        } catch (Exception $e) {
            throw new TokenException("TokenGiver Exception : Impossible de créer le token" . $e->getMessage());
        }

        $expiration = date('Y-m-d H:i:s', time() + $delay);

        $stmt = $pdo->prepare("INSERT INTO tokens (token, id_user, expiration_token) VALUES (?, ?, ?)");
        $stmt->execute([$token, $id_user, $expiration]);
        return $token;
    }
}