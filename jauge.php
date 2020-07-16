<script>	
RGraph.CSV('result.csv', function (csv)  {      //on récupère le fichier result
	
	var TempF = csv.getRow(1, 1);    // on enregistre les ligne du csv dans des variables
	var HumiF = csv.getRow(1, 0);
	var TempC = csv.getRow(2,1);  
	var HumiC = csv.getRow(2, 0);   
	
	RGraph.reset(document.getElementById('cvs1'));
	RGraph.reset(document.getElementById('cvs2'));
	RGraph.reset(document.getElementById('cvs3'));
	RGraph.reset(document.getElementById('cvs4'));
	 
	gauge1 = new RGraph.Gauge({
		id: 'cvs1',
		min: -20,
		max: 60,
		value: TempF[0],
		options: {
			textColor: 'white',
			labelsValue: true, 
			labelsValueDecimals: 1,
			labelsValueBounding: false,
			labelsValueYPos: 0.75,
			labelsValueBold: true,
			labelsValueUnitsPost: ' °C',
			titleTop: 'BME680',
			titleBottom: 'Température', 
			backgroundColor: 'black',
			borderOuter:     'grey',
			borderInner:     'black',
			borderOutline:   'black',
			shadow:           true,
			shadowColor:     'grey',
			centerpinColor:  'black',			               
			colorsRanges: [[-20, 0, '#0000FF'], [0, 15,'#00FFFF'], [15, 30, '#00FF47'], [30, 40, '#FFFF00'], [40,60,'#FF4A00']] ,                
			adjustable: false,
			textSize: 11
		}
	}).draw()
	
	gauge2 = new RGraph.Gauge({
		id: 'cvs2',
		min: 0,
		max: 100,
		value: HumiF[0],
		options: {
			textColor: 'white',
			backgroundColor: 'black',
			borderOuter:     'grey',
			borderInner:     'black',
			borderOutline:   'black',
			shadow:           true,
			shadowColor:     'grey',
			centerpinColor:  'black',
			labelsValue: true, 
			labelsValueDecimals: 1,
			labelsValueBounding: false,
			labelsValueYPos: 0.75,
			labelsValueBold: true,
			labelsValueUnitsPost: ' %',
			titleTop: 'BME680',
			titleBottom: 'Humidité',                
			colorsRanges: [[0, 40, '#DC3912'], [40, 50,'#FF9900'], [50, 75, '#00FF00'], [75, 85, '#FF9900'], [85,100,'#DC3912']] ,
			adjustable: false,
			textSize: 11
		}
	}).draw()
	
	gauge3 = new RGraph.Gauge({
		id: 'cvs3',
		min: -20,
		max: 60,
		value: TempC[0],
		options: {
			textColor: 'white',
			labelsValue: true, 
			labelsValueDecimals: 1,
			labelsValueBounding: false,
			labelsValueYPos: 0.75,
			labelsValueBold: true,
			labelsValueUnitsPost: ' °C',
			titleTop: 'Point froid',
			titleBottom: 'Température', 
			backgroundColor: 'black',
			borderOuter:     'grey',
			borderInner:     'black',
			borderOutline:   'black',
			shadow:           true,
			shadowColor:     'grey',
			centerpinColor:  'black',			               
			colorsRanges: [[-20, 0, '#0000FF'], [0, 15,'#00FFFF'], [15, 30, '#00FF47'], [30, 40, '#FFFF00'], [40,60,'#FF4A00']] ,                
			adjustable: false,
			textSize: 11
		}
	}).draw()
	
	gauge4 = new RGraph.Gauge({
		id: 'cvs4',
		min: 0,
		max: 100,
		value: HumiC[0],
		options: {
			textColor: 'white',
			backgroundColor: 'black',
			borderOuter:     'grey',
			borderInner:     'black',
			borderOutline:   'black',
			shadow:           true,
			shadowColor:     'grey',
			centerpinColor:  'black',
			labelsValue: true, 
			labelsValueDecimals: 1,
			labelsValueBounding: false,
			labelsValueYPos: 0.75,
			labelsValueBold: true,
			labelsValueUnitsPost: ' %',
			titleTop: 'BME680',
			titleBottom: 'Humidité',                
			colorsRanges: [[0, 40, '#DC3912'], [40, 50,'#FF9900'], [50, 75, '#00FF00'], [75, 85, '#FF9900'], [85,100,'#DC3912']] ,
			adjustable: false,
			textSize: 11
		}
	}).draw()	
});
</script>

<div id="canvas">
	<div class="conteneurH">
		<h3 class="element">Sonde de controle pour l'humidité</h3>
		<h3 class="element">Sonde de controle pour le chauffage</h3>
	</div>
	<div class="conteneur">
		<canvas class="element" id="cvs1" width="250" height="250" >      
			[No canvas support]
		</canvas>
		<canvas class="element" id="cvs2" width="250" height="250" >
			[No canvas support]
		</canvas>		
		<canvas class="element" id="cvs3" width="250" height="250" >
			[No canvas support]
		</canvas>
		<canvas class="element" id="cvs4" width="250" height="250" >
			[No canvas support]
		</canvas>
	</div>
</div>
