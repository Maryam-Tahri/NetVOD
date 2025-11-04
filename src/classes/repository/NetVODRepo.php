<?php

namespace iutnc\netVOD\repository;
use Exception;
use iutnc\netVOD\base\Serie;
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
                throw new Exception("Configuration non définie ! Appelle d'abord NetVODRepo::setConfig().");
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
    }

    public function getAllSeries(): array
    {
        $sql = "SELECT s.titre_serie, s.descriptif,s.annee,s.genre ,s.public, s.chemin_img, AVG(c.note) as note_moyenne 
                FROM Serie s
                LEFT JOIN Commentaire c ON s.id_serie = c.id_serie
                GROUP BY s.id_serie
                ORDER BY s.titre_serie";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        $series = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $series[] = new Serie(
                $row['titre_serie'],
                $row['descriptif'],
                $row['annee'],
                $row['genre'],
                $row['public'],
                $row['chemin_img']
            );
        }

        return $series;
    }

}