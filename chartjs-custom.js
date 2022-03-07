(function ($) {
	var ctx = document.getElementById("myChart").getContext("2d");
    var json_url = chart_ajax_ob.ajax_url+'?action=get_chart_data';
    
    const labels = [];
    const values = [];

    // draw empty chart
    const data = {
        labels: labels,
        datasets: [{
            data: values,
            fill: true,
            backgroundColor:'#FFFFFF',       
            borderColor: '#333333',
            borderWidth: 2,
            tension: 0.3,
            pointRadius: 2,
            hitRadius: 30,
            pointStyle: 'circle',
            pointBorderWidth: 2		            
        }]
    };

    const config = {
        type: 'line',
        data: data,
        options:{
            responsive: true,
            legend:{
                display:false
            },
            plugins:{
                /*legend:{
                    display:false
                },*/
            },
            maintainAspectRatio: true,
        },		        
    };

    const conversionChart = new Chart(ctx, config);

    // function to update our chart
    function ajax_chart(chart, url, data) {
    	var queryData = data || {month:10,year:2021};
        jQuery.ajax({
            url: url,
            method:'POST',
            data: queryData,
            async:false,
            success:function( response ){
                var response = JSON.parse(response);
                let data = [];
                for( const value in response.data ){
                    data.push(response.data[value]);
                }
                chart.data.labels = response.label;
                chart.data.datasets[0].data = data;
                chart.update();
            }
        });
    }

    var monnthSelect = document.querySelector("#monthSelect");
    monnthSelect.addEventListener('change', function(e){
        ajax_chart(conversionChart, json_url, {month:this.value, year:2021});
    });    

    setTimeout(function (argument) {
        ajax_chart(conversionChart, json_url);
    }, 500);

})(jQuery);