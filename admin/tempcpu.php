<?php


// On prolonge la session
session_start();

// On teste si la variable de session existe et contient une valeur
if(empty($_SESSION['login'])) 
{
  // Si inexistante ou nulle, on redirige vers le formulaire de login
  header('Location: auth.php');
  exit();
}

require 'connexion.php';

try {    
    $PDO = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
    $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);	

} catch(Exception $e) {
    echo "Impossible de se connecter à la base de données '".DB_NAME."' sur ".DB_HOST." avec le compte utilisateur '".DB_USER."'";
    echo "<br/>Erreur PDO : <i>".$e->getMessage()."</i>";
    die();
}
	
$reponse = $PDO->query("SELECT warmpi FROM config");	

while ($donnees = $reponse->fetch())
{	
	$warmpi = $donnees['warmpi'];
}
$reponse->closeCursor();


 //On exécute la commande de récupérage (si si) de température
 $temp = exec('cat /sys/class/thermal/thermal_zone0/temp');
 //On divise par 1000 pour convertir
 $tempconv  =  $temp / 1000;
 //Un chiffre après la virgule ça suffit
 $temppi = round($tempconv,1);
 //Si la température < 65°C alors on affiche en vert, sinon en rouge
 echo 'Temp CPU</br>';
 if ($temppi < $warmpi) {
  echo $temppi;
  echo ' °C';  
 } 
 if ($temppi > $warmpi) {
  echo $temppi ;
  echo ' °C';
  echo '<link href="KO.css" rel="stylesheet" type="text/css" />';
}

?>
