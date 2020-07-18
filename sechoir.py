#!/usr/bin/python
# -*- coding: utf-8 -*-

""""""""""""""""""""""""""""""""""""""""""
"""  import des librairies nécessaire  """
""""""""""""""""""""""""""""""""""""""""""
import time                						# pour utiliser les delai (time.sleep())
import socket              						# pour le serveur tcp
import Adafruit_DHT        						# pour lire la sonde DHT22
import RPi.GPIO as GPIO    						# pour utiliser les GPIO
import MySQLdb             						# pour utiliser les BDD
import I2C_LCD_driver      						# import de notre driver LCD
import json                						# pour utiliser les fichier json
import csv                 						# pour utiliser les fichier csv
import datetime            						# pour utiliser la date et l'heure
import threading           						# pour utiliser les process parallele
import smtplib             						# pour utlisiser les mails
from email.MIMEMultipart import MIMEMultipart	# pour utlisiser les mails
from email.MIMEBase import MIMEBase				# pour utlisiser les mails
from email.MIMEText import MIMEText				# pour utlisiser les mails
from email.Utils import COMMASPACE, formatdate	# pour utlisiser les mails
from email import Encoders						# pour utlisiser les mails

""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""
"""	déclaration des variables, constantes et instances d'objet """ 
""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""
GPIO.setmode(GPIO.BCM)  						# gpio numérotation BCM
GPIO.setwarnings(False) 						# ne pas afficher les warning des gpios
 
DHTpinCtrlHumi = 22 							# gpio sonde1 utilisé pour controler l'humidité 
DHTpinCtrlTemp = 27 							# gpio sonde2 pour controler la température

GPIO.setup(DHTpinCtrlHumi, GPIO.OUT)  			# gpio sonde1 en mode output
GPIO.setup(DHTpinCtrlTemp, GPIO.OUT)  			# gpio sonde2 en mode output

ecran = I2C_LCD_driver.lcd()      	  			# on déclare notre écran

ventilo_gpio = 4								# gpio du relais utilisé pour controler le ventillo
chauffage_gpio = 23								# gpio du relais utilisé pour controler le chauffage
pwm_gpio = 18                                   # gpio du servo utilisé pour controler la trappe

GPIO.setup(ventilo_gpio, GPIO.OUT)  			# relais ventilo
GPIO.setup(chauffage_gpio, GPIO.OUT)  			# relais chauffage
GPIO.setup(pwm_gpio, GPIO.OUT)      			# servo trappe
GPIO.output (ventilo_gpio, True)   				# on eteint le ventilo	
GPIO.output (chauffage_gpio, True)   			# on eteint le chauffage	

frequence = 50                                  # frequence de la pwm en Hz             
pwm = GPIO.PWM(pwm_gpio, frequence) 			# on instance la pwm pour le servo

ferme = 0                           			# ferme vaut 0°
ouvert = 90    									# ouvert vaut 90°
target_hum = 50                                 # par defaut la target humidité vaut 50
target_temp = 20                                # par defaut la target temperature vaut 20
position = ferme                                # par defaut la position du servo est ferme
duree = 0                                       # par defaut la duree du sechage est 0
temps_de_sechage = 0							# par defaut le temps du sechage est 0
flag_on_off = False                             # par defaut le flag pour demmarer le sechage est faux
flag_mail = False								# par defaut le flag pour envoyer un mail est faux
delai = 10                                      # le delai entre chaque lecture de sonde est de 10secondes

""""""""""""""""""""""""""""""
"""    fonction sendmail   """
""""""""""""""""""""""""""""""                     
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

""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""
"""	  fonction pour calculer le pourcentage depuis un angle    """ 
""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""
def angle_vers_pourcent (angle) :
	if angle > 180 or angle < 0 :
		return False
	start = 4
	end = 12.5
	ratio = (end - start)/180 #Calcule du ratio angle vers pourcent
	angle_en_pourcent = angle * ratio
	return start + angle_en_pourcent

