(function(){
    
    'use strict';
    
    var gscript = document.createElement('script');
		gscript.src = 'https://www.gstatic.com/charts/loader.js';
		document.head.appendChild( gscript );
		gscript.onload = function () {
  
			google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawPositivesChart);

      function drawPositivesChart() {

				var d = new Array();
				for( var x in uriCOVIDStatus) {
					d.push([uriCOVIDStatus[x][0], uriCOVIDStatus[x][2], uriCOVIDStatus[x][3]]);
				}
				var data = google.visualization.arrayToDataTable(d);
				
				var options = {
					title: 'Positive tests and isolations over time',
					//curveType: 'function',
					colors: ['#002147', '#2277b3'],
					legend: {
						position: 'bottom'
					},
					vAxis: {
						viewWindow: {
							min: 0
						}
					}
				};

				var chart = new google.visualization.AreaChart(document.getElementById('covid-line-chart'));

				chart.draw(data, options);
      }

    
		};
    

})();