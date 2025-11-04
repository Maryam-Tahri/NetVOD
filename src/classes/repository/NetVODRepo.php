<?php

namespace iutnc\netVOD\repository;
use Exception;
use PDO;

class NetVODRepo
{
    private PDO $pdo;
    private static ?NetVODRepo $instance = null;
    private static array $config = [];

    private function __construct(array $conf) {
        $this->pdo = new PDO($conf['dsn'], $conf['user'], $conf['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    }

    public static function getInstance(): NetVODRepo {
        if (self::$instance === null) {
            if (empty(self::$config)) {
                throw new Exception("Configuration non définie ! Appelle d'abord DeefyRepository::setConfig().");
            }

            self::$instance = new NetVODRepo(self::$config);
        }
        return self::$instance;
    }

    public static function setConfig(string $file) {
        $conf = parse_ini_file($file);
        if ($conf === false) {
            throw new Exception("Error reading configuration file");
        }
        $conf = parse_ini_file($file);
        $dsn = "{$conf['driver']}:host={$conf['host']};dbname={$conf['database']}";
        self::$config = ['dsn'=> $dsn, 'user'=> $conf['username'], 'pass'=> $conf['password']];
    }

    public function getPDO(): PDO {
        return $this->pdo;
    }

    public function SaveFavourite(int $id_user,int $_id_episode) {
        $stmt = $this->pdo->prepare("SELECT id_liste FROM Liste WHERE id_user = :id_user AND type_liste='preference'");
        $stmt =$this->pdo->prepare("INSERT INTO ");
    }

}