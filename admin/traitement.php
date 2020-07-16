<?php

require 'connexion.php';

$dateetheure = date("Y-m-d-H:i");

Print("Nous sommes le $dateetheure <br/>");  

$loginadmin = htmlspecialchars($_POST['loginadmin']);
$mdpadmin = htmlspecialchars($_POST['mdpadmin']);
$target_humi = htmlspecialchars($_POST['target_humi']);
$target_temp = htmlspecialchars($_POST['target_temp']);
$temp_min = htmlspecialchars($_POST['temp_min']);
$temp_max = htmlspecialchars($_POST['temp_max']);
$warmpi = htmlspecialchars($_POST['warmpi']);
$envoyeur = htmlspecialchars($_POST['envoyeur']);
$mdpenvoyeur = htmlspecialchars($_POST['mdpenvoyeur']);
$receveur = htmlspecialchars($_POST['receveur']);
$ipdupi = htmlspecialchars($_POST['ipdupi']);
 	

// On essaye de se connecter à la base de donnée
try {    
	$PDO = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
	$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING); 
	
	$resultats = $PDO->prepare("INSERT INTO config (loginadmin, mdpadmin, target_humi, target_temp, temp_min, temp_max, warmpi, envoyeur, mdpenvoyeur, receveur, ip) values (:loginadmin, :mdpadmin, :target_humi, :target_temp, :temp_min, :temp_max, :warmpi, :envoyeur, :mdpenvoyeur, :receveur, :ipdupi)");		

	$resultats->bindParam(':loginadmin', $loginadmin);
	$resultats->bindParam(':mdpadmin', $mdpadmin);
	$resultats->bindParam(':target_humi', $target_humi);
	$resultats->bindParam(':target_temp', $target_temp);
	$resultats->bindParam(':temp_min', $temp_min);
	$resultats->bindParam(':temp_max', $temp_max);
	$resultats->bindParam(':warmpi', $warmpi);
	$resultats->bindParam(':envoyeur', $envoyeur);
	$resultats->bindParam(':mdpenvoyeur', $mdpenvoyeur);
	$resultats->bindParam(':receveur', $receveur);
	$resultats->bindParam(':ipdupi', $ipdupi);

	$resultats->execute();					
	$resultats->closeCursor();	


// Si on n' arrive pas a se connecter à la base de donnée on affiche l' erreur
} catch(Exception $e) {
	echo "Impossible de se connecter à la base de données '".DB_NAME."' sur ".DB_HOST." avec le compte utilisateur '".DB_USER."'";
	echo "<br/>Erreur PDO : <i>".$e->getMessage()."</i>";
	die();
}

echo "Vos parametre on bien été prise encompte :<br/>";	
		
  
echo "<p>

	$target_humi <br/>
	$target_temp <br/>
	$temp_min <br/>
	$temp_max <br/>
	$warmpi <br/>
	$envoyeur <br/>
	$mdpenvoyeur <br/>
	$receveur <br/>
	$loginadmin <br/>
	$mdpadmin <br/>	
	$ipdupi
	</p>
	";
	

try {    
    $PDO = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
    $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
 
    $sql = 'SELECT COUNT(*) AS nb FROM config';
    $result = $PDO->query($sql);
    $columns = $result->fetch();
    $nb = $columns['nb'];
    $ef = $nb - 1;

	$req = $PDO->exec("DELETE from config ORDER BY dateetheure ASC LIMIT $ef");
	
	$result->closeCursor();
     
   echo 'il y a '.$nb.' entrées dans la base de donnée';
   
 
} catch(Exception $e) {
    echo "Impossible de se connecter à la base de données '".DB_NAME."' sur ".DB_HOST." avec le compte utilisateur '".DB_USER."'";
    echo "<br/>Erreur PDO : <i>".$e->getMessage()."</i>";
    die();
}	


$delai=5; // le nombre de secondes
$url='index.php'; // ton url
header("Refresh: $delai;url=$url");
    
    
?>
       

