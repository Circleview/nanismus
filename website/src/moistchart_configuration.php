<?php
    
    /*
     var data = google.visualization.arrayToDataTable([
     ['Year', 'Sales', 'Expenses'],
     ['2013',  1000,      400],
     ['2014',  1170,      460],
     ['2015',  660,       1120],
     ['2016',  1030,      540]
     ]);
     */
    
    
    // https://developers.google.com/chart/interactive/docs/gallery/areachart#configuration-options
    // https://developers.google.com/chart/interactive/docs/gallery/linechart
    
    echo "var options = {";
    echo "curveType: 'function',";
    echo "title: 'Feuchte im Zeitverlauf',";
    
    echo "hAxis: {";
    echo "textStyle: {";
    echo "color: '"; echo textColor($Feuchte); echo "',";
    echo "fontSize: 8";
    echo "},";
    echo "gridlines: {";
    echo "count: 5";
    echo "}";
    echo "},";
    
    echo "vAxis: {";
    echo "minValue: 0.35,";
    echo "textStyle: {";
    echo "color: '"; echo textColor($Feuchte);
    echo "'},";
    echo "format: 'percent',";
    echo "gridlines: {";
    echo "color: '"; echo backgroundColor($Feuchte); echo "',";
    echo "count: 2";
    echo "},";
    echo "textPosition: 'in'";
    echo "},";
    
    echo "titleTextStyle: {";
    echo "color: '"; echo textColor($Feuchte);  echo "',";
    echo "fontSize: 15";
    echo "},";
    
    echo "backgroundColor: '"; echo backgroundColor($Feuchte); echo "',";
    
    echo "chartArea:{left:0,top:20,width:'100%',height:'85%'},";
    
    // color script based on moisture
    include ("dataline_color.php");
    
    echo "series: {";
    echo "0: {";
    echo "visibleInLegend: false,";
    echo "lineWidth: 6,";
    echo "color: '"; echo datalineColor($Feuchte); echo "',";
    echo "areaOpacity: 0.3";
    echo "}";
    echo "}";
    
    echo "};"
    
    ?>
