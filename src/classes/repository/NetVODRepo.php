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
        $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
        $stmt->execute();
        $id_liste = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt =$this->pdo->prepare("INSERT INTO list2episode (id_liste, id_episode) VALUES(:id_lsite, :id_episode) ");
        $stmt->bindParam(':id_user', $id_liste, PDO::PARAM_INT);
        $stmt->bindParam(':id_episode', $_id_episode, PDO::PARAM_INT);
        $stmt->execute();
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
            $episodes[$ep['numero']] = new Episode(
                $ep['id_ep'],
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
            $ep['id_ep'],
            $ep['numero'],
            $ep['titre_ep'],
            $ep['resume_ep'],
            $ep['duree'],
            $ep['img'],
            $ep['file']
        );
    }

    // Renvoie l'id du propriétaire d'une Liste
    public function getListOwner(int $listId): ?int {
        $sql = "SELECT id_user FROM Liste WHERE id_liste = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$listId]);
        $row = $stmt->fetch();
        return $row ? (int)$row['id_user'] : null;
    }

    public function getFavorites(int $idUser): array{
        $stmt = $this->pdo->prepare("SELECT episode.id_serie FROM list2episode INNER JOIN episode ON episode.id_ep=list2episode.id_ep INNER JOIN liste ON liste.id_liste = list2episode.id_liste WHERE liste.id_user = ? AND liste.type_list = 'preference'");
        $stmt->execute([$idUser]);
        $series = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $series [] = $this->getSerieById($row['id_serie']);
        }
        return $series;
    }


    public function getDejaVu(int $idUser): array{
        $stmt = $this->pdo->prepare("SELECT DISTINCT(episode.id_serie) FROM list2episode INNER JOIN episode ON episode.id_ep=list2episode.id_ep INNER JOIN liste ON liste.id_liste = list2episode.id_liste WHERE liste.id_user = ? AND type_list = 'deja_visionne'");
        $stmt->execute([$idUser]);
        $series = ['en_cours'=>[],'deja_vu'=>[]];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stmt2 = $this->pdo->prepare("SELECT count(*) FROM list2episode INNER JOIN liste ON liste.id_liste = list2episode.id_liste ".
                "INNER JOIN episode ON episode.id_ep = list2episode.id_ep ".
                "WHERE liste.id_user = ? AND type_list = 'deja_visionne' AND episode.id_serie = ?");
            $stmt2->execute([$idUser, $row['id_serie']]);
            $stmt3 = $this->pdo->prepare("SELECT count(*) FROM episode WHERE id_serie = ?");
            $stmt3->execute([$row['id_serie']]);
            $nbVu = $stmt2->fetch(PDO::FETCH_ASSOC);
            $nbMax = $stmt3->fetch(PDO::FETCH_ASSOC);
            if ($nbVu['count(*)'] == $nbMax['count(*)']) {
                $series['deja_vu'][] = $this->getSerieById($row['id_serie']);
            }else{
                $series['en_cours'][] = $this->getSerieById($row['id_serie']);
            }
        }
        return $series;
    }


    public function addToEnCours(int $id_ep){
        $stmt1 = $this->pdo->prepare("SELECT id_liste FROM liste WHERE id_user = ? AND type_list = 'en_cours'");
        $stmt1->execute([$_SESSION['user']['id']]);
        $id_liste = $stmt1->fetch(PDO::FETCH_ASSOC);
        $stmt2 = $this->pdo->prepare("SELECT id_ep FROM list2episode WHERE id_ep= ? AND id_liste = ? ");
        $stmt2->execute([$id_ep,$id_liste['id_liste']]);
        $row = $stmt2->fetch(PDO::FETCH_ASSOC);
        if (!$row){
            $stmt3 = $this->pdo->prepare("INSERT INTO list2episode (id_ep, id_liste) VALUES(?, ?)");
            $stmt3->execute([$id_ep,$id_liste['id_liste']]);
        }


    }
    public function addToDejaVu(int $id_ep){
        $stmt1 = $this->pdo->prepare("SELECT id_liste FROM liste WHERE id_user = ? AND type_list = 'en_cours'");
        $stmt1->execute([$_SESSION['user']['id']]);
        $id_liste = $stmt1->fetch(PDO::FETCH_ASSOC);
        $stmt12 = $this->pdo->prepare("DELETE FROM list2episode WHERE id_ep = ? AND id_liste = ?");
        $stmt12->execute([$id_ep,$id_liste['id_liste']]);
        $stmt = $this->pdo->prepare("SELECT id_liste FROM liste WHERE id_user = ? AND type_list = 'deja_visionne'");
        $stmt->execute([$_SESSION['user']['id']]);
        $id_liste = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt2 = $this->pdo->prepare("SELECT id_ep FROM list2episode WHERE id_liste = ? AND id_ep= ? ");
        $stmt2->execute([$id_liste['id_liste'],$id_ep]);
        $row  = $stmt2->fetch(PDO::FETCH_ASSOC);
        if (!$row){
            $stmt3 = $this->pdo->prepare("INSERT INTO list2episode (id_ep, id_liste) VALUES(?, ?)");
            $stmt3->execute([$id_ep, $id_liste['id_liste']]);
        }
    }


}