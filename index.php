<?php
// On prolonge la session
session_start();

?>

<!doctype html>
<html lang="fr">
<head>
	<meta charset="utf-8">
	<title>Admin</title>	
	<link href="indexadminOK.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="lib/dateheure.js"></script>
	<script type="text/javascript" src="lib/jquery.js"></script>	
	<script src="lib/RGraph.common.core.js"></script> <!-- appel de la librairie Rgraph --> 
	<script src="lib/RGraph.common.csv.js"></script> <!-- appel de la librairie Rgraph --> 	
	<script src="lib/RGraph.common.dynamic"></script> <!-- appel de la librairie Rgraph --> 
	<script src="lib/RGraph.common.effects.js"></script> <!-- appel de la librairie Rgraph --> 	
	<script src="lib/RGraph.gauge.js"></script> <!-- appel de la librairie Rgraph --> 
	<link rel="stylesheet" href="index.css" /> <!-- appel du thème de la page -->  
	<script>
    $(document).ready(
            function() {
                setInterval(function() {
					$('#gauge').load('jauge.php').fadeIn("fast");								                     
                }, 10000);
            });
	</script>					
</head>
<body>

<header>  
	<div class="conteneurH">   		
		<div class="element" id="date">								
			<script type="text/javascript">window.onload = date('date');</script>							
		</div>														
		<div class="element" id="heure">
			<script type="text/javascript">window.onload = heure('heure');</script>
		</div>      
	</div>		
</header>
        
        
<main>	
	<h1>Humibox</h1>		
	<div class="conteneurH">  							
		<div class="element" id="gauge"><?php require 'jauge.php'; ?></div> <!-- contiendra les jauges -->			
	</div>   		 
</main>       
        
        
 <footer>	
	 <div class="conteneurH">	 
		<div class="element" id="serpent">
			<a href="histo.php" style="text-decoration:none"><span id="histo">Historique</span></a> <!--lien vers la page historique-->
		</div> 	
		<div class="element" id="pc">
			<a href="admin/index.php" style="text-decoration:none"><span id="admin">Admin</span></a><!--lien vers la page admin-->
		</div>         				
	</div>	     
</footer>

</body>
</html>



