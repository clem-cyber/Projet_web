<?php
/** Page de traitement **/

//Démarrage de ma session et connexion à la BD
session_start();
$bdd = new PDO('mysql:host=mysql-vanestarre-edyment.alwaysdata.net;dbname=vanestarre-edyment_projet;charset=utf8', '225207', '#clementeddy13',array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

/**********************************************************************
 Vérification de l'existence de la session et récupération des données 
 **********************************************************************/
if (isset($_SESSION['id']))
{
	$id_auteur=$_SESSION['id'];
	$reponse = $bdd->prepare('SELECT pseudo, droits FROM utilisateurs WHERE id= :id_auteur');
	$reponse->execute(array('id_auteur' => $id_auteur));
	while ($donnees = $reponse->fetch())
	{
	    $auteur = $donnees['pseudo'];
	    $droits = $donnees['droits'];
	}
}
else
	header('Location: index.php');


/**********************************************************************
	Traitement de la modification des informations d'un utilisateur
 **********************************************************************/
if (isset($_POST['ModifierInfos']))
{
	$req = $bdd->prepare('UPDATE utilisateurs SET descrip = :descrip, pseudo = :pseudo, email = :email WHERE id=:id');
	$req->execute(array('descrip'=>$_POST['descrip'], 'pseudo'=>$_POST['pseudo'], 'email'=>$_POST['email'], 'id'=>$_SESSION['id']));
	if ($_POST['ancien_mdp'] != '')
	{
		$req = $bdd->prepare('SELECT mot_passe FROM utilisateurs WHERE id=:id');
		$req->execute(array('id'=>$_SESSION['id']));
		while ($donnees = $req->fetch())
		{
			if (md5($_POST['ancien_mdp']) == $donnees['mot_passe'])
			{
				$req = $bdd->prepare('UPDATE utilisateurs SET mot_passe = :mot_passe WHERE id= \''. $_SESSION['id'] .'\'');
				$req->execute(array('mot_passe'=>md5($_POST['nouveau_mdp'])));
				header('Location: admin.php');
			}
			elseif (md5($_POST['ancien_mdp']) != $donnees['mot_passe'])
			{
				header('Location: parametre.php?error=1');
			}
		}
	}
	else
	{
		header('Location: admin.php');
	}
}


/**********************************************************************
				Augmentation des droits d'un utilisateur
 **********************************************************************/
if(isset($_GET['droit']) && $droits == 2)
{
	$id_utilisateur = $_GET['id_util'];
	if($_GET['droit'] == 1)
	{
		$req = $bdd->prepare('UPDATE utilisateurs SET droits = 1 WHERE id=:id');
		$req->execute(array('id'=>$id_utilisateur));
	}
	else if($_GET['droit'] == 0)
	{
		$req = $bdd->prepare('UPDATE utilisateurs SET droits = 0 WHERE id=:id');
		$req->execute(array('id'=>$id_utilisateur));
	}
	header('Location: admin.php');
}



/**********************************************************************
			Traitement des formulaires concernant les posts
 **********************************************************************/
if(isset($_POST['titre']) AND isset($_POST['contenu']))
{

	//Traitement du formulaire d'un nouveau post
    if (isset($_POST['Poster']))
    {
	    $date_creation='2001-01-01 00:00:00';
		$req = $bdd->prepare('INSERT INTO posts(titre, contenu, date_creation, id_auteur, auteur, nbLove, tag) VALUES(:titre, :contenu, :date_creation, :id_auteur, :auteur, :nbLove, :tag)');
		$req->execute(array(
		'titre'=>$_POST['titre'],
		'contenu'=>$_POST['contenu'],
		'date_creation'=>$date_creation,
		'id_auteur'=>$id_auteur,
		'auteur'=>$auteur,
		'nbLove'=> 0,
		'tag'=>$_POST['tag']
		));
		$reponse = $bdd->query('SELECT id FROM posts WHERE date_creation="2001-01-01 00:00:00"');
	    while ($donnees = $reponse->fetch())
	    {
	        $id_post = $donnees['id'];
	    }
	    $reponse->closeCursor();
	    $reponse = $bdd->query('SELECT minLove, maxLove FROM parametre');
	    while ($donnees = $reponse->fetch())
	    {
	       	$nbLove = rand($donnees['minLove'], $donnees['maxLove']);
	       	$req = $bdd->prepare('UPDATE posts SET nbLove = :nbLove WHERE id= :id');
	       	$req->execute(array('nbLove' => $nbLove, 'id' => $id_post));
	    }
	    $reponse->closeCursor();
		$req = $bdd->prepare('UPDATE posts SET date_creation = NOW() WHERE id= :id');
		$req->execute(array('id' => $id_post));

		$imageFileType = strtolower(pathinfo(basename($_FILES["upload"]["name"]),PATHINFO_EXTENSION));
		$target_dir = "./img_upload";
		$imgname = $_GET['idmsg'] .'.' . $imageFileType;

		// vérification de la taille du fichier
		if ($_FILES["file"]["size"] > 500000) 
		{
		    $uploadOk = 0;
		    echo 'le fichier est trop grand';
		}

		// vérification du format du fichier (on acceptera ici que les images et gifs)
		if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
		    && $imageFileType != "gif" ) {
		    $uploadOk = 0;
		    echo 'le fichier n\'est pas une image';
		}
		else
			$uploadOk = 1;

		//vérification de l'upload
		if ($uploadOk == 0) {
		    echo 'fichier invalide';
		}
		else
		{
			$chemin = "img_upload/".$_FILES['upload']['name'];
			move_uploaded_file($_FILES["file"]["tmp_name"], $chemin);
            echo 'le fichier à bien été uploadé';
            $bdd = new PDO('mysql:host=mysql-vanestarre-edyment.alwaysdata.net;dbname=vanestarre-edyment_projet;charset=utf8', '225207', '#clementeddy13',array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            $reponse = $bdd->prepare('INSERT INTO images (url_img, id_post) VALUES(:url, :id_post)');
            $reponse->execute(array('url'=>$chemin, 'id_post'=>$id_post));
		}
	}

	//Traitement du formulaire de modification d'un post
	else if (isset($_POST['Modifier']) && $droits >= 1)
	{
		$date_creation='2001-01-01 00:00:00';
		$req = $bdd->prepare('UPDATE posts SET titre = :titre, contenu = :contenu, date_creation = :date_creation, id_auteur = :id_auteur, auteur = :auteur, tag = :tag WHERE id= :id_post');
		$req->execute(array(
		'titre'=>$_POST['titre'],
		'contenu'=>$_POST['contenu'],
		'date_creation'=>$date_creation,
		'auteur'=>$auteur,
		'id_auteur'=>$id_auteur,
		'tag'=>$_POST['tag'],
		'id_post'=>$_POST['id_post']
		));
		$req = $bdd->prepare('UPDATE posts SET date_creation = NOW() WHERE id= :id_post');
		$req->execute(array('id_post'=>$_POST['id_post']));
	}
	header('Location: admin.php');
}


/**********************************************************************
				Traitement de la suppression d'un post
 **********************************************************************/
if (isset($_GET['supp']) && isset($_GET['id_post']))
{
	$reponse = $bdd->prepare('SELECT id FROM posts WHERE id_auteur = :id_auteur');
	$reponse->execute(array('id_auteur'=>$id_auteur));
	while ($donnees = $reponse->fetch())
	{
	    if ($_GET['id_post'] == $donnees['id'])
	    {
	    	$id_post = $_GET['id_post'];
			$req = $bdd->prepare('DELETE FROM posts WHERE id = :id_post');
			$req->execute(array('id_post'=>$id_post));
			$req = $bdd->prepare('DELETE FROM images WHERE id_post = :id_post');
			$req->execute(array('id_post'=>$id_post));
			$req = $bdd->prepare('DELETE FROM reactions WHERE id_post = :id_post');
			$req->execute(array('id_post'=>$id_post));
			header('Location: admin.php');
	    }
	}
	$reponse->closeCursor();
}



/**********************************************************************
			Attribution de l'emoji sélectionné à l'index
 **********************************************************************/
if (isset($_GET['react']) && isset($_GET['id_post']))
{
	$react = $_GET['react'];
	$id_post = $_GET['id_post'];
	//Insertion d'un nouvel emoji pour un post
	$reponse = $bdd->prepare('SELECT COUNT(id_auteur), id_auteur FROM reactions WHERE id_post = :id_post');
	$reponse->execute(array('id_post'=>$id_post));
	while ($donnees = $reponse->fetch())
	{
		if ($donnees['id_auteur'] == $_SESSION['id'])
			break;
		if($donnees['id_auteur'] != $_SESSION['id'])
		{
			$req = $bdd->prepare('INSERT INTO reactions(id_post, emoji, id_auteur) VALUES(:id_post, :emoji, :id_auteur)');
			$req->execute(array('id_post'=>$id_post, 'emoji'=>$react, 'id_auteur'=>$id_auteur));	
		}
	}
	$reponse->closeCursor();

	//Vérification que cette insertion ne soit pas celle du don obligatoire de 10B
    $reponse = $bdd->prepare('SELECT COUNT(id) FROM reactions WHERE emoji="Love" AND id_post=:id_post');
    $reponse->execute(array('id_post'=>$id_post));
    while ($donnees = $reponse->fetch())
    {
        $nbLove = $donnees['COUNT(id)'];
        $reponses = $bdd->prepare('SELECT nbLove FROM posts WHERE id=:id_post');
        $reponses->execute(array('id_post'=>$id_post));
	    while ($donnees = $reponses->fetch())
	    {
	    	if ($nbLove == $donnees['nbLove'])
	    		header('Location: index.php?random=1');
	    	else
	    		header('Location: index.php');
	    }
	    $reponses->closeCursor();
    }
    $reponse->closeCursor();
    
}

/**********************************************************************
					Suppression de l'emoji donné
 **********************************************************************/
if (isset($_GET['supp_react']) && isset($_GET['id_post']))
{
	$id_post = $_GET['id_post'];
	$reponses = $bdd->prepare('SELECT id_auteur FROM reactions WHERE id_post=:id_post');
	$reponses->execute(array('id_post'=>$id_post));
	while ($donnees = $reponses->fetch())
	{
	    if ($donnees['id_auteur'] != $_SESSION['id'])
	    {
	    	$sessionDiff = 1;
	    }
	    elseif ($donnees['id_auteur'] == $_SESSION['id'])
	    {
	    	$sessionDiff = 0;
	    }
	    if ($sessionDiff == 0)
	    {
	    	$req = $bdd->prepare('DELETE FROM reactions WHERE id_post=:id_post AND id_auteur=:id_auteur');
	    	$req->execute(array('id_post'=>$id_post, 'id_auteur'=>$_SESSION['id']));
	    }
	}
	$reponse->closeCursor();
	header('Location: index.php');
}

/*************************************************************************************
Traitement de la modification des paramètres concernant l'emoji Love et la pagination
 *************************************************************************************/
if (isset($_POST['modifierParam']))
{
	$req = $bdd->prepare('UPDATE parametre SET minLove = :minLove, maxLove = :maxLove, pagination = :pagination');
	$req->execute(array(
	'minLove'=>$_POST['minLove'],
	'maxLove'=>$_POST['maxLove'],
	'pagination'=>$_POST['pagination']
	));
	header('Location: admin.php');
}
?>