""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""
"""   Thread chargé de lire la sonde et de créer le fichier csv pour les jauge et d'envoyer les datas en BDD   """
""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""			  
class dht22(threading.Thread):	
	
	def __init__(self):
		threading.Thread.__init__(self)
		self._stopevent = threading.Event( )	
		
	def stop(self):
		self._stopevent.set( )
		
	def run(self):
		global humiF
		global humiC
		global tempF 
		global tempC 
		global temps_de_sechage
		date = datetime.datetime.now() 
		humiF, tempF =  Adafruit_DHT.read_retry(Adafruit_DHT.DHT22, DHTpinCtrlHumi) 
		humiC, tempC = Adafruit_DHT.read_retry(Adafruit_DHT.DHT22, DHTpinCtrlTemp) 
		print ("Point ctrl humi : ")
		print ("humidité {0:0.1f} % et température {1:0.1f} °C".format(humiF, tempF))
		print ("Point ctrl temp : ")
		print ("humidité {0:0.1f} % et température {1:0.1f} °C".format(humiC, tempC))
		
		# on arrondi 
		humiF = round(humiF,1)                   
		tempF = round(tempF,1)
		humiC = round(humiC,1)                   
		tempC = round(tempC,1)
		
		# on créer le fichier
		fname = "/var/www/html/sechoir/result.csv"    
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
			file.close()			
		
		con = MySQLdb.connect(host="localhost", user=login, passwd=mdp, db=bdd)                
		req = con.cursor()		
		try:
			req.execute("""insert into capteurdata (`dateandtime`,`tempF`,`humF`,`tempC`,`humC`) values (%s,%s,%s,%s,%s)""",(date,tempF,humiF,tempC,humiC))
			con.commit()			
		except:
			con.rollback()		
			
		con.close()			
		time.sleep(delai)

""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""		
"""   Thread chargé de d'écoué le socket tcp ( start ou stop ) """	
""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""		
class serveur(threading.Thread):	
	
	def __init__(self):
		threading.Thread.__init__(self)			
		self._stopevent = threading.Event( )		
			
	def stop(self):		
		s.shutdown(socket.SHUT_RDWR)
		s.close()
		print ("serveur fermé")
		self._stopevent.set()
		
	def run(self):		
		global duree
		global flag_on_off	
		global temps_de_sechage
		
		conn,addr = s.accept()         	# si un client se connecte
		data=conn.recv(2048)         	# met dans la variable data ce qu'il a envoyer
		data=data.decode("utf-8")   	# encodage 
		s.close                         # on ferme le socket	
							
		if data == "stop":						
			flag_on_off = False
		else:			
			duree = int(data)	
			temps_de_sechage = int(duree)	
			print("sechage demarré, temps de sechage: %s " %temps_de_sechage)
			flag_on_off = True

