<?php 
    // on récupère les infos dans config.json
$json = file_get_contents("/var/www/html/sechoir/admin/config.json");
$config = json_decode($json);

// on passe en variable php les champs qui nous intéressent
$login = $config->{'mysql'}->{'loginmysql'};
$mdp = $config->{'mysql'}->{'mdpmysql'};
$BDD = $config->{'mysql'}->{'bdd'};

// On récupère les variables de connexion à la base de donnée
define('DB_HOST' , 'localhost');		// l'adresse de mysql
define('DB_NAME' , $BDD);				// le nom de la database
define('DB_USER' , $login); 			// votre login de la base de donnée
define('DB_PASS' , $mdp);		 	    // votre mdp

?>
