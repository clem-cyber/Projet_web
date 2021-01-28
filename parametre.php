<?php
session_start();
$bdd = new PDO('mysql:host=mysql-vanestarre-edyment.alwaysdata.net;dbname=vanestarre-edyment_projet;charset=utf8', '225207', '#clementeddy13',array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="css/styleAdmin.css" />
        <title>Paramètre du compte</title>
    </head>
    <body>
        <header id="banniere">
            <h1><a href="index.php">VANESTARRE</a></h1>
            <nav id="liens">
                <a href="admin.php">Retour</a>
            </nav>
        </header>
        <div id="posts_index">
            <article>
                <h2>Paramètres</h2>
                <?php
                $reponse = $bdd->prepare('SELECT id, descrip, pseudo, email FROM utilisateurs WHERE id=:id');
                $reponse->execute(array('id'=>$_SESSION['id']));
                while ($donnees = $reponse->fetch())
                {
                    $ancien_description=$donnees['descrip'];
                    $ancien_pseudo=$donnees['pseudo'];
                    $ancien_email=$donnees['email'];
                    ?>
                    <h3>Modification des informations :</h3>
                    <form action="traitement.php" method="post">
                        <p>
                            <label for="descrip">Description :</label><br />
                            <?php 
                            echo "<textarea type='text' name='descrip' id='descrip' rows='3' cols='30' maxlength='20'>".$ancien_description."</textarea><br />" ?>
                            <label for="descrip">Pseudo : </label>
                            <?php 
                            echo "<input type='text' name='pseudo' id='pseudo' value=".$ancien_pseudo." /><br />"; 
                            ?>
                            <label for="email">Email : </label>
                            <?php
                            echo "<input type='mail' name='email' id='email' value=".$ancien_email." /><br />";
                            ?>
                            <input type="button" value="Modifier le mot de passe"  id="modifier" name="modifier" onclick="discoverThis('ancien_mdp', 'nouveau_mdp', 'annuler', 'modifier')" /><br />
                            <input type="button" value="Annuler" id="annuler" name="annuler" onclick="discoverThis('ancien_mdp', 'nouveau_mdp', 'annuler', 'modifier')" style="display: none;" />
                            <?php
                            echo "<input type='password' name='ancien_mdp' id='ancien_mdp' value='' placeholder='Mot de passe actuel' style='display: none;' /><br />";
                            echo "<input type='password' name='nouveau_mdp' id='nouveau_mdp' value='' placeholder='Nouveau mot de passe' style='display: none;' /><br />";
                            ?>
                            <input type="submit" name="ModifierInfos" value="Modifier" />
                        </p>
                    </form>
                <?php
                }
                $reponse = $bdd->prepare('SELECT droits FROM utilisateurs WHERE id=:id');
                $reponse->execute(array('id'=>$_SESSION['id']));
                while ($donnees = $reponse->fetch())
                {
                    $droits = $donnees['droits'];
                }
                if ($droits == 2)
                {
                    $reponse = $bdd->query('SELECT minLove, maxLove, pagination FROM parametre');
                    while ($donnees = $reponse->fetch())
                    {
                        $ancien_minLove    = $donnees['minLove'];
                        $ancien_maxLove    = $donnees['maxLove'];
                        $ancien_pagination = $donnees['pagination'];
                        ?>
                        <h3>Modification des bornes aléatoires</h3>
                        <form action="traitement.php" method="post">
                            <p>
                                <label for="minLove">Minimum : </label>
                                <?php
                                echo "<input type='number' name='minLove' id='minLove' value=" .$ancien_minLove. " /><br />";
                                ?>
                                <label for="minLove">Maximum : </label>
                                <?php 
                                echo "<input type='number' name='maxLove' id='maxLove' value=" .$ancien_maxLove. " /><br />";
                                ?>
                                <label for="pagination">Post(s) par page : </label>
                                <?php 
                                echo "<input type='number' name='pagination' id='pagination' value=" .$ancien_pagination. " /><br />";
                                ?>
                                <input type="submit" name="modifierParam" value="Modifier" />
                            </p>
                        </form>
                        <?php
                        break;
                    }
                }
                ?>
            </article>
        </div>
        <script >
            function discoverThis(_div, _divD, _divT, _divQ)
            {
                var obj = document.getElementById(_div);
                var objD = document.getElementById(_divD);
                var objT = document.getElementById(_divT);
                var objQ = document.getElementById(_divQ);
                if(obj.style.display == "block")
                {
                    obj.style.display = "none";
                    objD.style.display = "none";
                    objT.style.display = "none";
                    objQ.style.display = "block";
                }
                else
                {
                    obj.style.display = "block";
                    objD.style.display = "block";
                    objT.style.display = "block";
                    objQ.style.display = "none";
                }
            }
        </script>
    </body>
</html>