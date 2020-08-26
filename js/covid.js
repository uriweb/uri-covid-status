(function(){
    
    'use strict';
    
    var gscript = document.createElement('script');
		gscript.src = 'https://www.gstatic.com/charts/loader.js';
		document.head.appendChild( gscript );
		gscript.onload = function () {
  
			google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawPositivesChart);
      google.charts.setOnLoadCallback(drawIsoQuarChart);
      google.charts.setOnLoadCallback(drawCumulativeChart);
      
      function drawPositivesChart() {
      
      	var ymax = 0;
				var d = new Array();
				for( var x in uriCOVIDStatus) {
					var da = uriCOVIDStatus[x][0].split('/');
					d.push([new Date(uriCOVIDStatus[x][0]), uriCOVIDStatus[x][2]]);
					if( uriCOVIDStatus[x][2] > ymax ) {
						ymax = uriCOVIDStatus[x][2];
					}
				}
				var data = google.visualization.arrayToDataTable(d);
				
				var options = getDefaultOptions();
				options.title = 'Positive tests by date';
				
				options.vAxis.viewWindow = { min: 0, max: ymax + 20};
				
// 				var today = new Date();
// 				var start = new Date();
// 				start = start.setMonth(start.getMonth() - 2);
// 				
// 				options.hAxis.viewWindow = {
//             max:today,
//             min:start
//         };
				
				var el = document.getElementById('covid-daily-tests');
				el.style.height = '360px';
				var chart = new google.visualization.ColumnChart(el);

				chart.draw(data, options);
      }

      function drawIsoQuarChart() {

      	var ymax = 0;
				var d = new Array();
				for( var x in uriCOVIDStatus) {
					var da = uriCOVIDStatus[x][0].split('/');
					d.push([new Date(uriCOVIDStatus[x][0]), uriCOVIDStatus[x][3]]);
					if( uriCOVIDStatus[x][3] > ymax ) {
						ymax = uriCOVIDStatus[x][3];
					}
				}
				var data = google.visualization.arrayToDataTable(d);
				
				var options = getDefaultOptions();
				options.title = 'Isolation / quarantine by date';
				options.vAxis.viewWindow = { min: 0, max: ymax + 20};
				
				var el = document.getElementById('covid-iso-quar');
				el.style.height = '360px';
				var chart = new google.visualization.ColumnChart(el);

				chart.draw(data, options);
      }


      function drawCumulativeChart() {

      	var ymax = 0;
				var d = new Array();
				for( var x in uriCOVIDStatus) {
					var da = uriCOVIDStatus[x][0].split('/');
					d.push([new Date(uriCOVIDStatus[x][0]), uriCOVIDStatus[x][2], uriCOVIDStatus[x][1]]);
					if( uriCOVIDStatus[x][1] > ymax ) {
						ymax = uriCOVIDStatus[x][1];
					}
				}
				var data = google.visualization.arrayToDataTable(d);
				
				var options = getDefaultOptions();
				options.title = 'Total tests by date';
				options.colors = ['#2277b3', '#002147'];
				options.vAxis.viewWindow = { min: 0, max: ymax + 20};
				
				var el = document.getElementById('covid-cumulative-chart');
				el.style.height = '360px';
				var chart = new google.visualization.AreaChart(el);

				chart.draw(data, options);
      }
    
		};
    
		function getDefaultOptions() {
			var today = new Date();
			var startDate = new Date();
			startDate.setMonth( startDate.getMonth() - 2 );
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
					keepInBounds: false,
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
					format: 'M/d/YY',
					textStyle : {
						fontName: 'Hind',
						color: '#000'
					},
					viewWindow: {
						min: startDate,
						max: today
					},
					slantedText: false,
					//gridlines: {count: 15}
					title: 'Drag chart to change timeline; right click to reset.',
					titleTextStyle: {
						fontName: 'Hind',
						color: '#000'
					}
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
				bar: { groupWidth: '100%' },
			};
		}


})();