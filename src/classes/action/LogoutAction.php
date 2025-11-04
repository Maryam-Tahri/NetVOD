<?php

namespace iutnc\netVOD\action;
use iutnc\netVOD\auth\AuthnProvider;
use iutnc\netVOD\exception\AuthException;

class LogoutAction
{
    public function execute(): string
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            unset($_SESSION['user']);
            unset($_SESSION['playlist']);
            $html = "<div>Vous êtes bien déconnecter</div>";
            return $html;
        }
        return "<p>Méthode HTTP non supportée.</p>";
    }
}