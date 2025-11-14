<?php

namespace iutnc\netVOD\action;

use iutnc\netVOD\repository\NetVODRepo;
use PDO;

class DisplayEpisodeAction extends Action
{
    public function execute(): string
    {
        if (!isset($_SESSION["user"])) {
            return <<<HTML
<div>Merci de vous connecter pour avoir accès à toutes les fonctionnalités !</div>
HTML;
        }
        if (isset($_GET['watch'])) {
            if (isset($_SESSION['last_ep'])) {
                $lastep = unserialize($_SESSION['last_ep']);
                $repo = NetVODRepo::getInstance();
                $repo->addToDejaVu($lastep->id);
            }
            unset($_SESSION['last_ep']);
            $serie = unserialize($_SESSION['serie']);
            $ep = NetVODRepo::getInstance()->getNumEpById($_GET['watch'])-1;
            if (isset($serie->listeEpisodes[$ep])) {
                $actuel= $_GET['watch'];
                $preced=<<<HTML
<a href="?action=display-episode&watch={$actuel}"><button>Finir l'épisode sans passer au prochain(pas de précédent)</button></a>
HTML;
                $suivant=<<<HTML
<a href="?action=display-episode&watch={$actuel}"><button>Finir l'épisode (Pas d'épisode suivant)</button></a>
HTML;
                $episode=$serie->listeEpisodes[$ep];
                NetVODRepo::getInstance()->addToEnCours($episode->id);
                if(isset($serie->listeEpisodes[$ep-1])){
                    $eppre=$serie->listeEpisodes[$ep-1]->id;
                    $preced = <<<HTML
                    <a href="?action=display-episode&watch={$eppre}"><button>episode précedent</button></a>
                    HTML;
                }
                if(isset($serie->listeEpisodes[$ep+1])){
                    $eppro=$serie->listeEpisodes[$ep+1]->id;
                    $suivant = <<<HTML
                    <a href="?action=display-episode&watch={$eppro}"><button >episode Suivant</button></a>
                    HTML;
                }
                $_SESSION['last_ep']=serialize($episode);
                return <<<HTML
                <div class="player">
                  <video
                    controls
                    preload="metadata"
                    poster="{$episode->cheminImg}"
                    src="{$episode->chemin}">
                  </video>
                </div>
                {$preced}
                {$suivant}
                <div>
                <h2>Episode {$episode->numEpisode} - {$episode->titre} ( {$episode->duree} min) </h2>
                <p>{$episode->resume}</p>
                
                <a href="?action=noter&id={$episode->id}">Noté l'épisode</a>
                <a href="?action=add-favourite&id={$episode->id}">ajouter au favoris</a>
                </div>
                HTML;
            } else {
                return <<<HTML
                    <div>Kestufou?</div>
                    HTML;
            }
        }
        return <<<HTML
            <div>erreur</div>
            HTML;
    }
}