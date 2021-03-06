<?php
/**
 * Single blog meta
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 3.5.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Return if disabled
if ( ! wpex_get_mod( 'blog_post_meta', true ) ) {
	return;
}

// Get meta sections
$sections = wpex_blog_single_meta_sections();

// Return if sections are empty
if ( empty( $sections ) ) {
	return;
}

// Add class for meta with title
$classes = 'meta clr';
if ( 'custom_text' == wpex_get_mod( 'blog_single_header', 'custom_text' ) ) {
	$classes .= ' meta-with-title';
} ?>

<ul class="<?php echo esc_attr( $classes ); ?>">

	<?php
	// Loop through meta sections
	foreach ( $sections as $section ) : ?>

		<?php if ( 'date' == $section ) : ?>

			<li class="meta-date"><span class="fa fa-clock-o" aria-hidden="true"></span><time class="updated" datetime="<?php the_date('Y-m-d');?>"<?php wpex_schema_markup( 'publish_date' ); ?>><?php echo get_the_date(); ?></time></li>

		<?php elseif ( 'author' == $section ) : ?>

			<li class="meta-author"><span class="fa fa-user" aria-hidden="true"></span><span class="vcard author"<?php wpex_schema_markup( 'author_name' ); ?>><span class="fn"><?php the_author_posts_link(); ?></span></span></li>

		<?php elseif ( 'categories' == $section ) : ?>

			<li class="meta-category"><span class="fa fa-folder-o" aria-hidden="true"></span><?php the_category( ', ', get_the_ID() ); ?></li>

		<?php elseif ( 'comments' == $section && comments_open() && ! post_password_required() ): ?>
			
			<li class="meta-comments comment-scroll"><span class="fa fa-comment-o" aria-hidden="true"></span><?php comments_popup_link( esc_html__( '0 Comments', 'total' ), esc_html__( '1 Comment',  'total' ), esc_html__( '% Comments', 'total' ), 'comments-link' ); ?></li>

		<?php else : ?>

			<li class="meta-<?php echo esc_html( $section ); ?>"><?php get_template_part( 'partials/meta/'. $section ); ?></li>

		<?php endif; ?>

	<?php endforeach; ?>

</ul><!-- .meta -->