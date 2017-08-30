/*
Copyright © 2014 TestArena 

This file is part of TestArena.

TestArena is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

The full text of the GPL is in the LICENSE file.
*/
// Load the Visualization API and the piechart package.
google.load('visualization', '1', {'packages':['corechart']});
// Set a callback to run when the Google Visualization API is loaded.
google.setOnLoadCallback(drawChart);

function drawChart() {
  if (typeof projectTaskChartDataJson != 'undefined') {
    // Create our data table out of JSON data loaded from server.
    var data = new google.visualization.DataTable(projectTaskChartDataJson);

    // Instantiate and draw our chart, passing in some options.
    var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
    
    var options = {
      height: 319,      
      showRowNumber: true,
      //title: 'Zadania przypisane do mnie',
      colors: ['#e0440e', '#e6693e', '#ec8f6e', '#f3b49f', '#f6c7b6'],
      is3D: true,
      sliceVisibilityThreshold: 0
    };
    
    chart.draw(data, options);
    
    function resizeHandler () {
      chart.draw(data, options);
    }
    if (window.addEventListener) {
      window.addEventListener('resize', resizeHandler, false);
    }
    else if (window.attachEvent) {
      window.attachEvent('onresize', resizeHandler);
    }

    //chart.draw(data, options);

    /*chart2.draw(data, {
      width: 500,
      height: 240,
      showRowNumber: true,
      title: 'Wszystkie zadania przypisane do mnie1',
      colors: ['#e0440e', '#e6693e', '#ec8f6e', '#f3b49f', '#f6c7b6'],
      is3D: true
    });*/
    //chart.draw(data, {width: 800, height: 400});
  } else {
    return false;
  }
}
