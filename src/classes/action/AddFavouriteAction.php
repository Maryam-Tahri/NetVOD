<?php

namespace iutnc\netVOD\action;

use iutnc\netVOD\repository\NetVODRepo;

class AddFavouriteAction
{
    public function execute(){
        if(!isset($_SESSION["user"])){
            return <<<HTML
<div>Merci de vous connecter pour avoir accès à toutes les fonctionnalités !</div>
HTML;
        }
        $idFavourite = $_GET["id"];
        $repo = NetVODRepo::getInstance()->getPDO();
        $repo->SaveFavourite($_SESSION["user"]['id'],$idFavourite);
        return <<<HTML
        <div>Vous ne devriez pas être ici de cette manière</div>
        HTML;

    }

}