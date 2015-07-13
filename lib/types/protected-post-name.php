<?php
namespace DDBBD\Types;

/**
 * Dana Don-Boom-Boom-Doo Immutable Post Name
 *
 * @todo Hide post_name form from Quick Edit
 *
 * @author Toshimichi Mimoto
 *
 * @package WordPress
 * @subpackage DDBBD
 *
 * @see http://ja.forums.wordpress.org/topic/21239
 */
class Protected_Post_Name {

	/**
	 * Singleton pattern
	 *
	 * @uses DDBBD\Singleton
	 */
	use \DDBBD\Singleton;

	/**
	 * Post Types, disallowed to change Post Name
	 *
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
	}

	/**
	 * Constructor - add filter/action hooks
	 *
	 * @access private
	 */
	protected function __construct() {
		add_action( 'add_meta_boxes', [ &$this, '_remove_slugdiv' ], 99, 2 );
		add_filter( 'get_sample_permalink_html', [ &$this, '_sample_permalink_html' ], 10, 2 );
		add_filter( 'wp_insert_post_data', [ &$this, '_immutable_post_name'], 99, 2 );
	}

	/**
	 * Remove slugdiv from admin UIs
	 *
	 * @access private
	 *
	 * @param  string $post_type
	 * @param  
	 */
	public function _remove_slugdiv( $post_type, \WP_Post $post ) {
		if ( $this->_is_protected( $post_type, $post ) )
			remove_meta_box( 'slugdiv', $post_type, 'normal' );
	}

	/**
	 * @access private
	 *
	 * @see    https://github.com/WordPress/WordPress/blob/4.2-branch/wp-admin/includes/post.php#L1247
	 *
	 * @param  string      $return
	 * @param  int|WP_Post $id
	 * @return string
	 */
	public function _sample_permalink_html( $return, $id ) {
		$post_type = get_post_type( $id );
		if ( ! $this->_is_protected( $post_type, $id ) )
			return $return;

		$return  = '<strong>' . __('Permalink:') . "</strong>\n";
		$return .= '<span id="sample-permalink" tabindex="-1">' . get_permalink( $id ) . "</span>\n";
		if ( current_user_can( 'read_post', $id ) ) {
			$return .= sprintf(
				'<span id="view-post-btn"><a href="%s" class="button button-small">%s</a></span>',
				get_permalink( $id ),
				get_post_status( $id ) === 'draft' ? __( 'Preview' ) : get_post_type_object( $post_type )->labels->view_item
			);
			$return .= "\n";
		}
		return $return;
	}

	/**
	 * Disallow to update Post Name
	 *
	 * @access private
	 *
	 * @param  array $data
	 * @param  array $postarr
	 * @return array
	 */
	public function _immutable_post_name( $data, $postarr ) {
		list( $post_type, $post ) = [
			$postarr['post_type'],
			$postarr['ID']
		];
		if ( ! $this->_is_protected( $post_type, $post ) )
			return $data;

		/**
		 * Current post name
		 * @var string
		 */
		$old_post_name = get_post( $postarr['ID'] )->post_name;

		return array_merge( $data, [ 'post_name' => sanitize_title( $old_post_name ) ] );
	}

	/**
	 * Return Post Name being protected OR not
	 *
	 * @access private
	 *
	 * @param  string $post_type
	 * @param  int|WP_Post $post
	 * @return boolean
	 */
	private function _is_protected( $post_type, $post ) {
		if ( ! in_array( $post_type, $this->post_types, true ) )
			return false;

		if ( get_post_status( $post ) === 'draft' )
			/**
			 * Filter draft status' Post Name protected
			 *
			 * @param boolean false
			 * @param string  $post_type
			 * @param int|WP_Post $post
			 */
			return apply_filters( 'ddbbd_types_protected_post_name_is_protedted_draft', false, $post_type, $post );

		/**
		 * Filter misc Post Name protected
		 *
		 * @param boolean true
		 * @param string  $post_type
		 * @param int|WP_Post $post
		 */
		return apply_filters( 'ddbbd_types_protected_post_name_is_protected', true, $post_type, $post );
	}

}
