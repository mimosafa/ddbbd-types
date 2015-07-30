<?php
namespace DDBBD\Types;

/**
 * Rewrite slug to post_id for custom post types
 *
 * @see  http://www.torounit.com/blog/2011/04/17/683/
 * @see  http://blog.ext.ne.jp/?p=1416
 */
class Numeric_Permalink {

	/**
	 * Singleton pattern
	 *
	 * @uses DDBD\Singleton
	 */
	use \DDBBD\Singleton;

	/**
	 * @var array
	 */
	private $post_types = [];

	/**
	 * Interface
	 *
	 * @access public
	 *
	 * @param  string $post_type
	 * @return void
	 */
	public static function set( $post_type ) {
		if ( ! $post_type = filter_var( $post_type ) )
			return;

		$self = self::getInstance();
		$self->post_types[] = $post_type;

		/**
		 * Remove UIs & Functions to change Post Name
		 *
		 * @uses DDBBD\Types\Protected_Post_Name
		 */
		Protected_Post_Name::set( $post_type );
	}

	/**
	 * Constructor
	 *
	 * @access private
	 */
	protected function __construct() {
		add_action( 'init', [ &$this, 'set_rewrite' ], 11 );
		add_filter( 'post_type_link', [ &$this, 'set_permalink' ], 10, 2 );

		/**
		 * Disallow to change Post Name, Draft status too
		 *
		 * @see DDBBD\Types\Protected_Post_Name::_is_protected()
		 */
		add_filter( 'ddbbd_types_protected_post_name_is_protedted_draft', [ &$this, 'protected_draft_post_name' ], 10, 2 );
	}

	/**
	 * @access private
	 *
	 * @global WP_Rewrite $wp_rewrite
	 *
	 * @return (void)
	 */
	public function set_rewrite() {
		if ( ! $this->post_types )
			return;

		global $wp_rewrite;
		foreach ( $this->post_types as $post_type ) {
			if ( post_type_exists( $post_type ) ) {
				$object = get_post_type_object( $post_type );
				/**
				 * @todo WP_Rewrite::add_permastruct() paramator 3 is not working
				 *
				 * - $args = [ 'with_front' => $object->rewrite['with_front'] ];
				 * - $wp_rewrite->add_permastruct( $post_type, "/{$slug}/%{$post_type}_id%", $args );
				 * These code works in admin page (post.php), but in front-end return 404
				 */
				$slug = '';
				if ( $object->rewrite['with_front'] ) {
					$slug .= ltrim( $wp_rewrite->front, '/' );
				}
				$slug .= $object->rewrite['slug'];
				$wp_rewrite->add_rewrite_tag( "%{$post_type}_id%", '([^/]+)', "post_type={$post_type}&p=" );
				$wp_rewrite->add_permastruct( $post_type, "/{$slug}/%{$post_type}_id%", false );
			}
		}
	}

	/**
	 * @access private
	 *
	 * @see    https://developer.wordpress.org/reference/hooks/post_type_link/
	 *
	 * @global WP_Rewrite $wp_rewrite
	 *
	 * @param  string  $post_link
	 * @param  WP_Post $post
	 * @return string
	 */
	public function set_permalink( $post_link, \WP_Post $post ) {
		if ( ! $this->post_types )
			return;

		$post_type = $post->post_type;
		if ( in_array( $post_type, $this->post_types, true ) ) {
			global $wp_rewrite;
			$url = str_replace( "%{$post_type}_id%", $post->ID, $wp_rewrite->get_extra_permastruct( $post_type ) );
			$post_link = home_url( user_trailingslashit( $url ) );
		}
		return $post_link;
	}

	public function protected_draft_post_name( $bool, $post_type ) {
		if ( in_array( $post_type, $this->post_types, true ) ) {
			return true;
		}
		return $bool;
	}

}
