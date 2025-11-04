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
            $stmt = NetVODRepo::getInstance()->getPDO();
            $stmt = $stmt->prepare("SELECT numero,titre_ep,resume_ep,duree,img,file FROM episode WHERE id_ep=?");
            $stmt->bindParam("i", $_GET['watch']);
            $stmt->execute();
            if ($result = $stmt->fetch()) {
                return <<<HTML
                <div class="player">
                  <video
                    controls
                    preload="metadata"
                    poster="{$result['img']}"
                    src="{$result['file']}">
                  </video>
                </div>
                <div>
                <h2>Episode {$result['numero']} - {$result['titre_ep']} ( {$result['duree']} min) </h2>
                <p>{$result['resume_ep']}</p>
                <a href="?action=add-favourite&id={$_GET['watch']}">ajouter au favoris</a>
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