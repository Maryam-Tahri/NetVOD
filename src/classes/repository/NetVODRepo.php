<?php

namespace iutnc\netVOD\repository;

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

    public function findAllPlaylists(): array {
        $sql = "SELECT id, nom FROM playlist";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        $playlists = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $playlist = new Playlist($row['nom']);
            $playlist->id = $row['id'];
            $playlists[] = $playlist;
        }
        return $playlists;
    }

    public function saveEmptyPlaylist(Playlist $playlist): Playlist {
        $sql = "INSERT INTO playlist (nom) VALUES (:nom)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':nom', $playlist->__get('nom'));
        $stmt->execute();
        $playlist->id = (int)$this->pdo->lastInsertId();
        $id = $playlist->id;
        $stmnt = $this->pdo->prepare("INSERT INTO user2playlist (id_user,id_pl) VALUES (:id_user,:id_pl)");
        $stmnt->bindValue(':id_pl', $id);
        $stmnt->bindValue(':id_user', $_SESSION['user']['id']);
        $stmnt->execute();
        return $playlist;
    }

    public function savePodcastTrack(PodcastTrack $track): PodcastTrack {
        $sql = "INSERT INTO track (titre, auteur_podcast, duree, filename, genre, type) 
            VALUES (:titre, :auteur_podcast, :duree, :fichier, :genre, 'P')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':titre', $track->__get('titre'));
        $stmt->bindValue(':auteur_podcast', $track->__get('auteur'));
        $stmt->bindValue(':duree', $track->__get('duree'));
        $stmt->bindValue(':fichier', $track->getNomFichier());
        $stmt->bindValue(':genre', $track->__get('genre'));
        $stmt->execute();

        $track->id = (int)$this->pdo->lastInsertId();
        return $track;
    }

    public function saveAlbumTrack(AlbumTrack $track): AlbumTrack {

        $exist = $this->findAlbumTrackByAttributes($track->__get('titre'), $track->__get('artiste'), $track->__get('album'), $track->__get('numero'));

        if ($exist !== null) {
            $track->id = $exist;
            return $track;
        }
        $sql = "INSERT INTO track (titre, genre, duree, filename, type, artiste_album, titre_album, annee_album, numero_album) 
            VALUES (:titre, :genre, :duree, :fichier, 'A', :artiste, :album, :annee, :numero)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':titre', $track->__get('titre'));
        $stmt->bindValue(':genre', $track->__get('genre'));
        $stmt->bindValue(':duree', $track->__get('duree'));
        $stmt->bindValue(':fichier', $track->getNomFichier());
        $stmt->bindValue(':artiste', $track->__get('artiste'));
        $stmt->bindValue(':album', $track->__get('album'));
        $stmt->bindValue(':annee', $track->__get('annee'));
        $stmt->bindValue(':numero', $track->__get('numero'));
        $stmt->execute();

        $track->id = (int)$this->pdo->lastInsertId();
        return $track;
    }

    public function addTrackToPlaylist(int $trackId, int $playlistId): void {

        $sql_check = "SELECT COUNT(*) FROM playlist2track 
                      WHERE id_pl = :id_pl AND id_track = :id_track";
        $verif = $this->pdo->prepare($sql_check);
        $verif->bindValue(':id_pl', $playlistId, PDO::PARAM_INT);
        $verif->bindValue(':id_track', $trackId, PDO::PARAM_INT);
        $verif->execute();

        if ($verif->fetchColumn() > 0) {
            return;  //on fait rien si la musique est deja dans la playlist
        }

        // Calculer le prochain numéro de piste
        $sql_max = "SELECT COALESCE(MAX(no_piste_dans_liste), 0) + 1 
                FROM playlist2track 
                WHERE id_pl = :id_pl";
        $stmt_max = $this->pdo->prepare($sql_max);
        $stmt_max->bindValue(':id_pl', $playlistId, PDO::PARAM_INT);
        $stmt_max->execute();
        $nextTrackNumber = $stmt_max->fetchColumn();

        // Insérer l'association
        $sql = "INSERT INTO playlist2track (id_pl, id_track, no_piste_dans_liste) 
            VALUES (:id_pl, :id_track, :no_piste_dans_liste)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_pl', $playlistId, PDO::PARAM_INT);
        $stmt->bindValue(':id_track', $trackId, PDO::PARAM_INT);
        $stmt->bindValue(':no_piste_dans_liste', $nextTrackNumber, PDO::PARAM_INT);

        $stmt->execute();
    }

    public function findPlaylistById(int $playlistId): Playlist {
        // Récupérer la playlist
        $pl = $this->pdo->prepare("SELECT id, nom FROM playlist WHERE id = :id");
        $pl->bindValue(':id', $playlistId, PDO::PARAM_INT);
        $pl->execute();
        $row = $pl->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new Exception("Playlist avec l'ID $playlistId introuvable");
        }

        $playlist = new Playlist($row['nom']);
        $playlist->id = (int)$row['id'];


        $tracks = $this->pdo->prepare("
            SELECT 
                t.id,
                t.titre,
                t.genre,
                t.duree,
                t.filename,
                t.type,
                t.artiste_album,
                t.titre_album,
                t.annee_album,
                t.numero_album,
                t.auteur_podcast,
                t.date_posdcast 
            FROM track t
            INNER JOIN playlist2track pt ON pt.id_track = t.id 
            WHERE pt.id_pl = :id 
            ORDER BY pt.no_piste_dans_liste
        ");
        $tracks->bindValue(':id', $playlistId, PDO::PARAM_INT);
        $tracks->execute();

        while ($row = $tracks->fetch(PDO::FETCH_ASSOC)) {
            if ($row['type'] == 'A') {
                $track = new AlbumTrack(
                    $row['titre'] ?? 'inconnu',
                    $row['filename'] ?? 'inconnu',
                    $row['titre_album'] ?? 'inconnu',
                    $row['numero_album'] ?? 0,
                    $row['artiste_album'] ?? 'inconnu',
                    $row['annee_album'] ?? 0,
                    $row['genre'] ?? 'inconnu',
                    $row['duree'] ?? 0
                );
                $track->id = (int)$row['id'];
            } elseif ($row['type'] == 'P') {
                $track = new PodcastTrack(
                    $row['titre'] ?? 'inconnu',
                    $row['filename'] ?? 'inconnu',
                    $row['auteur_podcast'] ?? 'inconnu',
                    $row['date_posdcast'] ?? '',
                    $row['genre'] ?? 'inconnu',
                    $row['duree'] ?? 0
                );
                $track->id = (int)$row['id'];
            } else {
                continue;
            }

            $playlist->ajouterPiste($track);
        }

        return $playlist;
    }

    public function findAlbumTrackByAttributes(string $titre, string $artiste, string $album, int $numero) {
        $sql = "SELECT id FROM track 
                WHERE type = 'A' 
                AND titre = :titre 
                AND artiste_album = :artiste 
                AND titre_album = :album 
                AND numero_album = :numero 
                ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':titre', $titre);
        $stmt->bindValue(':artiste', $artiste);
        $stmt->bindValue(':album', $album);
        $stmt->bindValue(':numero', $numero, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if(isset($result['id'])){
            return  (int)$result['id'] ;
        }else{
            return null;
        }
    }

    public function delTrackFromPlaylist(int $id_pl, int $id_track){
        $stmt = $this->pdo->prepare("DELETE FROM playlist2track WHERE id_pl = :id_pl AND id_track = :id_track");
        $stmt->bindValue(':id_pl', $id_pl, PDO::PARAM_INT);
        $stmt->bindValue(':id_track', $id_track, PDO::PARAM_INT);
        return $stmt->execute();
    }
}