<?php

namespace iutnc\netVOD\action;
use iutnc\netVOD\auth\AuthnProvider;
use iutnc\netVOD\exception\AuthException;

class SigninAction
{

    public function execute(): string
    {

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (isset($_SESSION['TOed'][$_SERVER['REMOTE_ADDR']])) {
                if ($_SESSION['TOed'][$_SERVER['REMOTE_ADDR']] > time()) {
                    return <<<HTML
<div>Tu es toujours ban</div>
HTML;
                }else{
                    unset($_SESSION['TOed'][$_SERVER['REMOTE_ADDR']]);
                }
            }
            $html = "<h2>Connexion</h2>
                <form method='post' action='?action=signin'>
                    <label for='email'>Email :</label><br>
                    <input type='text' name='email' id='email' required><br><br>

                    <label for='mdp'>Mot de passe :</label><br>
                    <input type='password' name='mdp' id='mdp' required><br><br>               

                    <button type='submit'>Connexion</button>
                </form>";
            return $html;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_SESSION['try'])){
                $_SESSION['try'] = 0;
            }
            $email = filter_var($_POST['email'],FILTER_VALIDATE_EMAIL);
            $pswd = filter_var($_POST['mdp'],FILTER_SANITIZE_STRING);
            try{
                AuthnProvider::signin($email, $pswd);
            }catch (AuthException $e){
                if ($_SESSION['try'] < 3){
                    $html= <<<HTML
                    <div><p class="incorrect">Email et/ou mot de passe incorrect !</p></div>
                    HTML;
                    $_SESSION['try']+=1;
                }else{
                    $nom_dossier = '../img';
                    $dossier = opendir($nom_dossier);
                    $imgs =[];

                    while($fichier = readdir($dossier))
                    {
                        if ($fichier != "." && $fichier != ".."){
                            $imgs[] = $fichier;
                        }
                    }
                    closedir($dossier);
                    $i = rand(0,count($imgs)-1);
                    $html = <<<HTML
                            <div><p>Hop, t'es ban, à dans 5 minutes</p></div>
                            <img src='$nom_dossier/$imgs[$i]'>
                            HTML;
                    $_SESSION['TOed'][$_SERVER['REMOTE_ADDR']] = time()+(5*60);
                    $_SESSION['try']=0;
                }
                return $html;
            }
            unset($_SESSION['try']);
            $html =<<<HTML
                    <div class="correct"><p>Vous êtes bien connecté !</p></div>
                HTML;
            return $html;
        }

        return "<p class='error'>Méthode HTTP non supportée.</p>";
    }

}