<?php

add_action( 'widgets_init', function(){
     register_widget( 'Facebook_Pictures' );
});
function var_dump_pre($var){
    echo '<pre style="line-height: 1em;">';
    print_r($var);
    echo '</pre>';
}
class Facebook_Pictures extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'facebook_pictures', // Base ID
            'Facebook Pictures', // Name
            array( 'description' => __( 'This widget show pictures from Facebook.', 'Facebook_Pictures' ), ) // Args
        );
    }

    public function widget( $args, $instance ) {
        wp_enqueue_script( 'script-fb', plugin_dir_url( __FILE__ ) . '/js/script.js', array('jquery') );
        wp_enqueue_style( 'fb-style', plugin_dir_url( __FILE__ ) . '/css/fb-style.css' );

        $title = $instance['title'];
        $amount = $instance['amount'];
        $type = $instance['type'];

        echo $args['before_widget'];
        echo $args['before_title'] . $title . $args['after_title'];
        global $fbdata;

        if( empty( $fbdata->userId ) ){ ?>
            <div class="fb-error">
                <p>
                    No Facebook account linked. Please configure the plugin under Settings -> Facebook Pictures
                </p>
            </div>
        <?php
            echo $args['after_widget'];
            return;
        }


        if($type == 'pictures'){
            wp_enqueue_style( 'thickbox');
            wp_enqueue_script( 'thickbox');
            $data = $fbdata->get_pictures($amount);

            if (empty($data))
                return;

            foreach($data as $fbObject){  ?>
                <div class="photo-wrapper">
                    <a href="<?php echo $fbObject['images'][0]['source']; ?>" class="thickbox">
                        <?php
                        $imageHeight = $fbObject['images'][5]['height'];
                        $imageWidth = $fbObject['images'][5]['width'];
                        $landscape = ($imageHeight < $imageWidth) ? 'landscape' : ''; ?>

                        <img src="<?php echo $fbObject['images'][5]['source'] ?>" class="fb-photo <?php echo $landscape?>">
                    </a>
                </div><?php
            }
        }else if($type == 'albums'){
            $data = $fbdata->get_albums($amount);

            if (empty($data))
                return;

            foreach($data as $fbObject){
                if(empty($fbObject['pictures']))
                    continue;
                ?>
                <div class="album-wrapper">
                    <div class="album-cover">
                        <?php
                        $imageHeight = $fbObject['cover'][0]['images'][5]['height'];
                        $imageWidth = $fbObject['cover'][0]['images'][5]['width'];
                        $landscape = ($imageHeight < $imageWidth) ? 'landscape' : ''; ?>

                        <img src="<?php echo $fbObject['cover'][0]['images'][5]['source'] ?>" class="fb-photo <?php echo $landscape?>">
                    </div>
                    <div class="small-photos-wrapper left">

                        <?php
                        $imageHeight = $fbObject['pictures'][0]['images'][5]['height'];
                        $imageWidth = $fbObject['pictures'][0]['images'][5]['width'];
                        $landscape = ($imageHeight < $imageWidth) ? 'landscape' : ''; ?>

                        <img src="<?php echo $fbObject['pictures'][0]['images'][5]['source'] ?>" class="fb-photo <?php echo $landscape?>">
                    </div>
                    <div class="small-photos-wrapper right">
                        <?php
                        $imageHeight = $fbObject['pictures'][1]['images'][5]['height'];
                        $imageWidth = $fbObject['pictures'][1]['images'][5]['width'];
                        $landscape = ($imageHeight < $imageWidth) ? 'landscape' : ''; ?>
                        <img src="<?php echo $fbObject['pictures'][1]['images'][5]['source'] ?>" class="fb-photo <?php echo $landscape?>">
                    </div>
                    <div class="album-info">
                        <a href="<?php echo $fbObject['link'] ?>"><h3><?php echo $fbObject['name']?></h3></a>
                        <p>Last updated on <?php echo  date('d-m-Y \| h:m \h\s', $fbObject['modified']) ?></p>
                        <p><?php echo $fbObject['photo_count'] ?> Photos</p>
                    </div>
                </div>
                <?php
            }
        }

        echo $args['after_widget'];
    }


    public function update( $new_instance, $old_instance ) {
        return $new_instance;
    }

    public function form( $instance ) {
        $defaultValues = array(
            'title' => __( 'Facebook Pictures', 'Facebook_Pictures' ),
            'type' => 'pictures',
            'amount' => 4
        );

        $instance = wp_parse_args($instance, $defaultValues);
        ?>
        <div>
            <label>Title</label>
            <input type="text" name="<?php echo $this->get_field_name('title') ?>" value="<?php echo $instance['title'] ?>"/>
        </div>

        <div>
            <label><input type="radio" <?php echo $instance['type'] == 'pictures' ? 'checked="checked"' : '' ?> name="<?php echo $this->get_field_name('type') ?>" value="pictures" />Pictures</label>
            <label><input type="radio" <?php echo $instance['type'] == 'albums' ? 'checked="checked"' : '' ?> name="<?php echo $this->get_field_name('type') ?>" value="albums" />Albums</label>
        </div>

        <div>
            <label>Number of photos to show</label>
            <input type="text" name="<?php echo $this->get_field_name('amount') ?>" value="<?php echo $instance['amount'] ?>"/>
        </div>

        <?php
    }
}

?>