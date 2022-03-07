<?php 
/**
 * @package Neo Chart.js Example
 * @version 1.0.0
 */
/*
Plugin Name: Neo Chart.js Example
Plugin URI: 
Description: simple example on how to use chart.js in wordpress
Author: Nilesh Kumar Chouhan (neel_nilesh@hotmail.com)
Author URI: https://www.linkedin.com/in/nilesh-kumar-chouhan-wp-dev/
Version: 1.0.0
*/

add_action( 'admin_menu', 'register_neo_dashboard_menu_page' );
function register_neo_dashboard_menu_page(){
    add_menu_page( 
        __( 'Neo Post Chart', 'textdomain' ),
        'Neo Post Chart',
        'manage_options',
        'neo-post-chart',
        'neo_dashboard_menu_page',
        '',
        6
    ); 
}
function neo_dashboard_menu_page(){
    echo '<div class="wrap">';
    ?>
    <h1> <?php esc_html_e( 'Post Chart', 'wordpress' );  ?></h1>
    <select id="monthSelect" name="month">
        <option value="" disabled>month</option>
        <option value="1">January</option>
        <option value="2">February</option>
        <option value="3">March</option>
        <option value="4">April</option>
        <option value="5">May</option>
        <option value="6">June</option>
        <option value="7">July</option>
        <option value="8">August</option>
        <option value="9">September</option>
        <option value="10">October</option>
        <option value="11">November</option>
        <option value="12">December</option>
    </select>
    <br>
    <canvas id="myChart" width="600" height="500"></canvas>
	<?php
	echo '</div>';
}


add_action( 'admin_enqueue_scripts', 'neo__enqueue_chart_resources' );
function neo__enqueue_chart_resources() {
    // wp_enqueue_script( 'chart-script', plugins_url( '/chart.min.js', __FILE__ ), array('jquery') );
    wp_enqueue_script( 'chart-script', 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js', array('jquery') );
    wp_enqueue_script( 'chartjs-custom', plugins_url( '/chartjs-custom.js', __FILE__ ), array('jquery', 'chart-script') );
    wp_localize_script( 'chartjs-custom', 'chart_ajax_ob', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}

add_action( 'wp_ajax_get_chart_data', 'neo_get_chart_data' );
function neo_get_chart_data() {
    $year = isset($_REQUEST['year']) ? $_REQUEST['year'] : date('Y');
    $month = isset($_REQUEST['month']) ? $_REQUEST['month'] : date('m');
    
    $args = array('year' => $year, 'monthnum' => $month);
    
    $result = neo_get_chart_posts('post',$args);
    
    $response = array();  $labels = array();

    $total_month_days = cal_days_in_month(0, $month, $year);

    foreach ($result as $post) {
        $pDate = $post->post_date;
        $onlydate = date('d', strtotime($pDate));
        if( isset($response[$onlydate]) )
            $response[$onlydate] += $response[$onlydate];
        else
            $response[$onlydate] = 1;
    }

    for ($i=1; $i <= $total_month_days; $i++) { 
        $index = $i < 10 ? "0".$i : $i;
        if( !isset( $response[$index] )){
            $response[$i] = 0;
        }else if($i < 10 ){
            $response[$i] = $response[$index];
            unset($response[$index]);
        }
        $temp_date = $i.'-'.$month.'-'.$year;
        $temp_time = strtotime($temp_date);
        $temp_month=date_i18n('d-F',$temp_time);
        $labels[] = __($temp_month);
    }
    ksort($response);
    echo json_encode( array('data' => $response, 'label'=>$labels) );
    die();
}


function neo_get_chart_posts( $post_type = 'post', array $parameters = array() ) {
    $pre_query_config = array(
        'post_type' => $post_type,
        'post_status' => 'publish',
        'numberposts' => -1,
        'order'    => 'ASC'
    );
    $query_config = array_merge($pre_query_config, $parameters);
    $posts = get_posts($query_config);
    // $posts = new WP_Query($query_config);
    return $posts;
}
