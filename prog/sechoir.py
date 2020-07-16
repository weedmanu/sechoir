#!/usr/bin/python
# -*- coding: utf-8 -*-

# import des librairies nécessaire

import time
import socket
import Adafruit_DHT        # pour lire la sonde DHT22
import RPi.GPIO as GPIO    # pour utiliser les GPIO
import MySQLdb             # pour utiliser les BDD
import I2C_LCD_driver      # import de notre driver
import json
import csv
import datetime            # pour utiliser la date et l'heure
import smtplib
from email.MIMEMultipart import MIMEMultipart
from email.MIMEBase import MIMEBase
from email.MIMEText import MIMEText
from email.Utils import COMMASPACE, formatdate
from email import Encoders

GPIO.setmode(GPIO.BCM)  # gpio numérotation BCM
GPIO.setwarnings(False)
 
DHTpinCtrlHumi = 22 # gpio utilisé
DHTpinCtrlTemp = 27 

GPIO.setup(DHTpinCtrlHumi, GPIO.OUT)  # gpio en mode output
GPIO.setup(DHTpinCtrlTemp, GPIO.OUT)  # gpio en mode output

ecran = I2C_LCD_driver.lcd()      # on déclare notre écran

delay = 10
ventilo_gpio = 4
chauffage_gpio = 23
pwm_gpio = 18

GPIO.setup(ventilo_gpio, GPIO.OUT)  # relais ventilo
GPIO.setup(chauffage_gpio, GPIO.OUT)  # relais chauffage
GPIO.setup(pwm_gpio, GPIO.OUT)      # servo trappe
GPIO.output (ventilo_gpio, True)   # on eteint le ventilo	
GPIO.output (chauffage_gpio, True)   # on eteint le chauffage	
frequence = 50 
pwm = GPIO.PWM(pwm_gpio, frequence) # la pwm
ferme = 0 
position = 0
ouvert = 90    
target_hum = 50
target_temp = 20
flag_on_off = False

#fonction pour calculer le pourcentage depuis un angle
def angle_vers_pourcent (angle) :
    if angle > 180 or angle < 0 :
        return False
    start = 4
    end = 12.5
    ratio = (end - start)/180 #Calcule du ratio angle vers pourcent
    angle_en_pourcent = angle * ratio
    return start + angle_en_pourcent

#Init servo a 0°
pwm.start(angle_vers_pourcent(ferme))

# fonction sendmail
def sendMail(to, subject, text, files=[]):         
	assert type(to)==list
	assert type(files)==list

	msg = MIMEMultipart()
	msg['From'] = USERNAME
	msg['To'] = COMMASPACE.join(to)
	msg['Date'] = formatdate(localtime=True)
	msg['Subject'] = subject

	msg.attach( MIMEText(text) )

	for file in files:
		part = MIMEBase('application', "octet-stream")
		part.set_payload( open(file,"rb").read() )
		Encoders.encode_base64(part)
		part.add_header('Content-Disposition', 'attachment; filename="%s"' % os.path.basename(file))
		msg.attach(part)

	server = smtplib.SMTP('smtp.gmail.com:587')
	server.ehlo_or_helo_if_needed()
	server.starttls()
	server.ehlo_or_helo_if_needed()
	server.login(USERNAME,PASSWORD)
	server.sendmail(USERNAME, to, msg.as_string())
	server.quit()

# on ouvre le fichier config .json
with open('/var/www/html/humibox/admin/config.json') as config:    
    config = json.load(config)

 # on recupère le login et mdp de la bdd   
login = config["mysql"]["loginmysql"]
mdp = config["mysql"]["mdpmysql"]
bdd = config["mysql"]["bdd"]

# on se connecte a la bdd
db = MySQLdb.connect(host="localhost", 
                     user = login,     
                     passwd = mdp, 
                     db = bdd)  
cur = db.cursor()
cur.execute("SELECT * FROM config")   # on sort tout de la table config

for row in cur.fetchall():        # et on récupère les champs qui nous intéresse
    target_hum = row[3]
    target_temp = row[4]
    temp_min = row[5]
    temp_max = row[6]
    warmpi = row[7]    
    envoyeur = row[8]
    mdpenvoyeur = row[9]
    receveur = row[10]   
    pi = row[11] 

db.close()                  # on ferme la connexion a la bdd

USERNAME = envoyeur      # adresse de l'envoyeur
PASSWORD = mdpenvoyeur   # mot de passe

s=socket.socket(socket.AF_INET,socket.SOCK_STREAM) # on définit le socket

