    <!-- https://google-developers.appspot.com/chart/interactive/docs/gallery/linechart -->
    <!-- ggf. Alternative http://www.phplot.com/ -->
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load('visualization', '1', {packages: ['corechart']});
    </script>
    <script type="text/javascript">
      function drawVisualization() {
        // Some raw data (not necessarily accurate)
        /*var data = google.visualization.arrayToDataTable([
          ['Month', 'Bolivia', 'Ecuador', 'Madagascar', 'Papua New Guinea', 'Rwanda', 'Average'],
          ['2004/05',  165,      938,         522,             998,           450,      614.6],
          ['2005/06',  135,      1120,        599,             1268,          288,      682],
          ['2006/07',  157,      1167,        587,             807,           397,      623],
          ['2007/08',  139,      1110,        615,             968,           215,      609.4],
          ['2008/09',  136,      691,         629,             1026,          366,      569.6]
        ]);
        */
        // Das Array lasse ich in PHP aufbauen
        <?php
        include("moistdata.php");
        ?>

        var options =
        {
            title: '',
            titlePosition: 'in',        /* Where to place the chart title, compared to the chart area. Supported values:
                                        in - Draw the title inside the chart area.
                                        out - Draw the title outside the chart area.
                                        none - Omit the title*/
            lineWidth: 4,                   // Data line width in pixels. Use zero to hide all lines and show only the points. You can override values for individual series using the series property.
            pointSize: 5,                   // Diameter of displayed points in pixels. Use zero to hide all points. You can override values for individual series using the series property.
            width: 330,                     // Width of the chart, in pixels.
            height: 320,                    // Height of the chart, in pixels.
            backgroundColor: '#F4FAFF',     //,   // The background color for the main area of the chart. Can be either a simple HTML color string, for example: 'red' or '#00cc00', or an object with the following properties.
            colors: ['#2188FF', '#DE7700', '#68AFFF'],            // The colors to use for the chart elements. An array of strings, where each element is an HTML color string, for example: colors:['red','#004411'].
            curveType: 'function',          // Controls the curve of the lines when the line width is not zero. Can be one of the following:
                                            // 'none' - Straight lines without curve.
                                            // 'function' - The angles of the line will be smoothed.
            bar:
            {
                groupWidth: '80%'
            },
            legend:
            {
                position: 'none'
            },
            chartArea:
            {
                width: '65%',
                height: '80%',
                top: 10,
            },
            titleTextStyle:
            {
                    color: '#2188FF',
                    fontSize: 1
            },                          /* An object that specifies the title text style. The object has this format:
                                        { color: <string>,
                                          fontName: <string>,
                                          fontSize: <number>,
                                          bold: <boolean>,
                                          italic: <boolean> }
                                          */

            series:
            [
                {targetAxisIndex:0, type: "line", dataOpacity: 1.0},
                {targetAxisIndex:1, type: "line", dataOpacity: 0.7, lineWidth: 0},
                {targetAxisIndex:1, type: "bars", dataOpacity: 0.5}
            ],
            vAxes:  // Thx to http://lamages.blogspot.de/2013/04/how-to-set-axis-options-in-googlevis.html
            [
                {
                    logScale: false,
                    title: '',
                    format: '# %',
                    ticks: [0.30, 0.45, 0.60, 0.75, 0.90],
                    textStyle:
                    {
                        color: '#0B0500',
                        fontSize: 9
                    },
                    viewWindowMode: 'explicit',
                    viewWindow:
                    {
                        max: 0.90,
                        min: 0.3
                    }
                },
                {
                    logScale: false,
                    title: '',
                    format: '# ml/°C',
                    ticks: [50, 200, 350, 500, 650],
                    textStyle:
                    {
                        color: '#0B0500',
                        fontSize: 9
                    },
                    viewWindowMode: 'explicit',
                    viewWindow:
                    {
                        max: 650,
                        min: 50
                    }
                }

            ],
            hAxis:
            {                      // Wochentage
                textStyle:
                {
                    color: '#0B0500',
                    fontSize: 9
                },
                showTextEvery: 2
            }
        };

        var chart = new google.visualization.LineChart(document.getElementById('moistchart_div'));
        chart.draw(data, options);
      }
      google.setOnLoadCallback(drawVisualization);
    </script>