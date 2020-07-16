<?php

require 'connexion.php';

try {    
    $PDO = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
    $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);	

} catch(Exception $e) {
    echo "Impossible de se connecter à la base de données '".DB_NAME."' sur ".DB_HOST." avec le compte utilisateur '".DB_USER."'";
    echo "<br/>Erreur PDO : <i>".$e->getMessage()."</i>";
    die();
}
	
$reponse = $PDO->query("SELECT loginadmin, mdpadmin FROM config");	

while ($donnees = $reponse->fetch())
{	
	
	$loginadmin = $donnees['loginadmin'];
	$mdpadmin = $donnees['mdpadmin'];
	
}
$reponse->closeCursor();

  // Definition des constantes et variables
  define('LOGIN',$loginadmin);   
  define('PASSWORD',$mdpadmin); 
  $errorMessage = '';
 
  // Test de l'envoi du formulaire
  if(!empty($_POST)) 
  {
    // Les identifiants sont transmis ?
    if(!empty($_POST['login']) && !empty($_POST['password'])) 
    {
      // Sont-ils les mêmes que les constantes ?
      if($_POST['login'] !== LOGIN) 
      {
        $errorMessage = 'Mauvais login !';
      }
        elseif($_POST['password'] !== PASSWORD) 
      {  
        $errorMessage = 'Mauvais password !';
      }
        else
      {
        // On ouvre la session
        session_start();
        // On enregistre le login en session
        $_SESSION['login'] = LOGIN;
        // On redirige vers le fichier index.php
        header('Location: index.php');
        exit();
      }
    }
      else
    {
      $errorMessage = 'Veuillez inscrire vos identifiants svp !';
    }
  }
?>

<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>authentification</title>
  <link rel="icon" type="image/png" href="img/auth.png" />
  <link rel="stylesheet" href="auth.css">
  </head>
  <body>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
      
        <label>Identifiez-vous :</label>
        <?php
          // Rencontre-t-on une erreur ?
          if(!empty($errorMessage)) 
          {
            echo '<p>', htmlspecialchars($errorMessage) ,'</p>';
          }
        ?>
       <p>
          <label for="login">Login :</label> 
          <input type="text" name="login" id="login" value="" />
        </p>
        <p>
          <label for="password">Password :</label> 
          <input type="password" name="password" id="password" value=""/> 
          <input type="submit" name="submit" value="Se logguer" />
        </p>
      
    </form>

<a href="../index.php" style="text-decoration:none" id="accueil" >Accueil</a>

  </body>
</html>

