<?php

namespace iutnc\netVOD\render;

use iutnc\netVOD\base\Serie;

class SerieRenderer
{
    public function render(Serie $serie): string
    {
        $titre = htmlspecialchars($serie->titre);
        $img = htmlspecialchars($serie->cheminImg);
        $id = $serie->id;

        $html = <<<HTML
        <div class="serie-card">
            <a href="?action=serie&id=$id">
                <img class="serie" src="$img" alt="$titre">
                <h2>$titre</h2>
            </a>
        </div>
        HTML;

        return $html;
    }

    public function renderSerieEpisode(Serie $serie): string
    {
        $titre = htmlspecialchars($serie->titre);
        $genre = htmlspecialchars($serie->genre ?? '');
        $public = htmlspecialchars($serie->public ?? '');
        $descriptif = htmlspecialchars($serie->descriptif);
        $annee = $serie->annee;
        $nbEpisodes = count($serie->listeEpisodes);
        $id = $serie->id;

        $episodesHtml = '';
        foreach ($serie->listeEpisodes as $ep) {
            $episodesHtml .= <<<HTML
            <div class='episode'>
                <a href='?action=episode&id={$ep->numEpisode}'>
                    <img src='{$ep->cheminImg}' alt='Image de l\'épisode'>
                    <p><strong>Épisode {$ep->numEpisode} : {$ep->titre}</strong></p>
                    <p>Durée : {$ep->duree} min</p>
                </a>
            </div>
            HTML;
        }

        $html = <<<HTML
        <div class='serie-detail'>
            <h2>$titre</h2>
            <p><strong>Genre :</strong> $genre</p>
            <p><strong>Public visé :</strong> $public</p>
            <p><strong>Descriptif :</strong> $descriptif</p>
            <p><strong>Année de sortie :</strong> $annee</p>
            <p><strong>Nombre d'épisodes :</strong> $nbEpisodes</p>
            
            <form method='POST' action='?action=ajouter-favoris' style='display:inline;'>
                <input type='hidden' name='id_serie' value='$id'>
                <button type='submit'>Ajouter aux favoris</button>
            </form>
            
            <h3>Liste des épisodes :</h3>
            <div class='episodes'>
                $episodesHtml
            </div>
        </div>
        HTML;

        return $html;
    }
}