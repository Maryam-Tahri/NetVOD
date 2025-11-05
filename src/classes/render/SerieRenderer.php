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
            <form method='POST' action='?action=ajouter-favori' style='display:inline;'>
            <input type='hidden' name='id_episode' value='123'>
            <button type='submit' class='btn-favori'>Ajouter aux favoris</button>
            </form>
        </div>
        HTML;

        return $html;
    }
}