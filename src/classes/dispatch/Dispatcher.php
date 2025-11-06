<?php

namespace iutnc\netVOD\dispatch;

use iutnc\netVOD\action\AddPlaylistAction;
use iutnc\netVOD\action\AddPodcastTrackAction;
use iutnc\netVOD\action\AddTrackAction;
use iutnc\netVOD\action\AfficheCatalogue;
use iutnc\netVOD\action\AfficheEpisode;
use iutnc\netVOD\action\AfficheSerie;
use iutnc\netVOD\action\DefaultAction;
use iutnc\netVOD\action\DeleteTrackAction;
use iutnc\netVOD\action\DisplayPlaylistAction;
use iutnc\netVOD\action\CreateUserAction;
use iutnc\netVOD\action\logoutAction;
use iutnc\netVOD\action\signinAction;
use iutnc\netVOD\action\AjouterFavoris;

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
            case "catalogue":
                $action = new AfficheCatalogue();
                $this->renderPage($action->execute());
                break;
            case "add-user":
                $action = new CreateUserAction();
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
            case 'ajouter-favoris':
                $action = new AjouterFavoris();
                $this->renderPage($action->execute());
                break;
            case "serie":
                $action = new AfficheSerie();
                $this->renderPage($action->execute());
                break;
            case "episode":
                $action = new AfficheEpisode();
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
            <a href="?action=catalogue">Afficher le catalogue</a>
HTML;
        }else{
            $conn = <<<HTML
            <a href="?action=catalogue">Afficher le catalogue</a>
            <a href='?action=logout'>se déconnecter</a>
           HTML;
        }
        $res = <<<HTML
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <title>NetVOD</title>
            <link rel='stylesheet' href='../css/style.css'>
        </head>
        <body>
            <header>
                <h1>NetVOD</h1>
                <p>Votre espace personnel pour regarder les meilleures séries</p>
            </header>

            <nav>
                <a href="?action=default">Accueil</a>
                <a href="?action=catalogue">Afficher le catalogue</a>
                {$conn}
                
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