<?php session_start(); ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="css/styleAdmin.css" />
        <title>Vanestarre</title>
    </head>
    <body>
        <?php
        $bdd = new PDO('mysql:host=mysql-vanestarre-edyment.alwaysdata.net;dbname=vanestarre-edyment_projet;charset=utf8', '225207', '#clementeddy13',array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        ?>
        <header id="banniere">
            <h1><a href="index.php">VANESTARRE</a></h1>
            <nav id="liens">
                <?php
                echo "<a href='index.php'>Retour</a>";
                ?>
            </nav>
        </header>
        <section id="posts_index">
            <h2>Résultats : </h2>
                <?php
                function nbEmoji ($emoji, $id_post)
                {
                    $bdd = new PDO('mysql:host=mysql-vanestarre-edyment.alwaysdata.net;dbname=vanestarre-edyment_projet;charset=utf8', '225207', '#clementeddy13',array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
                    $reponse = $bdd->prepare('SELECT COUNT(id) FROM reactions WHERE emoji=:emoji AND id_post=:id_post');
                    $reponse->execute(array('emoji'=>$emoji,'id_post'=>$id_post));
                    while ($donnees = $reponse->fetch())
                        $nbEmoji = $donnees['COUNT(id)'];
                    $reponse->closeCursor();
                    return $nbEmoji;
                }

                if (!isset($_POST['rechercheTag']) || $_POST['rechercheTag'] == '')
                    header('Location: index.php?rechercheError=1');

                //Affichage des posts selon leur tag
                $reponseTag = $bdd->prepare('SELECT * FROM posts WHERE tag = :tag ');
                $reponseTag->execute(array('tag'=>$_POST['rechercheTag']));
                while ($donneesTag = $reponseTag->fetch())
                {
                    $id_post=$donneesTag['id'];

                    $nbLove = nbEmoji('Love', $id_post);
                    $nbCute = nbEmoji('Cute', $id_post);
                    $nbTropStyle = nbEmoji('TropStyle', $id_post);
                    $nbSwag = nbEmoji('Swag', $id_post);
                    ?>
                    <article id="entier">
                        <?php
                        //Affichage d'un post
                        echo '<h3>'. $donneesTag['titre'] .' - le '. $donneesTag['date_creation'] .' | écrit par '.$donneesTag['auteur']. '</h3>';
                        echo '<p>'. $donneesTag['contenu'] .'<br />';
                        echo '<p>Tag : ' .$donneesTag['tag']. '<br />';
                        $reponseImg = $bdd->prepare('SELECT * FROM images WHERE id_post = :id_post');
                        $reponseImg->execute(array('id_post'=>$id_post));
                        while ($donneesImg = $reponseImg->fetch())
                        {
                            if ($donneesImg['url_img'] != '')
                            {
                                echo "<img height='500rem' src=".$donneesImg['url_img']." />";
                            }
                        }
                        ?>
                    </article>
                    <?php

                    //Système d'affichage des réactions
                    $reponse = $bdd->prepare('SELECT COUNT(id_auteur) FROM reactions WHERE id_post=:id_post');
                    $reponse->execute(array('id_post'=>$id_post));
                    while ($donnees = $reponse->fetch())
                    {
                        if($donnees['COUNT(id_auteur)'] == 0 && isset($_SESSION['id']))
                        {
                            $afficheReact = 1; 
                        }
                        else
                        {
                            $afficheReact = 0;
                        }
                    }
                    $reponse->closeCursor();

                    $reponse = $bdd->prepare('SELECT id_auteur, emoji FROM reactions WHERE id_post=:id_post ');
                    $reponse->execute(array('id_post'=>$id_post));
                    while ($donnees = $reponse->fetch())
                    {
                        $emojiPost = $donnees['emoji'];
                        if (isset($_SESSION['id']) && $donnees['id_auteur'] == $_SESSION['id'])
                        {
                            $afficheReact = 0;
                            break;
                        }
                        elseif (isset($_SESSION['id']) && $donnees['id_auteur'] != $_SESSION['id'])
                        {
                            $afficheReact = 1;
                        }
                    }
                    $reponse->closeCursor();
                    ?>
                    <article id="reaction">
                        <?php
                        if (isset($_SESSION['id']) && $afficheReact == 1)
                        {
                            echo "<a href='traitement.php?id_post=". $id_post ."&react=Love'><img src='image/love.png' alt='Love' height=25px>".$nbLove."</a>";
                            echo "<a href='traitement.php?id_post=". $id_post ."&react=Cute'><img src='image/cute.png' alt='Cute' height=25px>".$nbCute."</a>";
                            echo "<a href='traitement.php?id_post=". $id_post ."&react=TropStyle'><img src='image/tropstyle.png' alt='TropStyle' height=25px>".$nbTropStyle."</a>";
                            echo "<a href='traitement.php?id_post=". $id_post ."&react=Swag'><img src='image/swag.png' alt='Swag' height=28px>".$nbSwag."</a>";
                        }
                        elseif (isset($_SESSION['id']) && $afficheReact == 0)
                        {
                            echo "<a href='traitement.php?supp_react=1&id_post=". $id_post ."'><img src='image/love.png' alt='Love' height=20px>".$nbLove;
                            echo "<a href='traitement.php?supp_react=1&id_post=". $id_post ."'><img src='image/cute.png' alt='Cute' height=20px>".$nbCute;
                            echo "<a href='traitement.php?supp_react=1&id_post=". $id_post ."'><img src='image/tropstyle.png' alt='TropStyle' height=20px>".$nbTropStyle;
                            echo "<a href='traitement.php?supp_react=1&id_post=". $id_post ."'><img src='image/swag.png' alt='Swag' height=20px>".$nbSwag."<br />";
                            //echo "Vous avez attribué l'emoji : ".$emojiPost."  ";
                            echo "<a href='traitement.php?supp_react=1&id_post=". $id_post ."'>Effacer </a>";
                        }
                        elseif (!isset($_SESSION['id']))
                        {
                            echo "<a href='connexion.php'>Se connecter pour réagir à ce message</a>";
                        }
                        ?>
                    </article>
                    <?php
                }
                $reponseTag->closeCursor();
                ?>
        </section>
    </body>
</html>