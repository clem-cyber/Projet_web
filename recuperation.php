<?php session_start(); 
//Fonction de generation d'un chaine de caractère
function genererChaineAleatoire($longueur = 10)
{
    $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $longueurMax = strlen($caracteres);
    $chaineAleatoire = '';
    for ($i = 0; $i < $longueur; ++$i)
    {
    $chaineAleatoire .= $caracteres[rand(0, $longueurMax - 1)];
    }
    return $chaineAleatoire;
}
?>

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
            <h2>Récupération de votre compte</h2>
            <?php
            $bdd = new PDO('mysql:host=mysql-vanestarre-edyment.alwaysdata.net;dbname=vanestarre-edyment_projet;charset=utf8', '225207', '#clementeddy13',array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            if (!isset($_GET['recup']) && !isset($_GET['valid']))
            {
                ?>
                <form action="recuperation.php?recup=1" method="post">
                    <p>
                        <label for="email">Veuillez entrer l'adresse email liée à votre compte</label>
                        <input type="email" name="email" id="email" /><br />
                        <label for="id">Veuillez entrer votre identifiant</label>
                        <input type="number" name="id" id="id" /><br />
                        <input type="submit" name="Valider" />
                    </p>
                </form>
                <?php
            }
            if (isset($_GET['recup']) && $_GET['recup']==1)
            {
                if (isset($_POST['email']) && isset($_POST['id']))
                {
                    $req = $bdd->prepare('SELECT pseudo, descrip FROM utilisateurs WHERE email=:email AND id=:id');
                    $req->execute(array('email'=>$_POST['email'], 'id'=>$_POST['id']));
                    while ($donnees = $req->fetch())
                    {
                        ?>
                        <form action="recuperation.php?valid=1" method="post">
                            <p>
                                <?php
                                echo 'Est-ce vous ?<br />';
                                echo 'Pseudo : '.$donnees['pseudo'].'<br />';
                                echo "<input type='hidden' name='pseudo' id='pseudo' value=".$_donnees['pseudo']." />";
                                echo 'Description : '.$donnees['descrip'].'<br />';
                                echo "<input type='hidden' name='id' id='id' value=".$_POST['id']." />";
                                echo "<input type='hidden' name='email' id='email' value=".$_POST['email']." />"; 
                                ?>
                                <input type="submit" name="oui" value="Oui" />
                            </p>
                        </form>
                        <?php
                    }
                }
                else
                {
                    echo "Veuillez entrer une adresse email valide";
                }
            }

            if (isset($_GET['valid']) AND $_GET['valid']==1 && isset($_POST['pseudo']))
            {
                $nouveau_mdp = genererChaineAleatoire();
                $mdp_bd = md5($nouveau_mdp);
                $req = $bdd->prepare('UPDATE utilisateurs SET mot_passe = :mot_passe WHERE id = :id');
                $req->execute(array('mot_passe'=>$mdp_bd, 'id'=>$_POST['id']));
                echo 'Nous vous avons envoyé un mail avec votre nouveau mot de passe<br />';

                //Envoi du mail de réinitialisation du mot de passe
                $to = $_POST['email'];
                $subject = 'Réinitialisation de mot de passe';
                 $message = '
                             <html>
                              <head>
                               <title>Réinitialisation de mot de passe</title>
                              </head>
                              <body>
                               <h1>Voici votre nouveau mot de passe</h1>
                               <h3>Vous pouvez le modifier à tout moment depuis le menu Paramètre</h3>
                               <p>Nouveau mot de passe : ';

                $message .= $nouveau_mdp;

                $message .= '</p><a href="http://vanestarre-edyment.alwaysdata.net/connexion.php"> Cliquez ici pour vous connectez dès maintenant</a>
                              </body>
                             </html>
                             ';

                $headers = 'Content-type: text/html; charset=utf-8';

                if (mail($to, $subject, $message, $headers)) 
                {
                  echo "Email envoyé avec succès";
                  ?><a href="index.php">Se connecter</a><?php
                } 
                else 
                {
                  echo "Échec de l'envoi de l'email...";
                }
            }
            ?>
            <nav>
                <p><a href="connexion.php">Retour</a></p>
            </nav>
        </section>
    </body>
</html>