""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""		
"""   Thread chargé de lancer le sechage ou de l'arréter """	
""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""		
class commande(threading.Thread):	
	
	def __init__(self):
		threading.Thread.__init__(self)	
		self._stopevent = threading.Event( )
		
	def stop(self):
		self._stopevent.set( )
		
	def run(self):
		global flag_mail
		global duree
		global temps_de_sechage
		duree = int(duree)
		global flag_on_off
		global position		
		while flag_on_off == True:	
			ecran.backlight(1)                                                       	            # on allume l'écran
			ecran.lcd_clear()                                    						            # on efface l'écran
			ecran.lcd_display_string('TempCtrl: {0:0.1f}*C'.format(tempC),1)  		# 1 ère ligne
			ecran.lcd_display_string('HumiCtrl: {0:0.1f}%'.format(humiF),2)      	# 2 ème ligne		
			while duree > 0:				
				if tempC > target_temp + 0.5:                   # si la temperature au point ctrl temp est > a la target + 0.5 °C
					GPIO.output (chauffage_gpio, True)   						# on eteint le chauffage	
				elif tempC < target_temp - 0.5:                   # si la temperature au point ctrl temp est > a la target - 0.5 °C	
					GPIO.output (chauffage_gpio, False)   						# on allume le chauffage					
				
				if humiF > target_hum + 2:                     # si l'humidite au point froid est > a la target + 2%  	
					while position < ouvert:                               	# tant que la porte n'est pas ouverte
						pwm.ChangeDutyCycle(angle_vers_pourcent(position)) 		# on ouvre la porte doucement
						position = position + 1
						time.sleep(0.1)
					GPIO.output (ventilo_gpio, False)   					# on allume le ventilo	
					pwm.ChangeDutyCycle(0)		
					position = position					
				elif humiF < target_hum - 2:                   # si l'humidite au point froid est > a la target - 2%	
					GPIO.output (ventilo_gpio, True)   						# on eteint le ventilo	
					while position > ferme:                                 # tant que la porte n'est pas fermée
						pwm.ChangeDutyCycle(angle_vers_pourcent(position))  	# on ferme la porte doucement
						position = position - 1
						time.sleep(0.1)
					pwm.ChangeDutyCycle(0)
					position = position
				else:                                                   # sinon ( si l'humidité est comprise entre target +- 2%
					GPIO.output (ventilo_gpio, True)   						# on eteint le ventilo		
					while position > ferme:									# tant que la porte n'est pas fermée
						pwm.ChangeDutyCycle(angle_vers_pourcent(position))  	# on ferme la porte doucement
						position = position - 2
						time.sleep(0.1)
					pwm.ChangeDutyCycle(0)
					position = position
				
				# On envoi un mail , si la température au point chaud dépasse les limites.
				if tempC <= temp_min or tempC >= temp_max :
					if flag_mail:
						flag_mail = False	
						sendMail( [receveur],           # adresse ou l'on veut envoyer le mail
								"Alerte humibox !!!!",              # sujet  
								"limite atteinte, il fait %s °C au point chaud ,connecte toi vite !!!" % tempC)         # le message	
					
				duree = int(duree) - 1
				print(duree)
				if flag_on_off == False:
					temps_de_sechage = temps_de_sechage - duree
					print("sechage aborté, temps de sechage: %s " %temps_de_sechage)
					duree = 0
					break
				time.sleep(1)
				
			temps_de_sechage = temps_de_sechage - duree
			print("sechage terminé, temps de sechage: %s " %temps_de_sechage)
			duree = 0
			flag_on_off = False			
			GPIO.output (ventilo_gpio, True)   						# on eteint le ventilo		
			while position > ferme:									# tant que la porte n'est pas fermée
				pwm.ChangeDutyCycle(angle_vers_pourcent(position))  	# on ferme la porte doucement
				position = position - 2
				time.sleep(0.1)
			pwm.ChangeDutyCycle(0)
			position = position
			
		else:			
			ecran.backlight(0)		
			GPIO.output (ventilo_gpio, True)
			GPIO.output (chauffage_gpio, True)

		
#Init servo a 0°
pwm.start(angle_vers_pourcent(ferme))
pwm.ChangeDutyCycle(0)

# on ouvre le fichier config .json
with open('/var/www/html/sechoir/admin/config.json') as config:    
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

ecran.lcd_clear()
ecran.backlight(0)

def main():	
	while True:
		try:
			thread_1 = dht22()
			thread_2 = serveur()
			thread_3 = commande()
			thread_1.start()
			thread_2.start()
			thread_3.start()		
			thread_1.join()		
				
		except KeyboardInterrupt:			
				print('KeyboardInterrupt')
				break		

	thread_1.stop()
	print ("le thread1 s'est arrete proprement")
	thread_3.stop()
	print ("le thread3 s'est arrete proprement")
	thread_2.stop()
	print ("le thread2 s'est arrete proprement")

	ecran.lcd_clear()
	ecran.backlight(0)         
	GPIO.cleanup()
	s.close
	exit

 
if __name__ == "__main__":
	main()
