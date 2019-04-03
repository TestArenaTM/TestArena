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

function drawChart() {
  google.charts.load('current', {
    callback: function () {
      var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
      var options = {
        height: 319,
        showRowNumber: true,
        //title: 'Zadania przypisane do mnie',
        colors: ['#e0440e', '#e6693e', '#ec8f6e', '#f3b49f', '#f6c7b6'],
        is3D: true,
        pieSliceText: 'value',
        sliceVisibilityThreshold: 0,
        tooltip: {
          showColorCode: false,
          text: 'value',
          trigger: 'selection'
        }
      };

      if (drawChartOptions.colors != undefined) {
        options.colors = drawChartOptions.colors;
      }

      chart.draw(
        new google.visualization.DataTable(projectTaskChartDataJson),
        options
      );

      function mouseOverHandler(selection) {
        chart.setSelection([selection]);
      }

      function mouseOutHandler() {
        chart.setSelection();
      }

      google.visualization.events.addListener(chart, 'onmouseover', mouseOverHandler);
      google.visualization.events.addListener(chart, 'onmouseout', mouseOutHandler);
    },
    packages: ['corechart', 'table']
  });
}

$(document).ready(function () {
  drawChart();
});

if (window.addEventListener) {
  window.addEventListener('resize', drawChart, false);
}
else if (window.attachEvent) {
  window.attachEvent('onresize', drawChart);
}
