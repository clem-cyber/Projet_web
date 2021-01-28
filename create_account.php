<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="css/styleAdmin.css" />
        <title>Création du compte</title>
    </head>
    <body>
        <header id="banniere_connexion">
            <h1><a href="index.php">Bienvenue chez Vanestarre</a></h1>
        </header>
        <div id="creation">
            <form id="create_Acc" action="create_account.php?create=1" method="post">
                    <fieldset>
                       <legend>Informations personnelles</legend>

                       <label for="pseudo">Pseudo : </label>
                       <input type="text" name="pseudo" id="pseudo" required />
                 
                       <label for="email">Adresse e-mail : </label>
                       <input type="email" name="email" id="email" required />

                       <label for="password">Mot de passe : </label>
                       <input type="password" name="password" id="password" required />

                       <label for="descrip">Description :</label><br />
                       <textarea name='descrip' id='descrip' rows='3' cols='30' maxlength='20' required></textarea><br />
                    </fieldset>
                    <input type="submit" name="Creation" id="Creation" value="Créer">
            </form>
        </div>
        <?php
        if (isset($_GET['create']) AND $_GET['create']==1)
        {
            $bdd = new PDO('mysql:host=mysql-vanestarre-edyment.alwaysdata.net;dbname=vanestarre-edyment_projet;charset=utf8', '225207', '#clementeddy13',array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            $req = $bdd->prepare('INSERT INTO utilisateurs(pseudo, mot_passe, email, descrip, droits) VALUES(:pseudo, :mot_passe, :email, :descrip, :droits)');
            $req->execute(array(
                'pseudo' => $_POST['pseudo'],
                'mot_passe' => md5($_POST['password']),
                'email' => $_POST['email'],
                'descrip' => $_POST['descrip'],
                'droits' => 0
                ));

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

            //Envoi du mail contenant l'identifiant
            $to = $_POST['email'];
            $subject = 'Vérification adresse email';
             $message = '
                         <html>
                          <head>
                           <title>Vérification de votre adresse email</title>
                          </head>
                          <body>
                           <h1>Bienvenue chez Vanestarre !</h1>
                           <h3>Votre compe Vanestarre a bien été créé !</h3>
                           <p>Vous trouverez ci-dessous votre identifiant de connexion</p><br />
                           <p>Identifiant : ';

            $message .= $identifiantMail;

            $message .= '</p><a href="http://vanestarre-edyment.alwaysdata.net/connexion.php"> Cliquez ici pour vous connectez dès maintenant</a>
                          </body>
                         </html>
                         ';

            $headers = 'Content-type: text/html; charset=utf-8';

            if (mail($to, $subject, $message,$headers)) 
            {
              echo "Email envoyé avec succès";
            } 
            else 
            {
              echo "Échec de l'envoi de l'email...";
            }
            header('Location: connexion.php?create=1');
        }
        ?>
    </body>
</html>