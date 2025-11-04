<?php

namespace iutnc\netVOD\action;

use iutnc\netVOD\repository\NetVODRepo;

class EvaluationAction extends Action
{

    public function execute():String{

        if(!isset($_SESSION['user'])){
            return <<<HTML
<div>Merci de vous connecter pour avoir accès à toutes les fonctionnalités !</div>
HTML;
        }
        $id_ep = $_GET['id'];
        $id_user = $_GET['user']['id'];
        $stmt = NetVODRepo::getInstance()->getPDO();
        $stmt = $stmt->prepare("SELECT count(*) FROM evaluation WHERE id_ep = ? AND id_user = ?");
        $stmt->bindParam(1,$id_ep);
        $stmt->bindParam(2,$id_user);
        $stmt->execute();
        $row = $stmt->fetch();
        if($row['count(*)'] == 1){
            return <<<HTML
    <div>Vous avez déjà noté cette episode</div>
    HTML;
        }
        if($_SERVER['REQUEST_METHOD'] == 'GET'){
            return <<<HTML
<form method="POST" action="?action=evaluation">
<label for='comm'>Commentaire :</label><br>
<input type='text' name='comm' id='comm' required><br><br>

<label for='note'>Note (0-5) :</label><br>
<input type='text' name='note' id='note' min="0" max ="5" required><br><br>
<button type='submit'>Publier le commentaire</button>
HTML;
        }
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $note =filter_var($_POST['comm'],FILTER_SANITIZE_NUMBER_INT);
            $comm =filter_var($_POST['comm'],FILTER_SANITIZE_STRING);
            $stmt = NetVODRepo::getInstance()->getPDO();
            $stmt = $stmt->prepare("INSERT INTO evaluation (id_ep, id_user, commentaire, note, date) VALUES (?, ?, ?, ?, ?)");
            $stmt->bindParam(1,$id_ep);
            $stmt->bindParam(2,$id_user);
            $stmt->bindParam(3,$comm);
            $stmt->bindParam(4,$note);
            $date  = date("Y-m-d",time());
            $stmt->bindParam(5,$date);
            $stmt->execute();
            if($stmt){
                return <<<HTML
<div>Merci de votre retour !</div>
HTML;
            }
        }
        return <<<HTML
<div>Erreur </div>
HTML;
    }
}