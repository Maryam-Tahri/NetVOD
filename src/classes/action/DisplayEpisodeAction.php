<?php

namespace iutnc\netVOD\action;

class DisplayEpisodeAction extends Action
{
    public function execute():String {
        if(!isset($_SESSION["user"])) {
            return <<<HTML
<div>Merci de vous connecter pour avoir accès à toutes les fonctionnalités !</div>
HTML;
        }

}