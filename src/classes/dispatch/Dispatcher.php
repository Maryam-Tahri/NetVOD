<?php

namespace iutnc\netVOD\dispatch;

use iutnc\deefy\action\AddPlaylistAction;
use iutnc\deefy\action\AddPodcastTrackAction;
use iutnc\deefy\action\AddTrackAction;
use iutnc\deefy\action\DefaultAction;
use iutnc\deefy\action\DeleteTrackAction;
use iutnc\deefy\action\DisplayPlaylistAction;
use iutnc\deefy\action\AddUserAction;
use iutnc\deefy\action\logoutAction;
use iutnc\deefy\action\signinAction;

class Dispatcher
{
    private $action;

    public function __construct(){
        $this->action = $_GET['action'] ?? "default";
    }

    public function run() : void{
        switch($this->action){
            case "default":
                $action = new DefaultAction();
                $this->renderPage($action->execute());
                break;
            case "display-playlist":
                $action = new DisplayPlaylistAction();
                $this->renderPage($action->execute());
                break;
            case "add-playlist":
                $action = new AddPlaylistAction();
                $this->renderPage($action->execute());
                break;
            case "add-track":
                $action = new AddTrackAction();
                $this->renderPage($action->execute());
                break;
            case "add-podcast":
                $action = new AddPodcastTrackAction();
                $this->renderPage($action->execute());
                break;
            case "add-user":
                $action = new AddUserAction();
                $this->renderPage($action->execute());
                break;
            case "signin":
                $action = new SigninAction();
                $this->renderPage($action->execute());
                break;
            case "logout":
                $action = new LogoutAction();
                $this->renderPage($action->execute());
                break;
            case "del-track":
                $action = new DeleteTrackAction();
                $this->renderPage($action->execute());
                break;
            default:
                $this->renderPage("pas d'action");
                break;
        }
    }

    private function renderPage($html){
        if (!isset($_SESSION['user'])){
            $conn = <<<HTML
            <a href='?action=add-user'>Inscription</a>
            <a href='?action=signin'>Se connecter</a>
HTML;
        }else{
            $conn = <<<HTML
            <a href='?action=logout'>se déconnecter</a>
           HTML;
        }
        $res = <<<HTML
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <title>Deefy</title>
            <link rel='stylesheet' href='../css/style.css'>
        </head>
        <body>
            <header>
                <h1>Deefy</h1>
                <p>Votre espace personnel pour créer et écouter vos playlists</p>
            </header>

            <nav>
                <a href="?action=default">Accueil</a>
                {$conn}
                <a href="?action=display-playlist">Mes playlists</a>
                <a href="?action=add-playlist">Créer une playlist</a>
                <a href="?action=add-track">Ajouter une piste</a>
            </nav>

            <main>
                {$html}
            </main>

        </body>
        </html>
        HTML;
        echo $res;

    }

}