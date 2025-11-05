<?php

namespace iutnc\netVOD\render;

use iutnc\netVOD\base\Serie;

class SerieRenderer
{
    public function render(Serie $serie): string
    {
        $titre = $serie->titre;
        $img =$serie->cheminImg;

        $html = <<<HTML
        <div class="serie-card">
            <img class="serie" src="$img" alt="$titre">
            <h2>$titre</h2>
        </div>
        HTML;

        return $html;
    }
}