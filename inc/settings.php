<?php
/**
 * Class for creating WordPress settings.
 *
 * @author     Christopher Lamm chris@theantichris.com
 * @copyright  2013 Christopher Lamm
 * @license    GNU General Public License, version 3
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link       http://www.theantichris.com
 *
 * @package    WordPress
 * @subpackage WordPressPluginFramework
 *
 * @since      5.0.0
 */

/*
 * TODO: Replace "WordPress_Plugin_Framework" with "Plugin_Name".
 * TODO: Replace "WordPressPluginFramework" with "PluginName".
 */

/*
 * Outline
 *
 * 1. add_settings_section( $id = 'eg_setting_section', $title = 'Example settings section in reading', $callback = 'eg_setting_section_callback_function', $page = 'reading' )
 * 2. add_settings_field( $id = 'eg_setting_name', $title = 'Example setting Name', $callback = 'eg_setting_callback_function', $page = 'reading', $section = 'eg_setting_section', $args = array() )
 * 		$page should equal $page in add_settings_section()
 * 		$section should equal $id in add_settings_section()
 * 3. register_setting( $option_group = 'reading', $option_name = 'eg_setting_name', $sanitize_callback = null )
 * 		$option_group should equal the values for $page in the other functions
 * 		$option_name should equal $id in add_settings_field()
 */

class Settings {
	/** @var string The WordPress page slug the settings will appear on. */
	private $page = 'general';
	/** @var array Information about the settings section if used. */
	private $section = array(
		'title'     => 'Default',
		'id'        => 'default',
		'view_path' => null,
		'view_data' => array()
	);

	/**
	 * Class constructor.
	 *
	 * @since 5.0.0
	 *
	 * @param string $page
	 */
	public function __construct( $page = 'general' ) {
		if ( '' != trim( $page ) ) {
			$this->page = $page;
		}
	}

	/**
	 * Adds a settings section to the object.
	 *
	 * @since 5.0.0
	 *
	 * @param string $title     User readable title for the settings section.
	 * @param string $view_path Path to the settings section's view.
	 * @param array  $view_data Any data that needs to be passed to the view.
	 *
	 * @return void
	 */
	public function add_section( $title, $view_path, $view_data = array() ) {
		if ( ( '' != trim( $title ) ) && ( file_exists( $view_path ) ) ) {
			$this->section[ 'title' ] = $title;
			$this->section[ 'id' ]    = WordPress_Plugin_Framework::make_slug( $title );

			$this->section[ 'view_path' ]            = $view_path;
			$this->section[ 'view_data' ]            = $view_data;
			$this->section[ 'view_data' ][ 'title' ] = $title;

			add_action( 'admin_init', array( $this, 'register_section' ) );
		}
	}

	/**
	 * Registers the settings section with WordPress.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function register_section() {
		add_settings_section( $this->section[ 'id' ], $this->section[ 'title' ], array( $this, 'display_section' ), $this->page );
	}

	/**
	 * Displays the settings section output.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function display_section() {
		echo View::render( $this->section[ 'view_path' ], $this->section[ 'view_data' ] );
	}

	/**
	 * Adds a settings field to the object.
	 *
	 * @since 5.0.0
	 *
	 * @param       $title
	 * @param       $view_path
	 * @param array $view_data
	 * @param array $args
	 *
	 * @return void
	 */
	public function add_field( $title, $view_path, $view_data = array(), $args = array() ) {
		if ( ( '' != trim( $title ) ) && ( file_exists( $view_path ) ) ) {
			/*
			$field = array(
				'title'     => $title,
				'id'        => WordPress_Plugin_Framework::make_slug( $title ),
				'view_path' => $view_path,
				'view_data' => $view_data,
				'args'      => $args
			);

			$field[ 'view_data' ][ 'title' ] = $title;

			$this->fields[ ] = $field;

			add_action( 'admin_init', array( $this, 'register_field' ) );
			*/

			$page    = $this->page;
			$section = $this->section[ 'id' ];

			add_action( 'admin_init', function () use ( $title, $view_path, $view_data, $args, $page, $section ) {
				$id = WordPress_Plugin_Framework::make_slug( $title );

				add_settings_field( $id, $title, function () use ( $view_path, $view_data ) {
					View::render( $view_path, $view_data );
				}, $page, $section, $args );

				register_setting( $page, $id );
			} );
		}
	}
}