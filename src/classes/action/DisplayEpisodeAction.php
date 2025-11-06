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
            $serie = unserialize($_SESSION['serie']);
            $ep = $_GET['watch']-1;
            if (isset($serie->listeEpisodes[$ep])) {
                $preced=<<<HTML
<button>Pas d'épisode précédent</button>
HTML;
                $suivant=<<<HTML
<button>Pas d'épisode suivant</button>
HTML;
                $episode=$serie->listeEpisodes[$ep];

                if(isset($serie->listeEpisodes[$ep-1])){
                    $eppre=$_GET['watch']-1;
                    $preced = <<<HTML
                    <a href="?action=display-episode&watch={$eppre}"><button>episode précedent</button></a>
                    HTML;
                }
                if(isset($serie->listeEpisodes[$ep+1])){
                    $eppro=$_GET['watch']+1;
                    $suivant = <<<HTML
                    <a href="?action=display-episode&watch={$eppro}"><button >episode Suivant</button></a>
                    HTML;
                }
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
                
                <a href="?action=add-favourite&id={$episode->id_ep}">ajouter au favoris</a>
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