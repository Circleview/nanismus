<!--html>
  <head-->
  
    <!-- https://google-developers.appspot.com/chart/interactive/docs/gallery/linechart -->  
    <!-- ggf. Alternative http://www.phplot.com/ -->
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
      /*
        var data = google.visualization.arrayToDataTable([
          ['Tag', 'Temperatur'],
          ['Montag',  19        ],
          ['Dienstag',  22        ],
          ['Mittwoch',  21         ],
          ['heute',  23        ]
        ]);
        */ 
        // Das Array lasse ich von PHP aufbauen
        <?php 
        include("tempdata.php");
        ?>

        // Dokumentation
        // https://google-developers.appspot.com/chart/interactive/docs/gallery/linechart
        var options = {
          title: '', 
          titlePosition: 'in', /* Where to place the chart title, compared to the chart area. Supported values:
                                in - Draw the title inside the chart area.
                                out - Draw the title outside the chart area.
                                none - Omit the title*/
          titleTextStyle: {
            color: '#DE7700', 
            fontSize: 1
          },                /* An object that specifies the title text style. The object has this format:
                            { color: <string>,
                              fontName: <string>,
                              fontSize: <number>,
                              bold: <boolean>,
                              italic: <boolean> }

                            The color can be any HTML color string, for example: 'red' or '#00cc00'. Also see fontName and fontSize.*/

          lineWidth: 4,                 // Data line width in pixels. Use zero to hide all lines and show only the points. You can override values for individual series using the series property.
          pointSize: 5,                 // Diameter of displayed points in pixels. Use zero to hide all points. You can override values for individual series using the series property.
          width: 356,                   // Width of the chart, in pixels.
          height: 190,                  // Height of the chart, in pixels.
          backgroundColor: '#F4FAFF',   //,   // The background color for the main area of the chart. Can be either a simple HTML color string, for example: 'red' or '#00cc00', or an object with the following properties.
          colors: ['#DE7700'],          // The colors to use for the chart elements. An array of strings, where each element is an HTML color string, for example: colors:['red','#004411'].
          curveType: 'function',        // Controls the curve of the lines when the line width is not zero. Can be one of the following:
                                        // 'none' - Straight lines without curve.
                                        // 'function' - The angles of the line will be smoothed.
          vAxis: {                      // Temperatur
              title: '',
              format: '#.## °C',
              ticks: [16, 18, 20, 22, 24],
              textStyle: {
                color: '#0B0500', 
                fontSize: 9
              }, 
              gridlines: {
                  count: 5
              }
          },
          hAxis: {                      // Wochentage
              textStyle: {
                color: '#0B0500',
                fontSize: 9                
              },
              showTextEvery: 2              
          }, 
          legend: {
              position: 'none'
          },
          chartArea: {
              width: '70%', 
              height: '80%',
              top: 5, 
          } 
        };

        var chart = new google.visualization.LineChart(document.getElementById('tempchart_div'));
        chart.draw(data, options);
      }
    </script>
  <!--/head>
  <body>
    <div id="chart_div" style="width: 900px; height: 500px;"></div>
  </body>
</html-->