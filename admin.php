<?php 
session_start();
$bdd = new PDO('mysql:host=mysql-vanestarre-edyment.alwaysdata.net;dbname=vanestarre-edyment_projet;charset=utf8', '225207', '#clementeddy13',array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

//Cette page ne peut être accessible qu'après une connexion on vérifie donc que l'utilisateur soit connecté
if (!isset($_SESSION['id']))
{
    header("Location: index.php");
}

//Récupération des droits de l'utilisateur
else if (isset($_SESSION['id']))
{
    $reponse = $bdd->prepare('SELECT droits FROM utilisateurs WHERE id=:id');
    $reponse->execute(array('id'=>$_SESSION['id']));
    while ($donnees = $reponse->fetch())
    {
        $droits = $donnees['droits'];
    }
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="css/styleAdmin.css" />
        <title>Mon Compte</title>
    </head>
    <script type="text/javascript">
        function hideThis(_div){
        var obj = document.getElementById(_div);
        if(obj.style.display == "block")
            obj.style.display = "none";
        else
            obj.style.display = "block";
        }
    </script>
    <body>        
        <header id="banniere">
            <h1><a href="index.php">VANESTARRE</a></h1>
            <nav id="liens">
                <a href="index.php">accueil</a>
                <?php

                //Si les droits sont égaux à 2 alors on est en mode super-administrateur
                if ($droits == 2)
                {
                    echo "<a href='membres.php'>Membres</a>";
                }
                if ($droits >= 0)
                {
                    echo "<a href='parametre.php'>Paramètres</a>";
                }
                if(isset($_GET['newpost']))
                {
                    if ($_GET['newpost']==1)
                    {
                        echo "<a href='admin.php?newpost=0'>Retour</a>";
                    }
                    else
                    {
                        echo "<a href='admin.php?newpost=1'>Nouveau</a>";   
                    }  
                }
                else if (!isset($_GET['newpost']) && ($droits == 1 || $droits == 2))
                {
                    echo "<a href='admin.php?newpost=1'>Nouveau</a>";    
                }
                else
                {
                    echo "<p title='Seule Vanestarre peut vous donnez le droit de poster'>Nouveau </p>";
                }
                ?>
                <a href="index.php?deco=1">Déconnexion</a> 
            </nav>
        </header>
            <section id="posts">
                <article>
                    <?php
                    //Formulaire de création d'un post après avoir cliqué sur "nouveau"
                    if(isset($_GET['newpost']))
                    { 
                        if ($_GET['newpost']==1 && ($droits == 1 || $droits == 2))
                        {?>
                            <div id="creation_post">
                                <h2>Créer un nouveau post :</h2>
                                <form action="traitement.php" method="post" enctype="multipart/form-data">
                                    <p>
                                        <label for="titre">Titre :</label><br />
                                        <input type="text" name="titre" id="titre" /><br />
                                        <label for="contenu">Contenu (50 caractères maximum) : </label><br />
                                        <textarea type="text" name="contenu" id="contenu" rows="3" cols="30" maxlength="50"></textarea><br />
                                        <input type="button" value="β" onclick="hideThis('tag')" /><br />
                                        <input type="text" name="tag" id="tag" placeholder="Tag" style="display: none;" /><br />
                                        <input type="hidden" name="MAX_FILE_SIZE" value="5000000.0" />
                                        Image (< 50kb): <input type="file" name="upload" /><br/>
                                        <input type="submit" name="Poster" value="Poster" />
                                    </p>
                                </form>
                            </div>
                            <?php
                        }
                        else
                        {
                            header('Location: admin.php');
                        }
                    }
                    //Formulaire de modification d'un post après avoir cliqué sur modifier
                    else if (isset($_GET['modifier']))
                    {
                        if ($_GET['modifier']==1)
                        {
                            $reponse = $bdd->prepare('SELECT tag, titre, contenu, id_auteur FROM posts WHERE id=:id_post');
                            $reponse->execute(array('id_post'=>$_GET['id_post']));
                            while ($donnees = $reponse->fetch())
                            {
                                $ancien_titre=$donnees['titre'];
                                $ancien_contenu=$donnees['contenu'];
                                $ancien_tag=$donnees['tag'];
                                $id_auteur=$donnees['id_auteur'];
                                if ($_SESSION['id'] != $id_auteur)
                                    break;
                                ?>
                                <div id="modification">
                                    <h2>Modification du post :</h2>
                                    <form action="traitement.php" method="post">
                                        <p>
                                            <label for="titre" >Titre :</label><br />
                                            <?php echo "<input type='text' name='titre' id='titre' value=".$ancien_titre." /><br />"; ?>
                                            <label for="contenu">Contenu :</label><br />
                                            <?php 
                                            echo "<textarea type='text' name='contenu' id='contenu' rows='3' cols='30' maxlength='50'>".$ancien_contenu."</textarea><br />"; 
                                            echo "<input type='hidden' name='id_post' value=".$_GET['id_post']." />"; ?>
                                            <label for="tag">Tag :</label><br />
                                            <?php
                                            echo "<input type='text' name='tag' id='tag' value=".$ancien_tag." /><br />"; ?>
                                            <input type="submit" name="Modifier" value="Modifier" />
                                        </p>
                                    </form>
                                </div>
                                <?php
                            }
                        }
                    }
                    ?>
                    <div id="post">
                        <h2>Vos derniers posts : </h2>
                        <?php
                        //Affichage des dernier posts de l'utilisateur
                        $id_auteur=$_SESSION['id'];
                        $reponse = $bdd->prepare('SELECT * FROM posts WHERE id_auteur=:id_auteur ORDER BY id DESC LIMIT 0, 10');
                        $reponse->execute(array('id_auteur'=>$id_auteur));
                        while ($donnees = $reponse->fetch())
                        {
                            ?>
                            <div class="entier">
                                <?php
                                $id_post=$donnees['id'];
                                echo '<h3>'. $donnees['titre'] .' - le '. $donnees['date_creation'] .'</h3>'; ?>
                                <div class="contenu">
                                    <?php
                                    //contenu post
                                    echo $donnees['contenu'] .'<br />';
                                    if ($donnees['tag'] != '')
                                    {
                                        echo 'β';
                                        echo $donnees['tag'] .'<br /><br/>';
                                    }
                                    $reponseImg = $bdd->prepare('SELECT * FROM images WHERE id_post = :id_post');
                                    $reponseImg->execute(array('id_post'=>$donnees['id']));
                                    while ($donneesImg = $reponseImg->fetch())
                                    {
                                        if ($donneesImg['url_img'] != '')
                                        {
                                            echo "<img height='500rem' src=".$donneesImg['url_img']." alt='Image indisponible' />";
                                        }
                                    }
                                    //Lien des emojis ?>
                                    <div class="lien_post">
                                        <?php
                                        echo "<a href='admin.php?modifier=1&id_post=".$id_post."'>Modifier </a>";
                                        echo "<a href='traitement.php?supp=1&id_post=".$id_post."'>Supprimer</a>";
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                </article>
                <aside>
                    <?php
                    //Affichage de la description de l'utilisateur
                    $reponse = $bdd->prepare('SELECT pseudo, descrip FROM utilisateurs WHERE id=:id');
                    $reponse->execute(array('id'=>$_SESSION['id']));
                    while ($donnees = $reponse->fetch())
                    {
                        $pseudo  = $donnees['pseudo'];
                        $descrip = $donnees['descrip'];
                    }
                    $reponse->closeCursor(); 
                    echo "<h3>A propos de @" .$pseudo. "</h3>";
                    echo "<p>" .$descrip. "</p>";
                    ?>
                </aside>
           </section>
    </body>
</html>
