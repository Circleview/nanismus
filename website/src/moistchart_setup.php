


<!-- https://developers.google.com/chart/interactive/docs/gallery/areachart -->

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">

    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawChart);



function drawChart() {
    
    <?php // assemble the data in
    
    include ("moistdata.php"); ?>
    
    
    var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
    chart.draw(data, options);

}

/* We display the anticipated watering date on the disabled watering button on the index page */
/* Use Javascript to grab the value in the HTML button on the index page */
var anticipatedWateringDayJS = <?php echo "'$anticipatedWateringDay'"; ?>;

</script>
