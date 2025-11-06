<?php

namespace iutnc\netVOD\render;

use iutnc\netVOD\base\Serie;
use iutnc\netVOD\base\Liste;

class ListRender
{
    public function renderListe(Liste $liste): string
    {
        $type = htmlspecialchars($liste->type_list);
        $id_liste = $liste->id_liste;

        $html = <<<HTML
        <div class="list-card">
            <a href="?action=liste&id_liste=$id_liste">
                <h2>$type</h2>
            </a>
        </div>
        HTML;

        return $html;
    }

}