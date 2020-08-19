(function(){
    
    'use strict';
    
    var gscript = document.createElement('script');
		gscript.src = 'https://www.gstatic.com/charts/loader.js';
		document.head.appendChild( gscript );
		gscript.onload = function () {
  
			google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawPositivesChart);
      google.charts.setOnLoadCallback(drawCumulativeChart);

      function drawPositivesChart() {

				var d = new Array();
				for( var x in uriCOVIDStatus) {
					var da = uriCOVIDStatus[x][0].split('/');
					d.push([new Date(uriCOVIDStatus[x][0]), uriCOVIDStatus[x][2], uriCOVIDStatus[x][3]]);
				}
				var data = google.visualization.arrayToDataTable(d);
				
				var options = {
					title: 'Positive tests and isolations / quarantine by date',
					//curveType: 'function',
					colors: ['#002147', '#2277b3'],
					legend: {
						position: 'bottom'
					},
					hAxis: {
            format: 'M/d'
            //gridlines: {count: 15}
          },
					vAxis: {
						viewWindow: {
							min: 0
						}
					}
				};
				
				var el = document.getElementById('covid-daily-chart');
				el.style.height = '360px';
				var chart = new google.visualization.AreaChart(el);

				chart.draw(data, options);
      }


      function drawCumulativeChart() {

				var d = new Array();
				for( var x in uriCOVIDStatus) {
					var da = uriCOVIDStatus[x][0].split('/');
					d.push([new Date(uriCOVIDStatus[x][0]), uriCOVIDStatus[x][1], uriCOVIDStatus[x][2]]);
				}
				var data = google.visualization.arrayToDataTable(d);
				
				var options = {
					title: 'Total tests by date',
					//curveType: 'function',
					colors: ['#002147', '#2277b3'],
					legend: {
						position: 'bottom'
					},
					hAxis: {
            format: 'M/d'
            //gridlines: {count: 15}
          },
					vAxis: {
						viewWindow: {
							min: 0
						}
					}
				};
				
				var el = document.getElementById('covid-cumulative-chart');
				el.style.height = '360px';
				var chart = new google.visualization.AreaChart(el);

				chart.draw(data, options);
      }
    
		};
    

})();