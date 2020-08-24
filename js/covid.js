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
				
				var options = getDefaultOptions();
				options.title = 'Positive tests and isolations / quarantine by date';
				
				var el = document.getElementById('covid-daily-chart');
				el.style.height = '360px';
				var chart = new google.visualization.AreaChart(el);

				chart.draw(data, options);
      }


      function drawCumulativeChart() {

				var d = new Array();
				for( var x in uriCOVIDStatus) {
					var da = uriCOVIDStatus[x][0].split('/');
					d.push([new Date(uriCOVIDStatus[x][0]), uriCOVIDStatus[x][2], uriCOVIDStatus[x][1]]);
				}
				var data = google.visualization.arrayToDataTable(d);
				
				var options = getDefaultOptions();
				options.title = 'Total tests by date';
				options.colors = ['#2277b3', '#002147'];
				
				var el = document.getElementById('covid-cumulative-chart');
				el.style.height = '360px';
				var chart = new google.visualization.AreaChart(el);

				chart.draw(data, options);
      }
    
		};
    
		function getDefaultOptions() {
			var today = new Date();
			var startDate = new Date();
			startDate.setMonth( startDate.getMonth() - 5 );
			return {
				backgroundColor: '#fafafa',
				curveType: 'function',
				animation: {
					duration: '250',
					easing: 'inAndOut',
					startup: true,
				},
				titleTextStyle : {
					fontName: 'Lato',
					fontSize: 16,
					color: '#000'
				},
				explorer: {
					actions: ['dragToPan', 'rightClickToReset'],
					axis: 'horizontal',
					keepInBounds: true,
				},
				chartArea: {
					width: '90%'
				},
				colors: ['#002147', '#2277b3'],
				legend: {
					position: 'bottom',
					textStyle : {
						fontName: 'Hind',
						color: '#000'
					}
				},
				hAxis: {
					format: 'M/d',
					textStyle : {
						fontName: 'Hind',
						color: '#000'
					},
					viewWindow: {
						min: startDate,
						max: today
					},
					//gridlines: {count: 15}
				},
				vAxis: {
					minValue: 0,
					textStyle : {
						fontName: 'Hind',
						color: '#000'
					},
 					format: '#'
				},
				isStacked: false,
				//bar: { groupWidth: '30' },
			};
		}


})();