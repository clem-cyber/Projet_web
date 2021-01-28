<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="css/styleAdmin.css" />
        <title>Connexion</title>
    </head>
    <body> 
        <header id="banniere_connexion">
            <h1><a href="index.php">Bienvenue chez Vanestarre</a></h1>
        </header>
        <section id="connexion">
            <h2>Connectez-vous</h2>
            <?php
            $bdd = new PDO('mysql:host=mysql-vanestarre-edyment.alwaysdata.net;dbname=vanestarre-edyment_projet;charset=utf8', '225207', '#clementeddy13',array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            $reponse = $bdd->query('SELECT id FROM utilisateurs ORDER BY id DESC');
            while ($donnees = $reponse->fetch())
            {
                if (isset($donnees['id']))
                {
                    $identifiantMail = $donnees['id'];
                    break;
                }
                else
                    $identifiantMail = 1;
            }
            $reponse->closeCursor();
            session_destroy();
            if(isset($_GET['create']) AND $_GET['create']==1) //Après creation du compte (create_account.php)
            {
                echo "Votre compte a bien été créé <br />";
                echo "Veuillez saisir le numéro d'identifiant qui vous a été envoyé par mail";
            }
            ?>

            <form action="authentification.php" method="post"> <?php //Création du formulaire d'index ?>
                <p>
                    <label for="identifiant">Numéro d'identifiant : </label>
                    <input type="number" name="identifiant" id="identifiant" /><br />
                    <label for="mot_de_passe">Mot de passe : </label><br />
                    <input type="password" name="mot_de_passe" id="mot_de_passe" /><br /><br />
                    <input type="submit" value="Se connecter">
                </p>
            </form>
            
            <?php
            if (isset($_GET['error']) AND $_GET['error']==2)
            {
                echo "L'identifiant ou le mot de passe est incorrect";
            }
            ?>
            <nav>
                <p><a href="create_account.php">Créer un compte</a></p>
                <p><a href="index.php">Retour</a></p>
                <p><a href="recuperation.php">Mot de passe oublié</a></p>
            </nav>
        </section>
    </body>
</html>