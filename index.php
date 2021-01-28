<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
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
            <!-- formulaire caché par défaut pour faire un recherche par tag -->
            <!-- Ce formulaire peut être rendu visible grâce au script défini plus loin -->
            <nav id="liens">
                <form action="rechercher.php" method="post">
                    <p>
                        <input type="button" name="Recherche" value="Recherche" id="recherche" onclick="discoverThis('rechercher', 'rechercheTag', 'recherche')" />
                        <input type="text" name="rechercheTag" id="rechercheTag" placeholder="Tag" style="display: none;" />
                        <input type="submit" name="Rechercher" value="Rechercher" id="rechercher" style="display: none;" />
                    </p>
                </form>
                <?php
                //Gestion de l'erreur de la barre de recherche vide
                if (isset($_GET['rechercheError']) && $_GET['rechercheError'] == 1)
                    echo 'Erreur de recherche<br />';

                //Si le bouton déconnexion a été cliqué la session est détruite
                if (isset($_GET['deco']) AND $_GET['deco'] == 1)
                { 
                    unset($_SESSION['id']);
                    session_destroy();
                }

                //Si la session n'existe pas la connexion est proposée sur la bannière
                if (!isset($_SESSION['id']))
                {
                    echo "<a href='connexion.php'>Connexion</a>";
                }
                //Si la session existe la déconnexion est proposée ou l'accès au compte
                else if (isset($_SESSION['id']))
                {
                    echo "<a href='index.php?deco=1'>Déconnexion </a>";
                    echo "<a href='admin.php'>Mon Compte</a>";
                }
                ?>
            </nav>
        </header>
        <section id="posts_index">
            <!-- Affichage des derniers posts -->
            <h2>Les derniers posts : </h2>
            <?php

            //Si lorsque la réaction "Love" est sélectionnée c'est la réaction n
            //L'utilisateur qui l'a faite doit faire un don
            if (isset($_GET['random']) && $_GET['random'] == 1)
            {
                //Script avertissant par une boîte de dialogue du don à effectuer
                ?>
                <script> alert("VOUS DEVEZ 10 BITCOINS A VANESTARRE"); </script> 
                <?php
            }
            //Déclaration des variables nécessaires à la pagination
            $pagination = 0;
            $nbPage = 1;
            $postParPage = 0;

            // la page par défaut est la page 1
            if (!isset($_GET['page']))
                $nbPage = 1;
            else
                // Sinon c'est la page sélectionnée
                $nbPage = $_GET['page'];

            //Attribution des valeurs des paramètres de la BD aux variables définies
            $reponse = $bdd->query('SELECT pagination FROM parametre');
            while ($donnees = $reponse->fetch())
            {
                $pagination = $pagination + (($nbPage-1) * $donnees['pagination']);
                $postParPage = $donnees['pagination'];
            }
            $reponse->closeCursor();

            //Cette fonction retourne le nombre d'emoji ayant une signification donnée pour un post donné
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

            //Affichage des posts présents dans la BD selon la pagination et la page
            $reponsePost = $bdd->query('SELECT * FROM posts ORDER BY id DESC LIMIT '. $pagination .','.$postParPage.'');
            while ($donneesPost = $reponsePost->fetch())
            {
                $id_post=$donneesPost['id'];

                //Calcul du nombre de chaque emoji pour le post donné
                $nbLove = nbEmoji('Love', $id_post);
                $nbCute = nbEmoji('Cute', $id_post);
                $nbTropStyle = nbEmoji('TropStyle', $id_post);
                $nbSwag = nbEmoji('Swag', $id_post);
                ?>
                <article class="entier">
                    <?php
                    //Affichage des posts
                    echo '<h3>'. $donneesPost['titre'] .' - le '. $donneesPost['date_creation'] .' | écrit par '.$donneesPost['auteur']. '</h3>';
                    echo '<p>'. $donneesPost['contenu'] .'<br />';
                    //Affichage du tag s'il existe
                    if ($donneesPost['tag'] != '')
                        echo '<p>β' .$donneesPost['tag']. '<br /><br />';
                    //Affichage de l'image ou de rien s'il n'y en a pas
                    $reponseImg = $bdd->prepare('SELECT * FROM images WHERE id_post = :id_post');
                    $reponseImg->execute(array('id_post'=>$donneesPost['id']));
                    while ($donneesImg = $reponseImg->fetch())
                    {
                        if ($donneesImg['url_img'] != '')
                        {
                            echo "<img height='500' src=".$donneesImg['url_img']."  alt='Image du post indisponible' />";
                        }
                    }

                    ?>  
                </article>
                <?php
                    //Contrôle du nombre de réaction donnée par un utilisateur pour un post donné
                    $reponse = $bdd->prepare('SELECT COUNT(id_auteur) FROM reactions WHERE id_post=:id_post');
                    $reponse->execute(array('id_post'=>$id_post));
                    while ($donnees = $reponse->fetch())
                    {
                        //Si ce nombre est égal à 0 et que cette utilisateur est connecté alors il pourra réagir
                        if($donnees['COUNT(id_auteur)'] == 0 && isset($_SESSION['id']))
                        {
                            $afficheReact = 1; 
                        }
                        //Sinon il ne pourra pas
                        else
                        {
                            $afficheReact = 0;
                        }
                    }
                    $reponse->closeCursor();

                    $reponse = $bdd->prepare('SELECT id_auteur, emoji FROM reactions WHERE id_post=:id_post');
                    $reponse->execute(array('id_post'=>$id_post));
                    while ($donnees = $reponse->fetch())
                    {
                        $emojiPost = $donnees['emoji'];
                        if (isset($_SESSION['id']))
                        {
                            if ($donnees['id_auteur'] == $_SESSION['id'])
                            {
                                $afficheReact = 0;
                                break;
                            }
                            elseif ($donnees['id_auteur'] != $_SESSION['id'])
                            {
                                $afficheReact = 1;
                            }
                        }
                    }
                    $reponse->closeCursor();
                    ?>
                    <div class="reaction">
                        <?php
                        if (isset($_SESSION['id']) && $afficheReact == 1)
                        {
                            //Si l'utilisateur est connecté et qu'il peut réagir on le lui permet
                            echo "<a href='traitement.php?id_post=". $id_post ."&react=Love'><img src='image/love.png' alt='Love' height=25px>".$nbLove."</a>";
                            echo "<a href='traitement.php?id_post=". $id_post ."&react=Cute'><img src='image/cute.png' alt='Cute' height=25px>".$nbCute."</a>";
                            echo "<a href='traitement.php?id_post=". $id_post ."&react=TropStyle'><img src='image/tropstyle.png' alt='TropStyle' height=25px>".$nbTropStyle."</a>";
                            echo "<a href='traitement.php?id_post=". $id_post ."&react=Swag'><img src='image/swag.png' alt='Swag' height=28px>".$nbSwag."</a>";
                        }
                        //Sinon cliquer sur une réaction active automatiquement la suppression
                        elseif (isset($_SESSION['id']) && $afficheReact == 0)
                        {
                            echo "<a href='traitement.php?supp_react=1&id_post=". $id_post ."'><img src='image/love.png' alt='Love' height=20px>".$nbLove;
                            echo "<a href='traitement.php?supp_react=1&id_post=". $id_post ."'><img src='image/cute.png' alt='Cute' height=20px>".$nbCute;
                            echo "<a href='traitement.php?supp_react=1&id_post=". $id_post ."'><img src='image/tropstyle.png' alt='TropStyle' height=20px>".$nbTropStyle;
                            echo "<a href='traitement.php?supp_react=1&id_post=". $id_post ."'><img src='image/swag.png' alt='Swag' height=20px>".$nbSwag."<br />";
                            //echo "Vous avez attribué l'emoji : ".$emojiPost."  ";
                            echo "<a href='traitement.php?supp_react=1&id_post=". $id_post ."'>Effacer </a>";
                        }
                        //Si l'utilisateur n'est pas connecté il doit l'être pour réagir
                        elseif (!isset($_SESSION['id']))
                        {
                            echo "<a href='connexion.php'>Se connecter pour réagir à ce message</a>";
                        }
                        ?>
                    </div>
                    <?php
                }
                $reponsePost->closeCursor();
                ?>
        </section>
        <!-- Calcul et affichage du nombre de page selon la pagination-->
        <div id="nbPage">
            <?php 
            //Récupération du nombre de posts
            $reponse = $bdd->query('SELECT COUNT(id) FROM posts');
            while ($donnees = $reponse->fetch())
            {
                //Récupération de la pagination choisie (par défaut 2)
                $reponsePag = $bdd->query('SELECT pagination FROM parametre');
                while ($donneesPag = $reponsePag->fetch())
                {
                    //calcul du nombre de page(s)
                    $nbPosts = $donnees['COUNT(id)'];
                    $pagination = $donneesPag['pagination'];
                    $parPage = $nbPosts / $pagination;
                    if ($nbPosts % $pagination != 0)
                        ++$parPage;
                    //affichage des numéro de page
                    for ($i = 1 ; $i <= $parPage ; ++$i)
                        echo "<a href='index.php?page=".$i."'>".$i."</a>";
                }
            }
            $reponse->closeCursor();
            ?>
        </div>
        <!-- Script servant à cacher ou montrer des éléments-->
        <script>
            function discoverThis(_div, _divD, _divT)
                {
                var obj = document.getElementById(_div);
                var objD = document.getElementById(_divD);
                var objT = document.getElementById(_divT);
                if(obj.style.display == "block")
                {
                    obj.style.display = "none";
                    objD.style.display = "none";
                    objT.style.display = "block";
                }
                else
                {
                    obj.style.display = "block";
                    objD.style.display = "block";
                    objT.style.display = "none";
                }
                }
        </script>
    </body>
</html>