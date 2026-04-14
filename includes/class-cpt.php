<?php
if (!defined('ABSPATH')) exit;

class TC_CPT {

    public function __construct() {
        add_action('init', [$this,'register_cpt']);
        add_action('add_meta_boxes', [$this,'add_meta_boxes']);
        add_action('save_post', [$this,'save_meta']);
    }

    public function register_cpt() {
        register_post_type('training_session',[
            'label' => 'Training Sessions',
            'public' => true,
            'menu_icon' => 'dashicons-calendar',
            'supports' => ['title']
        ]);
    }

    public function add_meta_boxes() {
        add_meta_box(
            'training_details',
            'Training Details',
            [$this,'render_meta'],
            'training_session'
        );
    }

    public function render_meta($post){

        wp_nonce_field('save_training_schedule','training_nonce');

        $schedule  = get_post_meta($post->ID,'_training_schedule',true);
        $product   = get_post_meta($post->ID,'_training_product',true);
        $recurring = get_post_meta($post->ID,'_training_recurring',true);
        $location  = get_post_meta($post->ID,'_training_location',true);

        if(!is_array($schedule)){
            $schedule = [];
        }
        ?>

        <h3>Manual Dates</h3>

        <div id="training-schedule-wrapper">

        <?php
        if(!empty($schedule)){
            foreach($schedule as $row){
                $date = esc_attr($row['date']);
                $time = esc_attr($row['time']);
                $end  = esc_attr($row['end_time'] ?? '');

                echo '<div class="training-row">
                <input type="date" name="training_dates[]" value="'.$date.'">
                <input type="time" name="training_times[]" value="'.$time.'">
                <input type="time" name="training_end_times[]" value="'.$end.'">
                </div>';
            }
        } else {
            echo '<div class="training-row">
            <input type="date" name="training_dates[]">
            <input type="time" name="training_times[]">
            <input type="time" name="training_end_times[]">
            </div>';
        }
        ?>

        </div>

        <button type="button" id="add-training-row">Add Another Day</button>

        <script>
        document.addEventListener("DOMContentLoaded",function(){

            let button=document.getElementById("add-training-row");

            if(button){
                button.addEventListener("click",function(){

                    let wrapper=document.getElementById("training-schedule-wrapper");

                    let row=document.createElement("div");
                    row.classList.add("training-row");

                    row.innerHTML = `
                    <input type="date" name="training_dates[]">
                    <input type="time" name="training_times[]">
                    <input type="time" name="training_end_times[]">
                    `;

                    wrapper.appendChild(row);
                });
            }

        });
        </script>

        <hr>

        <h3>Recurring Training</h3>

        <p>
        <label>Start Date</label><br>
        <input type="date" name="rec_start_date" value="<?php echo esc_attr($recurring['start_date'] ?? ''); ?>">
        </p>

        <p>
        <label>End Date</label><br>
        <input type="date" name="rec_end_date" value="<?php echo esc_attr($recurring['end_date'] ?? ''); ?>">
        </p>

        <p>
        <label>Start Time</label><br>
        <input type="time" name="rec_start_time" value="<?php echo esc_attr($recurring['start_time'] ?? ''); ?>">
        </p>

        <p>
        <label>End Time</label><br>
        <input type="time" name="rec_end_time" value="<?php echo esc_attr($recurring['end_time'] ?? ''); ?>">
        </p>

        <p><strong>Select Days</strong></p>

        <?php
        $selected_days = $recurring['days'] ?? [];
        $days = [
            1=>'Monday',2=>'Tuesday',3=>'Wednesday',
            4=>'Thursday',5=>'Friday',6=>'Saturday',0=>'Sunday'
        ];

        foreach($days as $key=>$label){
            $checked = in_array($key,$selected_days) ? 'checked' : '';
            echo '<label>
            <input type="checkbox" name="rec_days[]" value="'.$key.'" '.$checked.'> '.$label.'
            </label><br>';
        }
        ?>

        <hr>

        <p>
        <label>Location</label><br>
        <input type="text" name="training_location" 
        value="<?php echo esc_attr($location); ?>" 
        placeholder="e.g. Street 10, London">
        </p>

        <p>
        <label>WooCommerce Product</label><br>

        <select name="training_product">
        <?php
        $products = get_posts([
            'post_type'=>'product',
            'posts_per_page'=>-1
        ]);

        foreach($products as $p){
            echo '<option value="'.$p->ID.'" '.selected($product,$p->ID,false).'>'.$p->post_title.'</option>';
        }
        ?>
        </select>
        </p>

        <?php
    }

    public function save_meta($post_id){

        if(!isset($_POST['training_nonce'])) return;
        if(!wp_verify_nonce($_POST['training_nonce'],'save_training_schedule')) return;
        if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if(!current_user_can('edit_post',$post_id)) return;

        // Manual dates
        if(isset($_POST['training_dates'])){
            $dates = $_POST['training_dates'];
            $times = $_POST['training_times'];
            $end_times = $_POST['training_end_times'];

            $schedule = [];

            for($i=0;$i<count($dates);$i++){
                if(!empty($dates[$i])){
                    $schedule[] = [
                        'date' => sanitize_text_field($dates[$i]),
                        'time' => sanitize_text_field($times[$i]),
                        'end_time' => sanitize_text_field($end_times[$i])
                    ];
                }
            }

            update_post_meta($post_id,'_training_schedule',$schedule);
        }

        // Recurring
        if(isset($_POST['rec_start_date'])){
            $recurring = [
                'start_date' => sanitize_text_field($_POST['rec_start_date']),
                'end_date'   => sanitize_text_field($_POST['rec_end_date']),
                'start_time' => sanitize_text_field($_POST['rec_start_time']),
                'end_time'   => sanitize_text_field($_POST['rec_end_time']),
                'days'       => isset($_POST['rec_days']) ? $_POST['rec_days'] : []
            ];

            update_post_meta($post_id,'_training_recurring',$recurring);
        }

        // Location
        if(isset($_POST['training_location'])){
            update_post_meta(
                $post_id,
                '_training_location',
                sanitize_text_field($_POST['training_location'])
            );
        }

        if(isset($_POST['training_product'])){
            update_post_meta($post_id,'_training_product',intval($_POST['training_product']));
        }
    }
}