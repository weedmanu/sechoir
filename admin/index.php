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

// Si on reçoit quelque chose du formulaire (play stop))
 if(!empty($_POST['cam']))  {
	 //	 adresse ip du serveur (sevocam.py)
	 $host = "192.168.0.25";
	 //	 port du serveur (sevocam.py)
	 $port = 9999;
	 // création du socket
	 $socket = socket_create(AF_INET, SOCK_STREAM,0) or die("Impossible de créer le socket\n");
	 // ouverture de la connection
	 socket_connect ($socket, $host, $port ) ;
	// on place ce que l'on a reçu dans une variable
	 $form = $_POST['cam'];
	// si on reçoit play
	if ($form == 'start') {
		// la variable data vaut 6
		$data = 'start';
		// envoi cette variable par le socket au serveur (servocam.py)
		socket_write($socket, $data, strlen ($data)) or die("Impossible d'écrie des datas, le programme sechoir.py doit être fermé !!!!'\n");
		// attend 1 seconde avant d'afficher (pour être sur que tout soit lanncé)
		sleep(1);
	}
	// si on reçoit stop
	if ($form == 'stop') {
		// la variable data vaut 7
		$data = 'stop';
		// envoi cette variable par le socket au serveur (servocam.py)
		socket_write($socket, $data, strlen ($data)) or die("Impossible d'écrie des datas, le programme sechoir.py doit être fermé !!!!'\n");		
		sleep(1);
	}
	// on ferme ce socket
	socket_close($socket) ;
}
?>


<!doctype html>
<html lang="fr">
<head>
	<meta charset="utf-8">
	<title>Admin</title>	
	<link href="index.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="../lib/dateheure.js"></script>
	<script type="text/javascript" src="../lib/jquery.js"></script>

	<script>
	$(document).ready(
			function() {
				setInterval(function() {
					$('#loadavg').load('loadavg.php').fadeIn("fast");
					$('#mem').load('mem.php').fadeIn("fast");						
					$('#cpu').load('tempcpu.php').fadeIn("fast");													                     
				}, 1500);			    
			});
	 $(document).ready(
			function() {
				setInterval(function() {
					$('#bdd').load('bdd.php').fadeIn("fast");									                     
				}, 10000);			    
			});
	</script>					
</head>
<body>

	<header>  
		<div class="element" class="d" id="date"><script type="text/javascript">window.onload = date('date');</script></div>   	
		<div class="element" class="h" id="heure"><script type="text/javascript">window.onload = heure('heure');</script></div>
	</header>

	<div class="wrapper">
		<article>   			
			
		</article>
   
		<nav>
			<?php require'config.php';?>
		</nav>
    
		<aside>    
			<h2>PI monitor</h2>    
			<div class="element" id="model"><?php require'model.php';?></div>			
			<div class="element" id="cpu"><?php require'tempcpu.php';?></div>		
			<div class="element" id="loadavg"><?php require'loadavg.php';?></div>		
			<div class="element" id="mem"><?php require'mem.php';?></div>		
			<div class="element" ><div id="bdd"><?php require'bdd.php';?></div></div>	
			<h2>Commande</h2>
			<div class="element" > 
				<!-- bouton play -->
				<form class="form" method="post" action="index.php"> 		
					<input type="hidden" name="cam" value="start"><br>
					<input type="submit" name="play" id="play" value="start" />
				</form>		

				<!-- bouton stop -->		
				<form class="form" method="post" action="index.php"> 		
					<input type="hidden" name="cam" value="stop"><br>
					<input type="submit" name="stop" id="stop" value="stop" />
				</form>	
			</div>		   
		</aside>
	</div> 

	<footer>  
		<div class="element2">				
			<a href="../index.php" title="Accueil" style="text-decoration:none"><div id="accueil">Accueil</div></a>	
		</div>
	
		<p class="element2" id="phpmyadmin"><a href="<?php echo 'http://'.$ipdupi.'/phpmyadmin' ;?>" target="_blank" title="PhpMyAdmin" style="text-decoration:none">PhpMyAdmin</a></p>	
	
		<div class="element2" >
			<a href="logout.php" title="logout" style="text-decoration:none"><div id="logout">déconnexion</div></a>	
		</div>  
  </footer>
  
</body>
</html>
