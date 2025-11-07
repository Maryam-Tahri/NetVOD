<?php

namespace iutnc\netVOD\repository;
use Exception;
use iutnc\netVOD\base\Episode;
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

    public function getAllSeries(?string $search = null, string $sort = 'titre_serie', ?string $genre = null, ?string $public = null): array
    {
        $params = [];
        $query = "SELECT s.*, 
                     (SELECT COUNT(*) FROM episode e WHERE e.id_serie = s.id_serie) AS nb_episodes
              FROM serie s";

        $whereAdded = false;

        // Partie recherche perso
        if (!empty($search)) {
            $query .= " WHERE (s.titre_serie LIKE :search OR s.descriptif LIKE :search)";
            $params[':search'] = "%$search%";
            $whereAdded = true;
        }

        // Partie filtre
        if (!empty($genre)) {
            $query .= $whereAdded ? " AND" : " WHERE";
            $query .= " s.genre = :genre";
            $params[':genre'] = $genre;
            $whereAdded = true;
        }

        if (!empty($public)) {
            $query .= $whereAdded ? " AND" : " WHERE";
            $query .= " s.public_vise = :public";
            $params[':public'] = $public;
            $whereAdded = true;
        }

        // Partie Tri
        switch ($sort) {
            case 'date_ajout':
                $query .= " ORDER BY s.date_ajout DESC";
                break;
            case 'nb_episodes':
                $query .= " ORDER BY nb_episodes DESC";
                break;
            default:
                $query .= " ORDER BY s.titre_serie ASC";
                break;
        }

        //Partie execution
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);

        $series = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $series[] = new Serie(
                $row['id_serie'],
                $row['titre_serie'],
                $row['descriptif'],
                $row['annee'],
                $row['genre'],
                $row['public_vise'],
                $row['img']
            );
        }

        return $series;
    }



    public  function getSerieById(int $idSerie): ?Serie
    {
        $stmt = $this->pdo->prepare("SELECT * FROM serie WHERE id_serie = ?");
        $stmt->execute([$idSerie]);
        $serie = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$serie) return null;

        $episodes = $this->getEpisodeBySerieID($idSerie);

        $s = new Serie(
            $serie['id_serie'],
            $serie['titre_serie'],
            $serie['descriptif'],
            $serie['annee'],
            $serie['genre'],
            $serie['public_vise'],
            $serie['img'],
        );

        foreach ($episodes as $episode) {
            $s->AddEpisode($episode);
        }

        return $s;


    }


    public function getEpisodeBySerieID(int $idSerie): array
    {


        $stmt = $this->pdo->prepare("SELECT * FROM episode WHERE id_serie = ? ORDER BY numero ASC");
        $stmt->execute([$idSerie]);
        $episodesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $episodes = [];
        foreach ($episodesData as $ep) {
            $episodes[] = new Episode(
                $ep['numero'],
                $ep['titre_ep'],
                $ep['resume_ep'],
                $ep['duree'],
                $ep['img'],
                $ep['file']
            );
        }

        return $episodes;
    }

    public function getEpisodeById(int $idEpisode): ?Episode
    {
        $stmt = $this->pdo->prepare("SELECT * FROM episode WHERE id_ep = ?");
        $stmt->execute([$idEpisode]);
        $ep = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ep) return null;

        return new Episode(
            $ep['numero'],
            $ep['titre_ep'],
            $ep['resume_ep'],
            $ep['duree'],
            $ep['img'],
            $ep['file']
        );
    }

}