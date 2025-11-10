<?php

namespace iutnc\netVOD\action;

use iutnc\netVOD\render\SerieRenderer;
use iutnc\netVOD\repository\NetVODRepo;

class AfficheCatalogue extends Action
{
    public function execute(): string
    {
        $repo = NetVODRepo::getInstance();

        $searchValue = $_GET['search'] ?? '';
        $sortValue = $_GET['sort'] ?? 'titre_serie';
        $sortGenre = $_GET['genre'] ?? null;
        $sortPublic = $_GET['public'] ?? null;

        $series = $repo->getAllSeries($searchValue, $sortValue, $sortGenre, $sortPublic);

        $html = '<div class="catalogue-container">';
        $html .= '<h1>Catalogue des séries</h1>';

        $html .= <<<HTML
<form method="GET" action="">
    <input type="hidden" name="action" value="catalogue">
    <input type="text" name="search" placeholder="Rechercher..." value="{$searchValue}">
    
    <select name="sort">
        <option value="titre_serie"  <?= $sortValue === 'titre_serie' ? 'selected' : '' ?>Titre</option>
        <option value="date_ajout"   <?= $sortValue === 'date_ajout' ? 'selected' : '' ?>Date d’ajout</option>
        <option value="nb_episodes"  <?= $sortValue === 'nb_episodes' ? 'selected' : '' ?>Nombre d’épisodes</option>
    </select>
    
    <select name="genre">
        <option value="">-- Tous les genres --</option>
        <option value="action" <?= $sortGenre === 'action' ? 'selected' : '' ?>Action</option>
        <option value="comédie" <?= $sortGenre === 'comédie' ? 'selected' : '' ?>Comédie</option>
        <option value="drame" <?= $sortGenre === 'drame' ? 'selected' : '' ?>Drame</option>
        <option value="science-fiction" <?= $sortGenre === 'science-fiction' ? 'selected' : '' ?>Science-fiction</option>
    </select>
    
    <select name="public">
        <option value="">-- Tout public --</option>
        <option value="enfant" <?= $sortPublic === 'enfant' ? 'selected' : '' ?>Enfant</option>
        <option value="ado" <?= $sortPublic === 'ado' ? 'selected' : '' ?>Adolescent</option>
        <option value="adulte" <?= $sortPublic === 'adulte' ? 'selected' : '' ?>Adulte</option>
    </select>
    
    <button type="submit">Rechercher / Trier</button>
</form>

HTML;
        
        if (empty($series)) {
            $html .= '<p class="no-content">Aucune série trouvée.</p>';
        } else {
            $html .= '<div class="series-grid">';
            foreach ($series as $serie) {
                $renderer = new SerieRenderer();
                $html .= $renderer->render($serie);
            }
            $html .= '</div>';
        }

        $html .= '</div>';
        return $html;
    }


}
