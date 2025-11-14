<?php

namespace iutnc\netVOD\action;

use iutnc\netVOD\render\SerieRenderer;
use iutnc\netVOD\repository\NetVODRepo;
use PDO;

class MySerieAction
{
    public function execute(){
        if(!isset($_SESSION["user"])){
            return <<<HTML
<div>Merci de vous connecter pour avoir accès à toutes les fonctionnalités !</div>
HTML;
        }

        $repo = NetVODRepo::getInstance();
        $favoris = $repo->getFavorites($_SESSION["user"]['id']);
        $renderer = new SerieRenderer();

        // --- FAVORIS ---
        $html = <<<HTML
<div class="liste-section">
    <h3>Mes Favoris</h3>
    <div class="series-grid">
HTML;

        foreach($favoris as $fav){
            $html .= $renderer->render($fav);
        }

        $html .= <<<HTML
    </div>
</div>
HTML;

        // --- EN COURS ---
        $series = $repo->getDejaVu($_SESSION["user"]['id']);

        $html .= <<<HTML
<div class="liste-section">
    <h3>En cours</h3>
    <div class="series-grid">
HTML;

        foreach($series["en_cours"] as $en_cours){
            $html .= $renderer->render($en_cours);
        }

        $html .= <<<HTML
    </div>
</div>
HTML;

        // --- DÉJÀ VU ---
        $html .= <<<HTML
<div class="liste-section">
    <h3>Déjà vu</h3>
    <div class="series-grid">
HTML;

        foreach($series["deja_vu"] as $deja_vu){
            $html .= $renderer->render($deja_vu);
        }

        $html .= <<<HTML
    </div>
</div>
HTML;

        return $html;
    }
}
