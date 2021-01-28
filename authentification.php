<?php
session_start();
$error=2;
$bdd = new PDO('mysql:host=mysql-vanestarre-edyment.alwaysdata.net;dbname=vanestarre-edyment_projet;charset=utf8', '225207', '#clementeddy13',array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

//Chargement des informations correspondant à l'identifiant
$reponse = $bdd->prepare('SELECT mot_passe, pseudo FROM utilisateurs WHERE id=:id');
$reponse->execute(array('id'=>$_POST['identifiant']));
while ($donnees = $reponse->fetch())
{
    //vérification du mot de passe
    $pseudonyme = $donnees['pseudo'];
    $mdp = md5($_POST['mot_de_passe']);
 	if ($mdp == $donnees['mot_passe'])
    {
    	$error=0;
    }
    elseif ($mdp != $donnees['mot_passe'])
    {
     	$error=2;
    }
}
if($error==0)
{
    if ($pseudonyme == "vanestarre")
    {
        $_SESSION['id']=$_POST['identifiant'];
        header('Location: admin.php');   
    }
    else
    {
        $_SESSION['id']=$_POST['identifiant'];
        header('Location: index.php'); 
    }
    
}
else if ($error==2)
{
	header('Location: connexion.php?error=2');
}
?>