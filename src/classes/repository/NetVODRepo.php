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

    private function __construct(array $conf)
    {
        $this->pdo = new PDO($conf['dsn'], $conf['user'], $conf['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    }

    public static function getInstance(): NetVODRepo
    {
        if (self::$instance === null) {
            if (empty(self::$config)) {
                throw new Exception("Configuration non définie ! Appelle d'abord NetVODRepo::setConfig().");
            }

            self::$instance = new NetVODRepo(self::$config);
        }
        return self::$instance;
    }

    public static function setConfig(string $file)
    {
        if (!file_exists($file)) {
            throw new Exception("Configuration file not found: " . $file);
        }

        $conf = parse_ini_file($file);
        if ($conf === false) {
            throw new Exception("Error reading configuration file");
        }

        $dsn = "{$conf['driver']}:host={$conf['host']};dbname={$conf['database']}";
        self::$config = ['dsn' => $dsn, 'user' => $conf['username'], 'pass' => $conf['password']];
    }

    public function getPDO(): PDO
    {
        return $this->pdo;
    }

    public function SaveFavourite(int $id_user, int $id_episode): void
    {
        $stmt = $this->pdo->prepare("SELECT id_liste FROM Liste WHERE id_user = :id_user AND type_liste='preference'");
        $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            throw new \Exception("Liste de préférences introuvable");
        }

        $id_liste = $result['id_liste'];

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM list2episode WHERE id_liste = :id_liste AND id_episode = :id_episode");
        $stmt->bindParam(':id_liste', $id_liste, PDO::PARAM_INT);
        $stmt->bindParam(':id_episode', $id_episode, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->fetchColumn() > 0) {
            throw new \Exception("Cet épisode est déjà dans vos favoris");
        }

        $stmt = $this->pdo->prepare("INSERT INTO list2episode (id_liste, id_episode) VALUES(:id_liste, :id_episode)");
        $stmt->bindParam(':id_liste', $id_liste, PDO::PARAM_INT);
        $stmt->bindParam(':id_episode', $id_episode, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getAllSeries(): array
    {
        $sql = "SELECT s.titre_serie, s.descriptif, s.annee, s.genre, s.public_vise, s.img
                FROM Serie s
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
                $row['public_vise'],
                $row['img']
            );
        }

        return $series;
    }

    public function getSerieById(int $idSerie): ?Serie
    {
        $stmt = $this->pdo->prepare("SELECT * FROM serie WHERE id_serie = ?");
        $stmt->execute([$idSerie]);
        $serie = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$serie) return null;

        $episodes = $this->getEpisodeBySerieID($idSerie);

        $s = new Serie(
            $serie['titre'],
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
        $stmt = $this->pdo->prepare("SELECT * FROM episode WHERE id_serie = ? ORDER BY num_episode ASC");
        $stmt->execute([$idSerie]);
        $episodesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $episodes = [];
        foreach ($episodesData as $ep) {
            $episodes[] = new Episode(
                $ep['num_episode'],
                $ep['titre'],
                $ep['resume'],
                $ep['duree'],
                $ep['chemin_img'],
                $ep['chemin_video']
            );
        }

        return $episodes;
    }


}