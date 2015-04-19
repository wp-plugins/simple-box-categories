<?php
/*
 * Plugin Name: Simple Box Categories
 * Plugin URI: http://www.tchernitchenko.com
 * Description: A simple and clean category widget.
 * Author: Alexander Tchernitchenko
 * Version: 1.0.2
 * Author URI: http://www.tchernitchenko.com
 * License: GPL2
 */

class Simple_Box_Categories extends WP_Widget
{

	public function __construct() {
		$widget_ops = array( 'classname' => 'SimpleBoxCategories', 'description' => 'A custom categories widget' );
		$this->WP_Widget( 'SimpleBoxCategories', 'Simple Box Categories', $widget_ops );

		wp_enqueue_style( 'simple_box_categories_css', plugin_dir_url(__FILE__) . 'simple-box-categories.css' );

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'my-script-handle', plugins_url( 'simple-box-categories.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
	}


	/**
	 * Takes any hexcolor and makes it either darker och lighter. Useable with
	 * any type of hexcode including examples: '#ffe', '444', 12fff32, '#412dd1'.
	 *
	 * @param string|int $hexcode A hexcode either with '#' or without.
	 * @param int $steps Value between -255 and 255 determining darker/lighter. (Negative = darker, positive = lighter).
	 * @return string $return Returns a hexcode which is modified by the steps parameter using the hex parameter as base.
	 */
	public function sbc_color_adjust( $hex, $steps ) {

		    $steps = max( -255, min( 255, $steps ) );

		    // Normalize into a six character long hex string
		    $hex = str_replace( '#', '', $hex );
		    if ( strlen( $hex ) == 3 ) {
		        $hex = str_repeat( substr( $hex, 0, 1 ), 2 ) . str_repeat( substr( $hex, 1, 1 ), 2 ) . str_repeat( substr( $hex, 2, 1 ), 2 );
		    }

		    // Split into three parts: R, G and B
		    $color_parts = str_split( $hex, 2 );
		    $return = '#';

		    // Convert to decimal, adjust color and make it a hexcode.
		    foreach ( $color_parts as $color ) {
		        $color   = hexdec( $color );
		        $color   = max( 0, min( 255, $color + $steps ) );
		        $return .= str_pad( dechex( $color ), 2, '0', STR_PAD_LEFT );
		    }

		    return $return;
	}


	/**
	 * Very small function that converts an interger or a string to a string
	 * containing px at the end. Used to insert the return value into style.
	 *
	 * @param string|int $num
	 * @return string $numpx Numeric value with px added to the end.
	 */
	public function sbc_px_fix( $num ) {

		$numpx = str_replace( 'px', '', $num );
		return $numpx . "px";

	}



	public function widget( $args, $instance ) {

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Categories' ) : $instance['title'], $instance, $this->id_base );
		$c = ! empty( $instance['count'] ) ? '1' : '0';
		$h_e = ! empty( $instance['hide_empty'] ) ? '1' : '0';
		$bg_c = ! empty( $instance['background_color'] ) ? $instance['background_color'] : '#72AFE6';
		$d_t = ! empty( $instance['disable_title'] ) ? '1' : '0';
		$ani = ! empty( $instance['animation'] ) ? '1' : '0';
		$f_d = ! empty( $instance['fade_dark'] ) ? $instance['fade_dark'] : 'dark';
		$f_c = ! empty( $instance['font_color'] ) ? $instance['font_color'] : 'white';


		echo $args['before_widget'];

		if ( $title && $d_t == 0 ) {
			echo $args['before_title'] . "<h4 class='widget-title'>$title</h4>" . $args['after_title'];
		} else {
			$title = null;
		}

		// Decides the fade value for hovering, see sbc_color_adjust function
		if ( $f_d === 'dark' ) {
			$fade_value = -35;
		} else {
			$fade_value = 25;
		}

		// Decides font color
		if ( $f_c === 'white' ) {
			$font_color = '#fefefe';
		} else {
			$font_color = '#333';
		}

?>

<style>
.sbc-hvr-fade:hover, .hvr-fade:focus, .hvr-fade:active {
	background-color: <?php echo self::sbc_color_adjust($bg_c, $fade_value); ?>;
}

.sbc-no-animation:hover {
	background-color: <?php echo self::sbc_color_adjust($bg_c, $fade_value); ?>;
}

.sbc-box {
	background-color: <?php echo $bg_c; ?>;
	color: <?php echo $font_color; ?>;
}

/* Used to override the themes default link hover color. */
a.sbc-box:hover {
  color:<?php echo $font_color; ?> !important;
}

</style>


<div class="sbc-box-container">
<?php

	// Arguments used to fetch the category-array.
	$cat_args = array( 'orderby' => 'name', 'hide_empty' => $h_e );
	$categories = get_categories($cat_args);

	// Decides animation class
	if ( $ani ) {
		$animation_class = 'sbc-hvr-grow sbc-hvr-fade';
	} else {
		$animation_class = 'sbc-no-animation';
	}

	// Print out the ctegory boxes
	foreach ( $categories as $category ) {
		?>
		<a class='sbc-box <?php echo $animation_class; ?>' href="<?php echo esc_url( get_category_link( $category->cat_ID ) ); ?>">
				<?php echo $category->name; ?>
				<?php if ( $c ) {
					echo ' (' . $category->category_count . ')';
				}
				?>
		</a>
		<?php
	}
?>
</div>


<?php

		echo $args['after_widget'];
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['count'] = ! empty( $new_instance['count'] ) ? 1 : 0;
		$instance['hide_empty'] = ! empty( $new_instance['hide_empty'] ) ? 1 : 0;
		$instance['disable_title'] = ! empty( $new_instance['disable_title'] ) ? 1 : 0;
		$instance['animation'] = ! empty( $new_instance['animation'] ) ? 1 : 0;
		$instance['font_color'] = ! empty( $new_instance['font_color'] ) ? $new_instance['font_color'] : 'white';
		$instance['fade_dark'] = ! empty( $new_instance['fade_dark'] ) ? $new_instance['fade_dark'] : 'dark';
		$instance['background_color'] = strip_tags( $new_instance['background_color'] );
		return $instance;
	}

	public function form( $instance ) {
		// Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'background_color' => '', 'border_radius' => '') );
		$title = ! empty( $instance['title'] ) ? esc_attr($instance['title']) : 'Categories';
		$background_color = ! empty( $instance['background_color'] ) ? $instance['background_color'] : '#72AFE6';
		$count = isset($instance['count']) ? (bool) $instance['count'] :false;
		$hide_empty = isset( $instance['hide_empty'] ) ? (bool) $instance['hide_empty'] : true;
		$disable_title = isset( $instance['disable_title'] ) ? (bool) $instance['disable_title'] : false;
		$animation = isset( $instance['animation'] ) ? (bool) $instance['animation'] : true;
		$fade_dark = ! empty( $instance['fade_dark'] ) ? $instance['fade_dark'] : 'dark';
		$font_color = ! empty( $instance['font_color'] ) ? $instance['font_color'] : 'white';
?>
		<p><b><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title' ); ?></b></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><b><label for="<?php echo $this->get_field_id('background_color'); ?>"><?php _e( 'Box background color' ); ?></b><br/></label>
		<input type="text" id="<?php echo $this->get_field_id('background_color'); ?>" name="<?php echo $this->get_field_name('background_color'); ?>" value="<?php echo $background_color; ?>" class="sbc-color-field" /></p>
		
		<p><b><label for="<?php echo $this->get_field_id('fade_dark'); ?>"><?php _e( 'Hover fade type' ); ?></label></b><br/>
		<input type="radio" class="radio" id="<?php echo $this->get_field_id('fade_dark'); ?>" name="<?php echo $this->get_field_name('fade_dark'); ?>" value="dark" <?php checked( 'dark', $fade_dark ); ?> /> Darker <br />
		<input type="radio" class="radio" id="<?php echo $this->get_field_id('fade_dark'); ?>" name="<?php echo $this->get_field_name('fade_dark'); ?>" value="light" <?php checked( 'light', $fade_dark ); ?> /> Lighter</p>

		<p class='sbc-advanced-options-button'>Advanced Options &darr;</p>
		<div class="sbc-advanced-options">

		<p><b><label for="<?php echo $this->get_field_id('font_color'); ?>"><?php _e( 'Font color' ); ?></label></b><br/>
		<input type="radio" class="radio" id="<?php echo $this->get_field_id('font_color'); ?>" name="<?php echo $this->get_field_name('font_color'); ?>" value="white" <?php checked( 'white', $font_color ); ?> /> White <br />
		<input type="radio" class="radio" id="<?php echo $this->get_field_id('font_color'); ?>" name="<?php echo $this->get_field_name('font_color'); ?>" value="black" <?php checked( 'black', $font_color ); ?> /> Black</p>

		<p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hide_empty'); ?>" name="<?php echo $this->get_field_name('hide_empty'); ?>"<?php checked( $hide_empty ); ?> />
		<label for="<?php echo $this->get_field_id('hide_empty'); ?>"><?php _e( 'Hide empty categories' ); ?></label></p>

		<p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('animation'); ?>" name="<?php echo $this->get_field_name('animation'); ?>"<?php checked( $animation ); ?> />
		<label for="<?php echo $this->get_field_id('animation'); ?>"><?php _e( 'Enable hover animation' ); ?></label></p>

		<p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('disable_title'); ?>" name="<?php echo $this->get_field_name('disable_title'); ?>"<?php checked( $disable_title ); ?> />
		<label for="<?php echo $this->get_field_id('disable_title'); ?>"><?php _e( 'Remove the title' ); ?></label></p>

		<p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>"<?php checked( $count ); ?> />
		<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e( 'Show post counts' ); ?></label></p>

		</div>

<?php
	}

}

// Register widget
function simple_box_categories_init() {
	register_widget( 'Simple_Box_Categories' );
}

add_action( 'widgets_init', 'simple_box_categories_init' );