host = pi    				# ip du pi ou 192.168.xxx.xxx c'est la même sur les clients php
port = int(9999)       		# port utilisé, c'est le même sur les clients php
s.bind((host,port))   		#  on construit le socket
s.listen(1)                	# 1 seule instruction acceptée à la fois

               
# en écoute boucle infini
while True: 
	date = datetime.datetime.now() # on défini la date
	humiF, tempF = Adafruit_DHT.read_retry(Adafruit_DHT.DHT22, DHTpinCtrlHumi) # lecture de la sonde
	humiC, tempC = Adafruit_DHT.read_retry(Adafruit_DHT.DHT22, DHTpinCtrlTemp) # lecture de la sonde

	# on arrondi 
	humiF = round(humiF,1)                   
	tempF = round(tempF,1)
	humiC = round(humiC,1)                   
	tempC = round(tempC,1)
	
	# on créer le fichier
	fname = "/var/www/html/humibox/result.csv"    
	file = open(fname, "wb")
	 
	try:
		# Création du CSV   
		writer = csv.writer(file)
		# Écriture de la ligne d'en-tête avec le titre
		# des colonnes.
		writer.writerow( ('Humidité', 'Température') )
		# Écriture des quelques données.
		writer.writerow( (humiF, tempF) )
		writer.writerow( (humiC, tempC) )       

	finally:
		# Fermeture du fichier source    
		file.close()    


	print ("Point froid : ")
	print ("humidité {0:0.1f} % et température {1:0.1f} °C".format(humiF, tempF))
	print ("Point chaud :")
	print ("humidité {0:0.1f} % et température {1:0.1f} °C".format(humiC, tempC))
	
	conn,addr = s.accept()        # si un client se connecte
	data = conn.recv(2048)        # met dans la variable data ce qu'il a envoyer
	data = data.decode("utf-8")   # encodage 
	s.close   					  # on ferme le socket
	
	if data == 'start':			
		flag_on_off = True	
		
	while flag_on_off:		
		ecran.backlight(1)                                                       	            # on allume l'écran
		ecran.lcd_clear()                                    						            # on efface l'écran
		ecran.lcd_display_string('TempCtrl: {0:0.1f}*C'.format(tempC),1)  		# 1 ère ligne
		ecran.lcd_display_string('HumiCtrl: {0:0.1f}%'.format(humiF),2)      	# 2 ème ligne
		# Connexion à la base de donnée DHT22
		bdd = MySQLdb.connect(host="localhost",           # en local
							  user=login,                # l'utilisateur
							  passwd=mdp,      # son mot de passe
							  db="humibox")                 # la base de donnée
		req = bdd.cursor()

		# insert la date, la température et l'humidité dans la table temphumi
		try:
			req.execute("""insert into capteurdata (`dateandtime`,`tempF`,`humF`,`tempC`,`humC`) values (%s,%s,%s,%s,%s)""",(date,tempF,humiF,tempC,humiC))
			bdd.commit()
			
		except:
			bdd.rollback()
			
		# Fermeture de la connexion    
		bdd.close()
		
		if humiF > target_hum + 2:                     # si l'humidite au point froid est > a la target + 2%  	
			while position < ouvert:                               	# tant que la porte n'est pas ouverte
				pwm.ChangeDutyCycle(angle_vers_pourcent(position)) 		# on ouvre la porte doucement
				position = position + 1
				time.sleep(0.1)
			GPIO.output (ventilo_gpio, False)   					# on allume le ventilo								
		elif humiF < target_hum - 2:                   # si l'humidite au point froid est > a la target - 2%	
			GPIO.output (ventilo_gpio, True)   						# on eteint le ventilo	
			while position > ferme:                                 # tant que la porte n'est pas fermée
				pwm.ChangeDutyCycle(angle_vers_pourcent(position))  	# on ferme la porte doucement
				position = position - 1
				time.sleep(0.1)
		else:                                                   # sinon ( si l'humidité est comprise entre target +- 2%
			GPIO.output (ventilo_gpio, True)   						# on eteint le ventilo		
			while position > ferme:									# tant que la porte n'est pas fermée
				pwm.ChangeDutyCycle(angle_vers_pourcent(position))  	# on ferme la porte doucement
				position = position - 2
				time.sleep(0.1)

		# On envoi un mail , si la température au point chaud dépasse les limites.
		if tempC <= temp_min or tempC >= temp_max :
			if flag_mail:
				flag_mail = False	
				sendMail( [receveur],           # adresse ou l'on veut envoyer le mail
						"Alerte humibox !!!!",              # sujet  
						"limite atteinte, il fait %s °C au point chaud ,connecte toi vite !!!" % tempC,         # le message
						["/var/www/html/humibox/img/alerte.jpeg"])             # chemin pièce jointe  
											
				
		conn,addr = s.accept()        # si un client se connecte
		data = conn.recv(2048)        # met dans la variable data ce qu'il a envoyer
		data = data.decode("utf-8")   # encodage 
		s.close   					  # on ferme le socket
		
		if data == 'stop':		
			flag_on_off = False
			if position != ferme:                              	# si la porte est ouverte, on affiche la fermeture 
				ecran.lcd_clear()                               		# on efface l'écran
				ecran.lcd_display_string('Fermeture',1)  				# 1 ère ligne
				ecran.lcd_display_string('porte',2)      				# 2 ème ligne   
				GPIO.output (ventilo_gpio, True)   						# on eteint le ventilo		
			while position > ferme:									# tant que la porte n'est pas fermée
				pwm.ChangeDutyCycle(angle_vers_pourcent(position))  	# on ferme la porte doucement
				position = position - 2
				time.sleep(0.1)
			
			ecran.lcd_clear()
			ecran.backlight(0) 
			break
			
		time.sleep(delay)
