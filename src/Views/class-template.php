<?php
/**
 * A basic templating implementation.
 *
 * @package soter
 */

namespace Soter\Views;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * This class mimics the locate_template()/load_template() functionality from
 * WordPress core with two significant differences:
 *
 * 1) The act of locating a template is delegated to a locator which means we can
 *    employ different or multiple strategies for locating templates. In practice
 *    this means that we can easily define plugin templates which can be overridden
 *    from within a theme.
 * 2) Templates are loaded with the same variables in scope as they are when using
 *    load_template() with the added bonus of being able to explicitly pass in extra
 *    data. In practice this means data can be prepared outside of template files
 *    instead of relying on globals and template tags.
 */
class Template {
	/**
	 * Template locator instance.
	 *
	 * @var Template_Locator_Interface
	 */
	protected $locator;

	/**
	 * Class constructor.
	 *
	 * @param Template_Locator_Interface $locator Template locator instance.
	 */
	public function __construct( Template_Locator_Interface $locator ) {
		$this->locator = $locator;
	}

	/**
	 * Locator getter.
	 *
	 * @return Template_Locator_Interface
	 */
	public function locator() {
		return $this->locator;
	}

	/**
	 * Includes a template file.
	 *
	 * @param  string $name Template name.
	 * @param  array  $data Data to make available to the template.
	 */
	public function output( $name, $data = [] ) {
		if ( ! $template = $this->locator->locate( $this->candidates( $name ) ) ) {
			return;
		}

		static::include_template( $template, $data );
	}

	/**
	 * Captures the output generated by a template file.
	 *
	 * @param  string $name Name of the template file.
	 * @param  array  $data Data to make available to the template.
	 *
	 * @return string
	 */
	public function render( $name, $data = [] ) {
		ob_start();

		$this->output( $name, $data );

		$view = ob_get_contents();
		ob_end_clean();

		return $view;
	}

	/**
	 * Gets a list of template candidates based on a template name.
	 *
	 * @param  string $template Template name.
	 *
	 * @return string[]
	 */
	protected function candidates( $template ) {
		if ( '.php' !== substr( $template, -4 ) ) {
			$template .= '.php';
		}

		$candidates = (array) $template;

		if ( 'templates/' !== substr( $template, 0, 6 ) ) {
			array_unshift( $candidates, 'templates/' . $template );
		}

		return $candidates;
	}

	protected static function include_template( $template, $data ) {
		global $comment,
			   $id,
			   $posts,
			   $post,
			   $user_ID,
			   $wp,
			   $wp_did_header,
			   $wp_query,
			   $wp_rewrite,
			   $wp_version,
			   $wpdb;

		if ( is_array( $wp_query->query_vars ) ) {
			extract( $wp_query->query_vars, EXTR_SKIP );
		}

		if ( isset( $s ) ) {
			$s = esc_attr( $s );
		}

		// Explicitly passed data may overwrite global data...
		extract( $data, EXTR_SKIP );

		include $template;
	}
}
