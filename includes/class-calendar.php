<?php
if (!defined('ABSPATH')) exit;

class TC_Calendar {

    public function __construct(){

        add_shortcode('training_calendar',[$this,'calendar_shortcode']);
        add_shortcode('todays_training_events', [$this, 'todays_events_shortcode']);

        add_action('wp_enqueue_scripts',[$this,'scripts']);
        add_action('wp_ajax_get_training_sessions',[$this,'get_sessions']);
        add_action('wp_ajax_nopriv_get_training_sessions',[$this,'get_sessions']);
    }

    public function scripts(){

        wp_enqueue_script(
            'fullcalendar',
            'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js',
            [],
            null,
            true
        );

        wp_enqueue_script(
            'training-calendar-js',
            TC_PLUGIN_URL.'assets/js/calendar.js',
            ['jquery','fullcalendar'],
            time(), // force refresh
            true
        );

        wp_localize_script(
            'training-calendar-js',
            'training_ajax',
            ['ajaxurl'=>admin_url('admin-ajax.php')]
        );

        wp_enqueue_style(
            'training-calendar-css',
            TC_PLUGIN_URL.'assets/css/calendar.css'
        );
    }

    public function calendar_shortcode(){

        ob_start();
        ?>

        <div id="training-calendar"></div>
        <div id="training-events"></div>

        <?php
        return ob_get_clean();
    }

    // =========================
    // CALENDAR EVENTS (AJAX)
    // =========================
    public function get_sessions(){

        $sessions = get_posts([
            'post_type' => 'training_session',
            'posts_per_page' => -1
        ]);

        $events = [];

        foreach($sessions as $session){

            $product  = get_post_meta($session->ID, '_training_product', true);
            $location = get_post_meta($session->ID, '_training_location', true);

            if(!$product) continue;

            $product_title = get_the_title($product);
            $product_link  = get_permalink($product);

            // ===== MANUAL =====
            $schedule = get_post_meta($session->ID, '_training_schedule', true);

            if(!empty($schedule)){
                foreach($schedule as $item){

                    if(empty($item['date']) || empty($item['time'])) continue;

                    $start_time = $item['time'];
                    $end_time   = !empty($item['end_time']) ? $item['end_time'] : '';

                    $start_display = date("g:i A", strtotime($start_time));
                    $end_display   = $end_time ? date("g:i A", strtotime($end_time)) : '';

                    $events[] = [
                        'title' => $product_title,
                        'start' => $item['date'] . 'T' . $start_time,
                        'end'   => $end_time ? $item['date'] . 'T' . $end_time : '',
                        'url'   => $product_link,
                        'extendedProps' => [
                            'time' => $end_display ? "$start_display - $end_display" : $start_display,
                            'location' => $location ? $location : ''
                        ]
                    ];
                }
            }

            // ===== RECURRING =====
            $recurring = get_post_meta($session->ID,'_training_recurring',true);

            if(!empty($recurring) && !empty($recurring['start_date']) && !empty($recurring['end_date'])){

                $start_date = strtotime($recurring['start_date']);
                $end_date   = strtotime($recurring['end_date']);
                $days       = isset($recurring['days']) ? $recurring['days'] : [];

                $current = $start_date;

                while($current <= $end_date){

                    $day_number = date('w',$current);

                    if(in_array($day_number,$days)){

                        $date = date('Y-m-d',$current);

                        $start_time = $recurring['start_time'];
                        $end_time   = $recurring['end_time'];

                        $start_display = date("g:i A", strtotime($start_time));
                        $end_display   = date("g:i A", strtotime($end_time));

                        $events[] = [
                            'title' => $product_title,
                            'start' => $date . 'T' . $start_time,
                            'end'   => $date . 'T' . $end_time,
                            'url'   => $product_link,
                            'extendedProps' => [
                                'time' => "$start_display - $end_display",
                                'location' => $location ? $location : ''
                            ]
                        ];
                    }

                    $current = strtotime("+1 day",$current);
                }
            }
        }

        wp_send_json($events);
    }

    // =========================
    // TODAY EVENTS SHORTCODE
    // =========================
    public function todays_events_shortcode(){

        $today = date('Y-m-d');

        $sessions = get_posts([
            'post_type' => 'training_session',
            'posts_per_page' => -1
        ]);

        ob_start();

        echo '<div class="today-events-wrapper">';

        $found = false;

        foreach($sessions as $session){

            $product  = get_post_meta($session->ID, '_training_product', true);
            $location = get_post_meta($session->ID, '_training_location', true);

            if(!$product) continue;

            $product_title = get_the_title($product);
            $product_link  = get_permalink($product);

            // MANUAL
            $schedule = get_post_meta($session->ID, '_training_schedule', true);

            if(!empty($schedule)){
                foreach($schedule as $item){

                    if($item['date'] === $today){

                        $found = true;

                        $start = date("g:i A", strtotime($item['time']));
                        $end   = !empty($item['end_time']) ? date("g:i A", strtotime($item['end_time'])) : '';

                        echo '<div class="today-event-card">';
                        echo '<h4>'.$product_title.'</h4>';
                        echo '<p><strong>Time:</strong> '.($end ? "$start - $end" : $start).'</p>';
                        if($location){
                            echo '<p><strong>Location:</strong> '.$location.'</p>';
                        }
                        echo '<a href="'.$product_link.'" class="event-btn">View / Book</a>';
                        echo '</div>';
                    }
                }
            }

            // RECURRING
            $recurring = get_post_meta($session->ID,'_training_recurring',true);

            if(!empty($recurring)){

                $start_date = strtotime($recurring['start_date']);
                $end_date   = strtotime($recurring['end_date']);
                $days       = $recurring['days'];

                $today_ts = strtotime($today);

                if($today_ts >= $start_date && $today_ts <= $end_date){

                    $day_number = date('w', $today_ts);

                    if(in_array($day_number,$days)){

                        $found = true;

                        $start = date("g:i A", strtotime($recurring['start_time']));
                        $end   = date("g:i A", strtotime($recurring['end_time']));

                        echo '<div class="today-event-card">';
                        echo '<h4>'.$product_title.'</h4>';
                        echo '<p><strong>Time:</strong> '.$start.' - '.$end.'</p>';
                        if($location){
                            echo '<p><strong>Location:</strong> '.$location.'</p>';
                        }
                        echo '<a href="'.$product_link.'" class="event-btn">View / Book</a>';
                        echo '</div>';
                    }
                }
            }
        }

        if(!$found){
            echo '<p>No training sessions today.</p>';
        }

        echo '</div>';

        return ob_get_clean();
    }
}