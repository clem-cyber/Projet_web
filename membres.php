<?php
session_start();
$bdd = new PDO('mysql:host=mysql-vanestarre-edyment.alwaysdata.net;dbname=vanestarre-edyment_projet;charset=utf8', '225207', '#clementeddy13',array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$reponse = $bdd->prepare('SELECT droits FROM utilisateurs WHERE id=:id');
$reponse->execute(array('id'=>$_SESSION['id']));
while ($donnees = $reponse->fetch())
{
    $droits = $donnees['droits'];
}
if ($droits != 2)
	header('Location: admin.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="css/styleAdmin.css" />
        <title>Membres</title>
    </head>
    <body>
    	<header id="banniere">
            <h1><a href="index.php">VANESTARRE</a></h1>
            <nav id="liens">
                <a href="admin.php">Retour</a>
            </nav>
        </header>
    	<section id="membres">
            <h2>Membres et permissions</h2>
            <article>
            		<?php
            		$reponse = $bdd->query('SELECT pseudo, droits, id FROM utilisateurs ORDER BY id DESC');
        		    while ($donnees = $reponse->fetch())
        		    {
        		    	if ($donnees['droits'] == 0)
        		    	{
                            echo '<div id="users">';
        		    		echo '<p>'. $donnees['pseudo'] .' : Aucun droit<p>';
        		    		echo "<a href='traitement.php?droit=1&id_util=".$donnees['id']."'> Ajouter le droit de poster</a>";
                            echo '</div>';
        		    	}
        		        else if ($donnees['droits'] == 1)
        		        {
        		    		echo '<p>'. $donnees['pseudo'] .' : Droit de poster<p>';
        		    		echo "<a href='traitement.php?droit=0&id_util=".$donnees['id']."'> Retirer le droit de poster</a>";
        		    	}
        		    }
        		    ?>
            </article>
    	</section>
    </body>
</html>