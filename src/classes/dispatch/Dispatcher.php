<?php

namespace iutnc\netVOD\dispatch;

use iutnc\netVOD\action\ActivateAccountAction;
use iutnc\netVOD\action\AddPlaylistAction;
use iutnc\netVOD\action\AddPodcastTrackAction;
use iutnc\netVOD\action\AddTrackAction;
use iutnc\netVOD\action\AfficheCatalogue;
use iutnc\netVOD\action\AfficheEpisode;
use iutnc\netVOD\action\AfficheSerie;
use iutnc\netVOD\action\DefaultAction;
use iutnc\netVOD\action\DeleteTrackAction;
use iutnc\netVOD\action\DisplayEpisodeAction;
use iutnc\netVOD\action\DisplayPlaylistAction;
use iutnc\netVOD\action\CreateUserAction;
use iutnc\netVOD\action\logoutAction;
use iutnc\netVOD\action\MySerieAction;
use iutnc\netVOD\action\ResetPasswdAction;
use iutnc\netVOD\action\ResetPasswdTokenAction;
use iutnc\netVOD\action\signinAction;
use iutnc\netVOD\action\UserProfileSettingsAction;

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
            case "serie":
                $action = new AfficheSerie();
                $this->renderPage($action->execute());
                break;
            case "episode":
                $action = new AfficheEpisode();
                $this->renderPage($action->execute());
                break;
            case "display-episode":
                $action = new DisplayEpisodeAction();
                $this->renderPage($action->execute());
                break;
            case "mySeries":
                $action = new MySerieAction();
                $this->renderPage($action->execute());
                break;
            case "activate-account":
                $action = new ActivateAccountAction();
                $this->renderPage($action->execute());
                break;
            case "reset-passwd-token":
                $action = new ResetPasswdTokenAction();
                $this->renderPage($action->execute());
                break;
            case "reset-passwd":
                $action = new ResetPasswdAction();
                $this->renderPage($action->execute());
                break;
            case "change-user-info":
                $action = new UserProfileSettingsAction();
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
            <a href='?action=change-user-info'>Profil</a>
            <a href='?action=logout'>se déconnecter</a>
            <a href="?action=mySeries">Mes séries</a>
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