<?php
/**
 * The template for displaying search forms
 *
 * @package Total WordPress theme
 * @subpackage Partials
 * @version 3.3.3
 */ ?>

<form method="get" class="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<input type="search" class="field" name="s" placeholder="<?php echo esc_html__( 'Search', 'total' ); ?>" />
	<?php if ( defined( 'ICL_LANGUAGE_CODE' ) ) { ?>
		<input type="hidden" name="lang" value="<?php echo( ICL_LANGUAGE_CODE ); ?>"/>
	<?php } ?>
	<button type="submit" class="searchform-submit">
		<span class="fa fa-search"></span>
	</button>
</form>