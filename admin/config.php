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
	
$reponse = $PDO->query("SELECT * FROM config");	

while ($donnees = $reponse->fetch())
{	
	
	$loginadmin = $donnees['loginadmin'];
	$mdpadmin = $donnees['mdpadmin'];;
	$target_humi = $donnees['target_humi'];
	$target_temp = $donnees['target_temp'];
	$temp_min = $donnees['temp_min'];
	$temp_max = $donnees['temp_max'];
	$warmpi = $donnees['warmpi'];
	$envoyeur = $donnees['envoyeur'];
	$mdpenvoyeur = $donnees['mdpenvoyeur'];
	$receveur = $donnees['receveur'];
	$ipdupi = $donnees['ip'];	
}
$reponse->closeCursor();
?>


<form method="post" action="traitement.php" id="form" >
		
	   <legend>consigne</legend>
       <label for="target_humi">target_humi:</label>
       <input type="number" name="target_humi" id="target_humi" value="<?php echo $target_humi ;?>" /> <br/>
       <label for="target_temp">target_temp:</label>
       <input type="number" name="target_temp" id="target_temp" value="<?php echo $target_temp ;?>" /> 

	   <legend>warning humibox</legend>       
       <label for="temp_min">Alerte basse:</label>
       <input type="number" name="temp_min" id="temp_min" value="<?php echo $temp_min ;?>" /> <br/>  
       <label for="temp_max">Alerte haute:</label>
       <input type="number" name="temp_max" id="temp_max" value="<?php echo $temp_max ;?>" /><br/>  

   
	    <legend>mail</legend>      
		<label for="envoyeur">envoyeur: <em>(gmail obligatoire)</em></label><br/>
		<input type="mail" name="envoyeur" id="envoyeur" value="<?php echo $envoyeur ?>" /> <br/>
		<label for="mddpenvoyeur">mot de passe:</label><br/>
		<input type="password" name="mdpenvoyeur" id="mdpenvoyeur" value="<?php echo $mdpenvoyeur ;?>" /> <br/>
		<label for="receveur">receveur: <em>(gmail Non obligatoire)</em></label><br/>
		<input type="mail" name="receveur" id="receveur" value="<?php echo $receveur ;?>" /> 

	    <legend>warning Raspberry</legend>
        <label for="warmpi">warning pi</label>
        <input type="number" name="warmpi" id="warmpi" value="<?php echo $warmpi ;?>" /> <br>   
        <label for="ipdupi">ip du pi</label>   
        <input type="text" name="ipdupi" id="ipdupi" value="<?php echo $ipdupi ;?>" />      
       
	    <legend>admin</legend>
        <label for="loginadmin">login:</label>
        <input type="text" name="loginadmin" id="loginadmin" value="<?php echo $loginadmin ;?>" /> <br/>
        <label for="mdpadmin">mot de passe:</label>
        <input type="password" name="mdpadmin" id="mdpadmin" value="<?php echo $mdpadmin ;?>" /> <br/>         
	    <br/>

	<script type="text/javascript" language="javascript">
	function Confirmation() {
	if (confirm("Etes-vous sûr de vouloir valider?")) {
		this.form.submit();
		}
	}
	</script>
	<input type="submit" name="valider" value="valider" class="bouton" OnClick="return confirm('Etes-vous sûr de vouloir valider?')" />
</form>
