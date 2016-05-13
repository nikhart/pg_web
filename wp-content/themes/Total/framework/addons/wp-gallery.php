<?php
/**
 * Create custom gallery output for the WP gallery shortcode
 *
 * @package Total WordPress theme
 * @subpackage Framework
 * @version 3.3.5
 */

// Render the class
if ( ! class_exists( 'WPEX_Custom_WP_Gallery' ) ) {
	class WPEX_Custom_WP_Gallery {

		/**
		 * Initialize the class and set its properties.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			add_filter( 'wpex_image_sizes', array( $this, 'add_image_sizes' ), 999 );
			add_filter( 'post_gallery', array( $this, 'output' ), 10, 2 );
		}

		/**
		 * Adds image sizes for your galleries to the image sizes panel
		 *
		 * @since 2.0.0
		 */
		public static function add_image_sizes( $sizes ) {
			if ( apply_filters( 'wpex_custom_wp_gallery', true ) ) {
				$sizes['gallery'] = array(
					'label'   => esc_html__( 'WordPress Gallery', 'total' ),
					'width'   => 'gallery_image_width',
					'height'  => 'gallery_image_height',
					'crop'    => 'gallery_image_crop',
					'section' =>  'other',
				);
			}
			return $sizes;
		}
		
		/**
		 * Tweaks the default WP Gallery Output
		 *
		 * @since 1.0.0
		 */
	   public static function output( $output, $attr ) {

	   		// If disabled return output
	   		if ( ! apply_filters( 'wpex_custom_wp_gallery', true ) ) {
	   			return $output;
	   		}
			
			// Main Variables
			global $post, $wp_locale, $instance;
			$instance++;
			static $instance = 0;
			$output          = '';

			// Sanitize orderby statement
			if ( isset( $attr['orderby'] ) ) {
				$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
				if ( ! $attr['orderby'] ) {
					unset( $attr['orderby'] );
				}
			}

			// Get shortcode attributes
			extract( shortcode_atts( array(
				'order'      => 'ASC',
				'orderby'    => 'menu_order ID',
				'id'         => $post->ID,
				'columns'    => 3,
				'include'    => '',
				'exclude'    => '',
				'img_height' => '',
				'img_width'  => '',
				'size'       => '',
				'crop'       => '',
			), $attr ) );

			// Get post ID
			$id = intval( $id );

			if ( 'RAND' == $order ) {
				$orderby = 'none';
			}

			if ( ! empty( $include ) ) {
				$include      = preg_replace( '/[^0-9,]+/', '', $include );
				$_attachments = get_posts(
					array(
						'include'        => $include,
						'post_status'    => '',
						'inherit'        => '',
						'post_type'      => 'attachment',
						'post_mime_type' => 'image',
						'order'          => $order,
						'orderby'        => $orderby
					)
				);

			$attachments = array();
				foreach ( $_attachments as $key => $val ) {
					$attachments[$val->ID] = $_attachments[$key];
				}
			} elseif ( ! empty( $exclude ) ) {
				$exclude     = preg_replace( '/[^0-9,]+/', '', $exclude );
				$attachments = get_children( array(
					'post_parent'    => $id,
					'exclude'        => $exclude,
					'post_status'    => 'inherit',
					'post_type'      => 'attachment',
					'post_mime_type' => 'image',
					'order'          => $order,
					'orderby'        => $orderby) );
			} else {
				$attachments = get_children( array(
					'post_parent'    => $id,
					'post_status'    => 'inherit',
					'post_type'      => 'attachment',
					'post_mime_type' => 'image',
					'order'          => $order,
					'orderby'        => $orderby
				) );
			}

			if ( empty( $attachments ) ) {
				return '';
			}

			if ( is_feed() ) {
				$output = "\n";
				$size   = $size ? $size : 'thumbnail';
				foreach ( $attachments as $attachment_id => $attachment )
					$output .= wp_get_attachment_link( $attachment_id, $size, true ) . "\n";
				return $output;
			}

			// Get columns #
			$columns = intval( $columns );

			// Set cropping sizes
			if ( $columns > 1 ) {
				$img_width  = $img_width ? $img_width : wpex_get_mod( 'gallery_image_width' );
				$img_height = $img_height ? $img_height : wpex_get_mod( 'gallery_image_height' );
			}

			// Sanitize Data
			$size = $size ? $size : 'large';
			$size = ( $img_width || $img_height ) ? 'wpex_custom' : $size;
			$crop = $crop ? $crop : 'center-center';

			// Float
			$float = is_rtl() ? 'right' : 'left';

			// Load lightbox skin stylesheet
			wpex_enqueue_ilightbox_skin();


			// Begin output
			$output .= '<div id="gallery-'. $instance .'" class="wpex-gallery wpex-row lightbox-group clr">';
				
				// Begin Loop
				$count  = 0;
				foreach ( $attachments as $attachment_id => $attachment ) {

					// Increase counter for clearing floats
					$count++;

					// Attachment Vars
					$attachment_data = wpex_get_attachment_data( $attachment_id );
					$alt             = $attachment_data['alt'];
					$caption         = $attachment_data['caption'];
					$video           = $attachment_data['video'];
					$lightbox_url    = $video ? $video : wpex_get_lightbox_image( $attachment_id );

					// Generate the image HTMl
					$img_html = wpex_get_post_thumbnail( array(
						'attachment' => $attachment_id,
						'size'       => $size,
						'width'      => $img_width,
						'height'     => $img_height,
						'crop'       => $crop
					) );

					// Add data attributes for lightbox
					if ( $video ) {
						$lightbox_data = ' data-options="thumbnail: \''. wpex_get_lightbox_image( $attachment_id ) .'\', width:1920, height:1080"';
					} else {
						$lightbox_data = ' data-type="image"';
					}
			
					// Start Gallery Item
					$output .= '<figure class="gallery-item '. wpex_grid_class( $columns ) .' col col-'. $count .'">';
					
						// Display image
						$output .= '<a href="'. $lightbox_url .'" title="'. wp_strip_all_tags( $caption ) .'" class="wpex-lightbox-group-item"'. $lightbox_data .'>';
							$output .= $img_html;
						$output .= '</a>';

						// Display Caption
						if ( trim ( $attachment->post_excerpt ) ) {
							if ( wpex_is_front_end_composer() ) {
								$output .= '<div class="gallery-caption">'. wptexturize( $attachment->post_excerpt ) . '</div>';
							} else {
								$output .= '<figcaption class="gallery-caption">'. wptexturize( $attachment->post_excerpt ) . '</figcaption>';
							}
						}
						
					// Close gallery item div
					$output .= "</figure>";

					// Set vars to remove margin on last item of each row and clear floats
					if ( $count == $columns ) {
						$count = '0';
					}
					
				}

			// Close gallery div
			$output .= "</div>\n";

			return $output;
		}
	}
}
$wpex_custom_wp_gallery = new WPEX_Custom_WP_Gallery;