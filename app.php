<?php
/*
Plugin Name: Gambling Game Retriever
Plugin URI: http://pavlo.cc
Description: Get and display casino games from API
Version: 1.0
Author: Pavlo Harkusha
Author URI: http://pavlo.cc
License: GPL2, MIT License
*/

class Casino_Widget extends WP_Widget
{
    public function __construct()
    {
        $options = array(
            'classname' => 'cls_wdg_pavlo',
            'description' => 'A game listing widget',
        );

        parent::__construct(
            'cls_wdg_pavlo', 'Top 3 Games', $options,
            $this->defaults = array(
                'games' => array(
                    'name'  => '',
                    'id' => 'id',
                    'images' => array(
                        'rectangle' => array(
                            'width' => '',
                            'url' => ''
                        ),
                    ),
                ),
            )
        );

        add_action('wp_enqueue_scripts', array($this, 'cls_wdg_enqueue_assets'));
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget'];

        echo $args['before_title'] . apply_filters('widget_title', 'Top 3 Games') . $args['after_title'];

        $best_game = $instance[ 'best_game' ] ? 'true' : 'false';
        $tones_winning = $instance[ 'tones_winning' ] ? 'true' : 'false';
        $great_fun = $instance[ 'great_fun' ] ? 'true' : 'false';

        extract( $args );
        $game_id      = esc_attr( $instance['game_id'] );

        /** API data */
        $api_code = 'LPUK';
        $api_url = "http://primeapi.com/feeds/v1/games/?brand_local_code=".$api_code;

        /** @var $ch = initialize cURL*/
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        /** Return the response */
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        /** Set the url */
        curl_setopt($ch, CURLOPT_URL, $api_url);

        /**  Execute */
        $result = curl_exec($ch);

        /** check for errors */
        if( curl_error( $ch ) ) {
            return false;
        } else {
            if ($result === FALSE) {
                die('Curl failed: ' . curl_error($ch)) && curl_close($ch);
            }
        }

        /** Closing  */
        curl_close($ch);

        /** Decoding JSON data (retrieving API objects) */
        $encode = json_decode($result);

        /** Test Std Objects */
        //echo '<pre>' . print_r($encode->games, TRUE) . '</pre>';

        print_r('<div class="col-md-2">');

        /** Access JSON array dataset objects */
        foreach ($encode->games as $item) {
            if ($item->id == $game_id) {
                print_r("<h1>Game name: </h1><p>".$item->name."</p>");
                print_r("<h1>Rating: </h1><p>".$item->rating."</p>");
                print_r("<a href=".$item->images->rectangle[0]->url ."><img width=".$item->images->rectangle[0]->width." src=".$item->images->rectangle[0]->url."></a>");
            }
        }

        /** Checkboxes fields */

        if( 'on' == $instance[ 'best_game' ] ) : ?>
            <div>
                <p>Best game</p>
            </div>
        <?php endif;
        if( 'on' == $instance[ 'tones_winning' ] ) : ?>
            <div>
                <p>Tones of winning</p>
            </div>
        <?php endif;
        if( 'on' == $instance[ 'great_fun' ] ) : ?>
            <div>
                <p>Great fun</p>
            </div>
        <?php endif;

        print_r('</div>');

        echo $args['after_widget'];
    }

    public function form( $instance ) {

        /** Form view fields in widgets panel */
        $game_id      = esc_attr( isset($instance['game_id'] ));
        $bgame     = esc_attr( isset($instance['best_game'] ));
        $winning      = esc_attr( isset($instance['tones_winning'] ));
        $fun   = esc_attr( isset($instance['great_fun'] ));
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('game_id'); ?>"><?php _e('Game Id:'); ?></label>
            <input id="<?php echo $this->get_field_id('game_id'); ?>" name="<?php echo $this->get_field_name('game_id'); ?>" type="text" value="<?php echo $game_id; ?>" />
        </p>

        <p>
            <input class="checkbox" type="checkbox" <?php checked( isset($bgame), 'on' ); ?> id="<?php echo $this->get_field_id( 'best_game' ); ?>" name="<?php echo $bgame; ?>" />
            <label for="<?php echo $this->get_field_id( 'best_game' ); ?>">Best game</label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked( isset($winning), 'on' ); ?> id="<?php echo $this->get_field_id( 'tones_winning' ); ?>" name="<?php echo $winning; ?>" />
            <label for="<?php echo $this->get_field_id( 'tones_winning' ); ?>">Tones of winning</label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked( isset($fun), 'on' ); ?> id="<?php echo $this->get_field_id( 'great_fun' ); ?>" name="<?php echo $fun; ?>" />
            <label for="<?php echo $this->get_field_id( 'great_fun' ); ?>">Great fun</label>
        </p>
        <?php
    }

    public function update( $new_instance, $old_instance ) {

        /** Update variables on change */
        $instance = $old_instance;

        $instance['game_id'] = strip_tags( $new_instance['game_id'] );
        /* checkboxes */
        $instance[ 'best_game' ] = $new_instance[ 'best_game' ];
        $instance[ 'tones_winning' ] = $new_instance[ 'tones_winning' ];
        $instance[ 'great_fun' ] = $new_instance[ 'great_fun' ];

        return $instance;
    }

    public function cls_wdg_enqueue_assets()
    {
        if (is_page()) {
            wp_enqueue_style('cls_styles', plugin_dir_url(__FILE__) . 'assets/css/styles.css');
            wp_enqueue_script('cls_scripts', plugin_dir_url(__FILE__) . 'assets/js/slide_front.js', array(), null, false);
        }
    }
}