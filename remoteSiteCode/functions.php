<?php
/**
 * Twenty Twenty functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage Twenty_Twenty
 * @since Twenty Twenty 1.0
 */

/**
 * Table of Contents:
 * Theme Support
 * Required Files
 * Register Styles
 * Register Scripts
 * Register Menus
 * Custom Logo
 * WP Body Open
 * Register Sidebars
 * Enqueue Block Editor Assets
 * Enqueue Classic Editor Styles
 * Block Editor Settings
 */

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function twentytwenty_theme_support() {

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	// Custom background color.
	add_theme_support(
		'custom-background',
		array(
			'default-color' => 'f5efe0',
		)
	);

	// Set content-width.
	global $content_width;
	if ( ! isset( $content_width ) ) {
		$content_width = 580;
	}

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support( 'post-thumbnails' );

	// Set post thumbnail size.
	set_post_thumbnail_size( 1200, 9999 );

	// Add custom image size used in Cover Template.
	add_image_size( 'twentytwenty-fullscreen', 1980, 9999 );

	// Custom logo.
	$logo_width  = 120;
	$logo_height = 90;

	// If the retina setting is active, double the recommended width and height.
	if ( get_theme_mod( 'retina_logo', false ) ) {
		$logo_width  = floor( $logo_width * 2 );
		$logo_height = floor( $logo_height * 2 );
	}

	add_theme_support(
		'custom-logo',
		array(
			'height'      => $logo_height,
			'width'       => $logo_width,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'script',
			'style',
		)
	);

	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on Twenty Twenty, use a find and replace
	 * to change 'twentytwenty' to the name of your theme in all the template files.
	 */
	load_theme_textdomain( 'twentytwenty' );

	// Add support for full and wide align images.
	add_theme_support( 'align-wide' );

	// Add support for responsive embeds.
	add_theme_support( 'responsive-embeds' );

	/*
	 * Adds starter content to highlight the theme on fresh sites.
	 * This is done conditionally to avoid loading the starter content on every
	 * page load, as it is a one-off operation only needed once in the customizer.
	 */
	if ( is_customize_preview() ) {
		require get_template_directory() . '/inc/starter-content.php';
		add_theme_support( 'starter-content', twentytwenty_get_starter_content() );
	}

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	/*
	 * Adds `async` and `defer` support for scripts registered or enqueued
	 * by the theme.
	 */
	$loader = new TwentyTwenty_Script_Loader();
	add_filter( 'script_loader_tag', array( $loader, 'filter_script_loader_tag' ), 10, 2 );

}

add_action( 'after_setup_theme', 'twentytwenty_theme_support' );

/**
 * REQUIRED FILES
 * Include required files.
 */
require get_template_directory() . '/inc/template-tags.php';

// Handle SVG icons.
require get_template_directory() . '/classes/class-twentytwenty-svg-icons.php';
require get_template_directory() . '/inc/svg-icons.php';

// Handle Customizer settings.
require get_template_directory() . '/classes/class-twentytwenty-customize.php';

// Require Separator Control class.
require get_template_directory() . '/classes/class-twentytwenty-separator-control.php';

// Custom comment walker.
require get_template_directory() . '/classes/class-twentytwenty-walker-comment.php';

// Custom page walker.
require get_template_directory() . '/classes/class-twentytwenty-walker-page.php';

// Custom script loader class.
require get_template_directory() . '/classes/class-twentytwenty-script-loader.php';

// Non-latin language handling.
require get_template_directory() . '/classes/class-twentytwenty-non-latin-languages.php';

// Custom CSS.
require get_template_directory() . '/inc/custom-css.php';

/**
 * Register and Enqueue Styles.
 */
function twentytwenty_register_styles() {

	$theme_version = wp_get_theme()->get( 'Version' );

	wp_enqueue_style( 'twentytwenty-style', get_stylesheet_uri(), array(), $theme_version );
	wp_style_add_data( 'twentytwenty-style', 'rtl', 'replace' );

	// Add output of Customizer settings as inline style.
	wp_add_inline_style( 'twentytwenty-style', twentytwenty_get_customizer_css( 'front-end' ) );

	// Add print CSS.
	wp_enqueue_style( 'twentytwenty-print-style', get_template_directory_uri() . '/print.css', null, $theme_version, 'print' );

}

add_action( 'wp_enqueue_scripts', 'twentytwenty_register_styles' );

/**
 * Register and Enqueue Scripts.
 */
function twentytwenty_register_scripts() {

	$theme_version = wp_get_theme()->get( 'Version' );

	if ( ( ! is_admin() ) && is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	wp_enqueue_script( 'twentytwenty-js', get_template_directory_uri() . '/assets/js/index.js', array(), $theme_version, false );
	wp_script_add_data( 'twentytwenty-js', 'async', true );

}

add_action( 'wp_enqueue_scripts', 'twentytwenty_register_scripts' );

/**
 * Fix skip link focus in IE11.
 *
 * This does not enqueue the script because it is tiny and because it is only for IE11,
 * thus it does not warrant having an entire dedicated blocking script being loaded.
 *
 * @link https://git.io/vWdr2
 */
function twentytwenty_skip_link_focus_fix() {
	// The following is minified via `terser --compress --mangle -- assets/js/skip-link-focus-fix.js`.
	?>
	<script>
	/(trident|msie)/i.test(navigator.userAgent)&&document.getElementById&&window.addEventListener&&window.addEventListener("hashchange",function(){var t,e=location.hash.substring(1);/^[A-z0-9_-]+$/.test(e)&&(t=document.getElementById(e))&&(/^(?:a|select|input|button|textarea)$/i.test(t.tagName)||(t.tabIndex=-1),t.focus())},!1);
	</script>
	<?php
}
add_action( 'wp_print_footer_scripts', 'twentytwenty_skip_link_focus_fix' );

/** Enqueue non-latin language styles
 *
 * @since Twenty Twenty 1.0
 *
 * @return void
 */
function twentytwenty_non_latin_languages() {
	$custom_css = TwentyTwenty_Non_Latin_Languages::get_non_latin_css( 'front-end' );

	if ( $custom_css ) {
		wp_add_inline_style( 'twentytwenty-style', $custom_css );
	}
}

add_action( 'wp_enqueue_scripts', 'twentytwenty_non_latin_languages' );

/**
 * Register navigation menus uses wp_nav_menu in five places.
 */
function twentytwenty_menus() {

	$locations = array(
		'primary'  => __( 'Desktop Horizontal Menu', 'twentytwenty' ),
		'expanded' => __( 'Desktop Expanded Menu', 'twentytwenty' ),
		'mobile'   => __( 'Mobile Menu', 'twentytwenty' ),
		'footer'   => __( 'Footer Menu', 'twentytwenty' ),
		'social'   => __( 'Social Menu', 'twentytwenty' ),
	);

	register_nav_menus( $locations );
}

add_action( 'init', 'twentytwenty_menus' );

/**
 * Get the information about the logo.
 *
 * @param string $html The HTML output from get_custom_logo (core function).
 *
 * @return string $html
 */
function twentytwenty_get_custom_logo( $html ) {

	$logo_id = get_theme_mod( 'custom_logo' );

	if ( ! $logo_id ) {
		return $html;
	}

	$logo = wp_get_attachment_image_src( $logo_id, 'full' );

	if ( $logo ) {
		// For clarity.
		$logo_width  = esc_attr( $logo[1] );
		$logo_height = esc_attr( $logo[2] );

		// If the retina logo setting is active, reduce the width/height by half.
		if ( get_theme_mod( 'retina_logo', false ) ) {
			$logo_width  = floor( $logo_width / 2 );
			$logo_height = floor( $logo_height / 2 );

			$search = array(
				'/width=\"\d+\"/iU',
				'/height=\"\d+\"/iU',
			);

			$replace = array(
				"width=\"{$logo_width}\"",
				"height=\"{$logo_height}\"",
			);

			// Add a style attribute with the height, or append the height to the style attribute if the style attribute already exists.
			if ( strpos( $html, ' style=' ) === false ) {
				$search[]  = '/(src=)/';
				$replace[] = "style=\"height: {$logo_height}px;\" src=";
			} else {
				$search[]  = '/(style="[^"]*)/';
				$replace[] = "$1 height: {$logo_height}px;";
			}

			$html = preg_replace( $search, $replace, $html );

		}
	}

	return $html;

}

add_filter( 'get_custom_logo', 'twentytwenty_get_custom_logo' );

if ( ! function_exists( 'wp_body_open' ) ) {

	/**
	 * Shim for wp_body_open, ensuring backward compatibility with versions of WordPress older than 5.2.
	 */
	function wp_body_open() {
		do_action( 'wp_body_open' );
	}
}

/**
 * Include a skip to content link at the top of the page so that users can bypass the menu.
 */
function twentytwenty_skip_link() {
	echo '<a class="skip-link screen-reader-text" href="#site-content">' . __( 'Skip to the content', 'twentytwenty' ) . '</a>';
}

add_action( 'wp_body_open', 'twentytwenty_skip_link', 5 );

/**
 * Register widget areas.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function twentytwenty_sidebar_registration() {

	// Arguments used in all register_sidebar() calls.
	$shared_args = array(
		'before_title'  => '<h2 class="widget-title subheading heading-size-3">',
		'after_title'   => '</h2>',
		'before_widget' => '<div class="widget %2$s"><div class="widget-content">',
		'after_widget'  => '</div></div>',
	);

	// Footer #1.
	register_sidebar(
		array_merge(
			$shared_args,
			array(
				'name'        => __( 'Footer #1', 'twentytwenty' ),
				'id'          => 'sidebar-1',
				'description' => __( 'Widgets in this area will be displayed in the first column in the footer.', 'twentytwenty' ),
			)
		)
	);

	// Footer #2.
	register_sidebar(
		array_merge(
			$shared_args,
			array(
				'name'        => __( 'Footer #2', 'twentytwenty' ),
				'id'          => 'sidebar-2',
				'description' => __( 'Widgets in this area will be displayed in the second column in the footer.', 'twentytwenty' ),
			)
		)
	);

}

add_action( 'widgets_init', 'twentytwenty_sidebar_registration' );

/**
 * Enqueue supplemental block editor styles.
 */
function twentytwenty_block_editor_styles() {

	$css_dependencies = array();

	// Enqueue the editor styles.
	wp_enqueue_style( 'twentytwenty-block-editor-styles', get_theme_file_uri( '/assets/css/editor-style-block.css' ), $css_dependencies, wp_get_theme()->get( 'Version' ), 'all' );
	wp_style_add_data( 'twentytwenty-block-editor-styles', 'rtl', 'replace' );

	// Add inline style from the Customizer.
	wp_add_inline_style( 'twentytwenty-block-editor-styles', twentytwenty_get_customizer_css( 'block-editor' ) );

	// Add inline style for non-latin fonts.
	wp_add_inline_style( 'twentytwenty-block-editor-styles', TwentyTwenty_Non_Latin_Languages::get_non_latin_css( 'block-editor' ) );

	// Enqueue the editor script.
	wp_enqueue_script( 'twentytwenty-block-editor-script', get_theme_file_uri( '/assets/js/editor-script-block.js' ), array( 'wp-blocks', 'wp-dom' ), wp_get_theme()->get( 'Version' ), true );
}

add_action( 'enqueue_block_editor_assets', 'twentytwenty_block_editor_styles', 1, 1 );

/**
 * Enqueue classic editor styles.
 */
function twentytwenty_classic_editor_styles() {

	$classic_editor_styles = array(
		'/assets/css/editor-style-classic.css',
	);

	add_editor_style( $classic_editor_styles );

}

add_action( 'init', 'twentytwenty_classic_editor_styles' );

/**
 * Output Customizer settings in the classic editor.
 * Adds styles to the head of the TinyMCE iframe. Kudos to @Otto42 for the original solution.
 *
 * @param array $mce_init TinyMCE styles.
 *
 * @return array $mce_init TinyMCE styles.
 */
function twentytwenty_add_classic_editor_customizer_styles( $mce_init ) {

	$styles = twentytwenty_get_customizer_css( 'classic-editor' );

	if ( ! isset( $mce_init['content_style'] ) ) {
		$mce_init['content_style'] = $styles . ' ';
	} else {
		$mce_init['content_style'] .= ' ' . $styles . ' ';
	}

	return $mce_init;

}

add_filter( 'tiny_mce_before_init', 'twentytwenty_add_classic_editor_customizer_styles' );

/**
 * Output non-latin font styles in the classic editor.
 * Adds styles to the head of the TinyMCE iframe. Kudos to @Otto42 for the original solution.
 *
 * @param array $mce_init TinyMCE styles.
 *
 * @return array $mce_init TinyMCE styles.
 */
function twentytwenty_add_classic_editor_non_latin_styles( $mce_init ) {

	$styles = TwentyTwenty_Non_Latin_Languages::get_non_latin_css( 'classic-editor' );

	// Return if there are no styles to add.
	if ( ! $styles ) {
		return $mce_init;
	}

	if ( ! isset( $mce_init['content_style'] ) ) {
		$mce_init['content_style'] = $styles . ' ';
	} else {
		$mce_init['content_style'] .= ' ' . $styles . ' ';
	}

	return $mce_init;

}

add_filter( 'tiny_mce_before_init', 'twentytwenty_add_classic_editor_non_latin_styles' );

/**
 * Block Editor Settings.
 * Add custom colors and font sizes to the block editor.
 */
function twentytwenty_block_editor_settings() {

	// Block Editor Palette.
	$editor_color_palette = array(
		array(
			'name'  => __( 'Accent Color', 'twentytwenty' ),
			'slug'  => 'accent',
			'color' => twentytwenty_get_color_for_area( 'content', 'accent' ),
		),
		array(
			'name'  => __( 'Primary', 'twentytwenty' ),
			'slug'  => 'primary',
			'color' => twentytwenty_get_color_for_area( 'content', 'text' ),
		),
		array(
			'name'  => __( 'Secondary', 'twentytwenty' ),
			'slug'  => 'secondary',
			'color' => twentytwenty_get_color_for_area( 'content', 'secondary' ),
		),
		array(
			'name'  => __( 'Subtle Background', 'twentytwenty' ),
			'slug'  => 'subtle-background',
			'color' => twentytwenty_get_color_for_area( 'content', 'borders' ),
		),
	);

	// Add the background option.
	$background_color = get_theme_mod( 'background_color' );
	if ( ! $background_color ) {
		$background_color_arr = get_theme_support( 'custom-background' );
		$background_color     = $background_color_arr[0]['default-color'];
	}
	$editor_color_palette[] = array(
		'name'  => __( 'Background Color', 'twentytwenty' ),
		'slug'  => 'background',
		'color' => '#' . $background_color,
	);

	// If we have accent colors, add them to the block editor palette.
	if ( $editor_color_palette ) {
		add_theme_support( 'editor-color-palette', $editor_color_palette );
	}

	// Block Editor Font Sizes.
	add_theme_support(
		'editor-font-sizes',
		array(
			array(
				'name'      => _x( 'Small', 'Name of the small font size in the block editor', 'twentytwenty' ),
				'shortName' => _x( 'S', 'Short name of the small font size in the block editor.', 'twentytwenty' ),
				'size'      => 18,
				'slug'      => 'small',
			),
			array(
				'name'      => _x( 'Regular', 'Name of the regular font size in the block editor', 'twentytwenty' ),
				'shortName' => _x( 'M', 'Short name of the regular font size in the block editor.', 'twentytwenty' ),
				'size'      => 21,
				'slug'      => 'normal',
			),
			array(
				'name'      => _x( 'Large', 'Name of the large font size in the block editor', 'twentytwenty' ),
				'shortName' => _x( 'L', 'Short name of the large font size in the block editor.', 'twentytwenty' ),
				'size'      => 26.25,
				'slug'      => 'large',
			),
			array(
				'name'      => _x( 'Larger', 'Name of the larger font size in the block editor', 'twentytwenty' ),
				'shortName' => _x( 'XL', 'Short name of the larger font size in the block editor.', 'twentytwenty' ),
				'size'      => 32,
				'slug'      => 'larger',
			),
		)
	);

	// If we have a dark background color then add support for dark editor style.
	// We can determine if the background color is dark by checking if the text-color is white.
	if ( '#ffffff' === strtolower( twentytwenty_get_color_for_area( 'content', 'text' ) ) ) {
		add_theme_support( 'dark-editor-style' );
	}

}

add_action( 'after_setup_theme', 'twentytwenty_block_editor_settings' );

/**
 * Overwrite default more tag with styling and screen reader markup.
 *
 * @param string $html The default output HTML for the more tag.
 *
 * @return string $html
 */
function twentytwenty_read_more_tag( $html ) {
	return preg_replace( '/<a(.*)>(.*)<\/a>/iU', sprintf( '<div class="read-more-button-wrap"><a$1><span class="faux-button">$2</span> <span class="screen-reader-text">"%1$s"</span></a></div>', get_the_title( get_the_ID() ) ), $html );
}

add_filter( 'the_content_more_link', 'twentytwenty_read_more_tag' );

/**
 * Enqueues scripts for customizer controls & settings.
 *
 * @since Twenty Twenty 1.0
 *
 * @return void
 */
function twentytwenty_customize_controls_enqueue_scripts() {
	$theme_version = wp_get_theme()->get( 'Version' );

	// Add main customizer js file.
	wp_enqueue_script( 'twentytwenty-customize', get_template_directory_uri() . '/assets/js/customize.js', array( 'jquery' ), $theme_version, false );

	// Add script for color calculations.
	wp_enqueue_script( 'twentytwenty-color-calculations', get_template_directory_uri() . '/assets/js/color-calculations.js', array( 'wp-color-picker' ), $theme_version, false );

	// Add script for controls.
	wp_enqueue_script( 'twentytwenty-customize-controls', get_template_directory_uri() . '/assets/js/customize-controls.js', array( 'twentytwenty-color-calculations', 'customize-controls', 'underscore', 'jquery' ), $theme_version, false );
	wp_localize_script( 'twentytwenty-customize-controls', 'twentyTwentyBgColors', twentytwenty_get_customizer_color_vars() );
}

add_action( 'customize_controls_enqueue_scripts', 'twentytwenty_customize_controls_enqueue_scripts' );

/**
 * Enqueue scripts for the customizer preview.
 *
 * @since Twenty Twenty 1.0
 *
 * @return void
 */
function twentytwenty_customize_preview_init() {
	$theme_version = wp_get_theme()->get( 'Version' );

	wp_enqueue_script( 'twentytwenty-customize-preview', get_theme_file_uri( '/assets/js/customize-preview.js' ), array( 'customize-preview', 'customize-selective-refresh', 'jquery' ), $theme_version, true );
	wp_localize_script( 'twentytwenty-customize-preview', 'twentyTwentyBgColors', twentytwenty_get_customizer_color_vars() );
	wp_localize_script( 'twentytwenty-customize-preview', 'twentyTwentyPreviewEls', twentytwenty_get_elements_array() );

	wp_add_inline_script(
		'twentytwenty-customize-preview',
		sprintf(
			'wp.customize.selectiveRefresh.partialConstructor[ %1$s ].prototype.attrs = %2$s;',
			wp_json_encode( 'cover_opacity' ),
			wp_json_encode( twentytwenty_customize_opacity_range() )
		)
	);
}

add_action( 'customize_preview_init', 'twentytwenty_customize_preview_init' );

/**
 * Get accessible color for an area.
 *
 * @since Twenty Twenty 1.0
 *
 * @param string $area The area we want to get the colors for.
 * @param string $context Can be 'text' or 'accent'.
 * @return string Returns a HEX color.
 */
function twentytwenty_get_color_for_area( $area = 'content', $context = 'text' ) {

	// Get the value from the theme-mod.
	$settings = get_theme_mod(
		'accent_accessible_colors',
		array(
			'content'       => array(
				'text'      => '#000000',
				'accent'    => '#cd2653',
				'secondary' => '#6d6d6d',
				'borders'   => '#dcd7ca',
			),
			'header-footer' => array(
				'text'      => '#000000',
				'accent'    => '#cd2653',
				'secondary' => '#6d6d6d',
				'borders'   => '#dcd7ca',
			),
		)
	);

	// If we have a value return it.
	if ( isset( $settings[ $area ] ) && isset( $settings[ $area ][ $context ] ) ) {
		return $settings[ $area ][ $context ];
	}

	// Return false if the option doesn't exist.
	return false;
}

/**
 * Returns an array of variables for the customizer preview.
 *
 * @since Twenty Twenty 1.0
 *
 * @return array
 */
function twentytwenty_get_customizer_color_vars() {
	$colors = array(
		'content'       => array(
			'setting' => 'background_color',
		),
		'header-footer' => array(
			'setting' => 'header_footer_background_color',
		),
	);
	return $colors;
}

/**
 * Get an array of elements.
 *
 * @since Twenty Twenty 1.0
 *
 * @return array
 */
function twentytwenty_get_elements_array() {

	// The array is formatted like this:
	// [key-in-saved-setting][sub-key-in-setting][css-property] = [elements].
	$elements = array(
		'content'       => array(
			'accent'     => array(
				'color'            => array( '.color-accent', '.color-accent-hover:hover', '.color-accent-hover:focus', ':root .has-accent-color', '.has-drop-cap:not(:focus):first-letter', '.wp-block-button.is-style-outline', 'a' ),
				'border-color'     => array( 'blockquote', '.border-color-accent', '.border-color-accent-hover:hover', '.border-color-accent-hover:focus' ),
				'background-color' => array( 'button:not(.toggle)', '.button', '.faux-button', '.wp-block-button__link', '.wp-block-file .wp-block-file__button', 'input[type="button"]', 'input[type="reset"]', 'input[type="submit"]', '.bg-accent', '.bg-accent-hover:hover', '.bg-accent-hover:focus', ':root .has-accent-background-color', '.comment-reply-link' ),
				'fill'             => array( '.fill-children-accent', '.fill-children-accent *' ),
			),
			'background' => array(
				'color'            => array( ':root .has-background-color', 'button', '.button', '.faux-button', '.wp-block-button__link', '.wp-block-file__button', 'input[type="button"]', 'input[type="reset"]', 'input[type="submit"]', '.wp-block-button', '.comment-reply-link', '.has-background.has-primary-background-color:not(.has-text-color)', '.has-background.has-primary-background-color *:not(.has-text-color)', '.has-background.has-accent-background-color:not(.has-text-color)', '.has-background.has-accent-background-color *:not(.has-text-color)' ),
				'background-color' => array( ':root .has-background-background-color' ),
			),
			'text'       => array(
				'color'            => array( 'body', '.entry-title a', ':root .has-primary-color' ),
				'background-color' => array( ':root .has-primary-background-color' ),
			),
			'secondary'  => array(
				'color'            => array( 'cite', 'figcaption', '.wp-caption-text', '.post-meta', '.entry-content .wp-block-archives li', '.entry-content .wp-block-categories li', '.entry-content .wp-block-latest-posts li', '.wp-block-latest-comments__comment-date', '.wp-block-latest-posts__post-date', '.wp-block-embed figcaption', '.wp-block-image figcaption', '.wp-block-pullquote cite', '.comment-metadata', '.comment-respond .comment-notes', '.comment-respond .logged-in-as', '.pagination .dots', '.entry-content hr:not(.has-background)', 'hr.styled-separator', ':root .has-secondary-color' ),
				'background-color' => array( ':root .has-secondary-background-color' ),
			),
			'borders'    => array(
				'border-color'        => array( 'pre', 'fieldset', 'input', 'textarea', 'table', 'table *', 'hr' ),
				'background-color'    => array( 'caption', 'code', 'code', 'kbd', 'samp', '.wp-block-table.is-style-stripes tbody tr:nth-child(odd)', ':root .has-subtle-background-background-color' ),
				'border-bottom-color' => array( '.wp-block-table.is-style-stripes' ),
				'border-top-color'    => array( '.wp-block-latest-posts.is-grid li' ),
				'color'               => array( ':root .has-subtle-background-color' ),
			),
		),
		'header-footer' => array(
			'accent'     => array(
				'color'            => array( 'body:not(.overlay-header) .primary-menu > li > a', 'body:not(.overlay-header) .primary-menu > li > .icon', '.modal-menu a', '.footer-menu a, .footer-widgets a', '#site-footer .wp-block-button.is-style-outline', '.wp-block-pullquote:before', '.singular:not(.overlay-header) .entry-header a', '.archive-header a', '.header-footer-group .color-accent', '.header-footer-group .color-accent-hover:hover' ),
				'background-color' => array( '.social-icons a', '#site-footer button:not(.toggle)', '#site-footer .button', '#site-footer .faux-button', '#site-footer .wp-block-button__link', '#site-footer .wp-block-file__button', '#site-footer input[type="button"]', '#site-footer input[type="reset"]', '#site-footer input[type="submit"]' ),
			),
			'background' => array(
				'color'            => array( '.social-icons a', 'body:not(.overlay-header) .primary-menu ul', '.header-footer-group button', '.header-footer-group .button', '.header-footer-group .faux-button', '.header-footer-group .wp-block-button:not(.is-style-outline) .wp-block-button__link', '.header-footer-group .wp-block-file__button', '.header-footer-group input[type="button"]', '.header-footer-group input[type="reset"]', '.header-footer-group input[type="submit"]' ),
				'background-color' => array( '#site-header', '.footer-nav-widgets-wrapper', '#site-footer', '.menu-modal', '.menu-modal-inner', '.search-modal-inner', '.archive-header', '.singular .entry-header', '.singular .featured-media:before', '.wp-block-pullquote:before' ),
			),
			'text'       => array(
				'color'               => array( '.header-footer-group', 'body:not(.overlay-header) #site-header .toggle', '.menu-modal .toggle' ),
				'background-color'    => array( 'body:not(.overlay-header) .primary-menu ul' ),
				'border-bottom-color' => array( 'body:not(.overlay-header) .primary-menu > li > ul:after' ),
				'border-left-color'   => array( 'body:not(.overlay-header) .primary-menu ul ul:after' ),
			),
			'secondary'  => array(
				'color' => array( '.site-description', 'body:not(.overlay-header) .toggle-inner .toggle-text', '.widget .post-date', '.widget .rss-date', '.widget_archive li', '.widget_categories li', '.widget cite', '.widget_pages li', '.widget_meta li', '.widget_nav_menu li', '.powered-by-wordpress', '.to-the-top', '.singular .entry-header .post-meta', '.singular:not(.overlay-header) .entry-header .post-meta a' ),
			),
			'borders'    => array(
				'border-color'     => array( '.header-footer-group pre', '.header-footer-group fieldset', '.header-footer-group input', '.header-footer-group textarea', '.header-footer-group table', '.header-footer-group table *', '.footer-nav-widgets-wrapper', '#site-footer', '.menu-modal nav *', '.footer-widgets-outer-wrapper', '.footer-top' ),
				'background-color' => array( '.header-footer-group table caption', 'body:not(.overlay-header) .header-inner .toggle-wrapper::before' ),
			),
		),
	);

	/**
	* Filters Twenty Twenty theme elements
	*
	* @since Twenty Twenty 1.0
	*
	* @param array Array of elements
	*/
	return apply_filters( 'twentytwenty_get_elements_array', $elements );
}

//Woocommerce hook : This action is triggered to check whether product is purchaseable or not,if product is not purchaseable this function remove the add to card button and show the read more button is place of add to cart button.....
add_filter( 'woocommerce_is_purchasable', 'disable_repeat_purchase_by_email', 10, 2 );

//Function Definiation : disable_repeat_purchase_by_email
function disable_repeat_purchase_by_email( $product_is_purchasable, $product_details ) {
	$current_product_id = $product_details->get_id();//Get the current product id..... 
   	$current_user = wp_get_current_user();//get the current user details....
   	$userEmail = $current_user->user_email;
	$userId = $current_user->ID;
   	//Call the function "wc_customer_bought_product" is used to check whether a user (by email or id) has bought an item. If yes than return false and remove the add to cart button..... 
   	if (wc_customer_bought_product($userEmail, $userId, $current_product_id)) {
        $product_is_purchasable = false;
    }else{
    	$product_is_purchasable = true;
    }
    return $product_is_purchasable;
}

//Woocommerce hook : This action is triggered to show the message below read more button for login users.....
add_action( 'woocommerce_after_shop_loop_item', 'product_already_bought_loggedin_users', 30 );

//Function Definiation : product_already_bought_loggedin_users
function product_already_bought_loggedin_users() {
	global $product;//Define Variables.....
	//Check user is login or not if login then get the user details....
	if (is_user_logged_in()){
		$current_user = wp_get_current_user();	
	}
	//Check user details not empty...
	if(isset($current_user) && !empty($current_user)){
		$userEmail = $current_user->user_email;
		$userId = $current_user->ID;
		$product_id = $product->get_id();
		$userFirstName = $current_user->first_name;
		//Call the function "wc_customer_bought_product" is used to check whether a user (by email or id) has bought an item. If yes than display the message below read more button....
		if (wc_customer_bought_product( $userEmail,$userId,$product_id))
		{
		 	echo '<div class="user-bought">Hi ' . $userFirstName . ', You are not able to purchase this product. This product is already purchased by you!</div>';
		}
	}
}

//Woocommerce hook : This action is triggered to show the message on product summary page,when user is login.......
add_action( 'woocommerce_single_product_summary', 'purchase_disabled_message_summary_page', 31 );

//Function Definiation : purchase_disabled_message_summary_page
function purchase_disabled_message_summary_page() {
    global $product;//Define Variables.....
    $current_user = wp_get_current_user();//get the current user details....
   	$userEmail = $current_user->user_email;
	$userId = $current_user->ID;
	$product_id = $product->get_id();
    if ( wc_customer_bought_product($userEmail,$userId,$product_id))
    {
        echo '<div class="woocommerce"><div class="woocommerce-info wc-nonpurchasable-message">You are not able to purchase this product. This product is already purchased by you!</div></div>';
    }
}

//Woocommerce hook : This action is triggered when user login from checkout page to purchase the particular product. If product is already purchase then redirect to login cart page with error message...
add_action('woocommerce_checkout_process', 'wc_action_checkout_process' );

//Function Definiation : wc_action_checkout_process
function wc_action_checkout_process() {
    $user_id = get_current_user_id();//get the current user details....
    //Check current user id.....
	if( $user_id > 0 ) {
        $userVariation = $user_id;
    }
    else {
        if ( isset($_POST['billing_email']) && ! empty($_POST['billing_email']) ) {
            $email = sanitize_email( $_POST['billing_email'] );
            $user  = get_user_by( 'email', $email );
            if ( is_a($user, 'WP_User') ) {
            }
            else {
                $userVariation = $email;
            }
        }
    }
	foreach ( WC()->cart->get_cart() as $cart_item ) {
		//Call the function "wc_customer_bought_product" is used to check whether a user (by email or id) has bought an item. If yes than display the message below read more button....
        if ( itemsAlreadyBought( $userVariation,  $cart_item['product_id'] ) ) {
            wc_add_notice( sprintf( __('You have already purchased "%s" product before. So you are not able to purchase it again.'), $cart_item['data']->get_name() ), 'error' );
        }
    }
}

//Function Definiation : itemsAlreadyBought(Used to check whether the product is already purchase by login user or not......)
function itemsAlreadyBought( $userVariation = 0,  $productIds = 0 ) {
    global $wpdb;
    if ( is_numeric($userVariation) ) { 
        $itemMetaKey     = '_customer_user';
        $itemMetaValue   = $userVariation == 0 ? (int) get_current_user_id() : (int) $userVariation;
    } 
    else { 
        $itemMetaKey     = '_billing_email';
        $itemMetaValue   = sanitize_email( $userVariation );
    }
    $status    = array_map( 'esc_sql', wc_get_is_paid_statuses() );
    $productIds      = is_array( $productIds ) ? implode(',', $productIds) : $productIds;
	$item_meta_value  = $productIds !=  ( 0 || '' ) ? 'AND woim.meta_value IN ('.$productIds.')' : 'AND woim.meta_value != 0';
	$countNumberOfProducts = $wpdb->get_var( "
        SELECT COUNT(p.ID) FROM {$wpdb->prefix}posts AS p
        INNER JOIN {$wpdb->prefix}postmeta AS pm ON p.ID = pm.post_id
        INNER JOIN {$wpdb->prefix}woocommerce_order_items AS woi ON p.ID = woi.order_id
        INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS woim ON woi.order_item_id = woim.order_item_id
        WHERE p.post_status IN ( 'wc-" . implode( "','wc-", $status ) . "' )
        AND pm.meta_key = '$itemMetaKey'
        AND pm.meta_value = '$itemMetaValue'
        AND woim.meta_key IN ( '_product_id', '_variation_id' ) $item_meta_value 
    " );
	return $countNumberOfProducts > 0 ? true : false;
}

//Woocommerce hook : This action is triggered when a payment is done.
add_action('woocommerce_payment_complete', 'wooconnection_trigger_status_complete_hook');

//Function Definiation : wooconnection_trigger_status_complete_hook
function wooconnection_trigger_status_complete_hook($orderid){
	if(!empty($orderid)){
		$admin_auth_details = get_option('admin_authentication_details');
		$accessToken =  '';
		if(isset($admin_auth_details) && !empty($admin_auth_details)){
		  	if(!empty($admin_auth_details['access_token'])){
		  		$accessToken = $admin_auth_details['access_token'];		
		  	}
		}
	  	$accessToken =  '9DAwBtlZbAGGKaeQ5t9G5aWGjpoA';
	  	//Order Data by order id.....
	    $orderDetails = new WC_Order( $orderid );
	    //Get the order items from order then execute loop to create the order items array....
        $wcproductSku = '';
        if ( sizeof($products_items = $orderDetails->get_items()) > 0 ) {
            foreach($products_items as $item_id => $item)
            {
            	$product = wc_get_product($item['product_id']);//get the prouct details..
            	$wcproductSku = $product->get_sku();//get product sku....
            }
        }
        
        //Get the order associated email....
	    $orderEmail = $orderDetails->get_billing_email();
		
		//Check order email is valid or not(If not valid then stop the next process)...... 
    	if(isset($orderEmail) && !empty($orderEmail)){
	      if (!filter_var($orderEmail, FILTER_VALIDATE_EMAIL)) {
	      	return false;//if email is in valid format then stop the process...
	      }
	    }else{
	    	return false;//if email is empty then stop the process...
	    }
	    
	   	$contactId = addUpdateContact($orderEmail,$accessToken);//Call the function is used to check contact is exist or not if not exist then create new one, if exist then get the id of contact...
	   	$wcOrderData = $orderDetails->get_data();//Get the order related data like billing data....

	   	global $wpdb,$table_prefix;
	   	if($wcproductSku == 'pro_wc'){
        	$orderDetails->update_status( 'completed' );
	        $callname = 'wcpurchasepro';
	        if(!empty($contactId)){
	        	achieveTrigger($contactId,$wcOrderData,$accessToken,$callname);
	        }
	    }else{
        	$orderDetails->update_status( 'completed' );
        	$callname  = 'wcpurchase';
        	if(!empty($contactId)){
	        	achieveTrigger($contactId,$wcOrderData,$accessToken,$callname);
	        }
		}
	}
}

//Function Definiation : This function is add or update contact with same curl request by overriding the duplicate email.... 
function addUpdateContact($orderEmail,$accessToken){
    $applicationContactId = "";//Define Variables.....
    //Check email is exist or not if exist then hit the curl request to add or update the contact with email....
    if(isset($orderEmail) && !empty($orderEmail)){
        $curlContactJsonData ='{"duplicate_option": "Email","email_addresses":[{"email": "'.$orderEmail.'","field": "EMAIL1"}],"opt_in_reason": "Customer opted-in for wooconnection plugin purchase"}';
        $curlContactRequestUrl = 'https://api.infusionsoft.com/crm/rest/v1/contacts';
        $curlContactRequestInit = curl_init($curlContactRequestUrl);
        curl_setopt($curlContactRequestInit, CURLOPT_RETURNTRANSFER, true);
        $curlContactRequestHeader = array('Accept: application/json','Content-Type: application/json','Authorization: Bearer '. $accessToken);
        curl_setopt($curlContactRequestInit, CURLOPT_HTTPHEADER, $curlContactRequestHeader);
        curl_setopt($curlContactRequestInit, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($curlContactRequestInit, CURLOPT_POSTFIELDS, $curlContactJsonData);
        $curlContactResponse = curl_exec($curlContactRequestInit);
        $curlContactErr = curl_error($curlContactRequestInit);
        if($curlContactErr){
        }else{
          $curlContactSucessData = json_decode($curlContactResponse,true);
          if(!empty($curlContactSucessData['id'])){
            $applicationContactId = $curlContactSucessData['id'];
          }
          return $applicationContactId;
        }
        curl_close($curlContactRequestInit);
    }
    return $applicationContactId;
}



//Function Definiation : This function is add or update contact with same curl request by overriding the duplicate email.... 
function updateContactExtraInfo($contactId,$orderData,$accessToken){
    $informationArr = array();//Define Array.....
    if(!empty($contactId) && !empty($orderData) && !empty($accessToken)){
        $informationArr['field'] = "BILLING";
        if(isset($orderData['billing']['country']) && !empty($orderData['billing']['country'])){
            $orderCountryCode = countryCode($orderData['billing']['country']);
            $informationArr['country_code'] = $orderCountryCode;
        }
        if(isset($orderData['billing']['address_1']) && !empty($orderData['billing']['address_1'])){
            $informationArr['line1'] = trim($orderData['billing']['address_1']);
        }
        if(isset($orderData['billing']['address_2']) && !empty($orderData['billing']['address_2']))
        {
            $informationArr['line2'] = $orderData['billing']['address_2'];
        }
        if(isset($orderData['billing']['postcode']) && !empty($orderData['billing']['postcode'])){
            $informationArr['postal_code'] = $orderData['billing']['postcode'];
        }
        if(isset($orderData['billing']['city']) && !empty($orderData['billing']['city'])){
            $informationArr['locality'] = $orderData['billing']['city'];
        }
        if(isset($orderData['billing']['state']) && !empty($orderData['billing']['state'])){
            $orderStates = WC()->countries->get_states($orderData['billing']['country']);
            $orderState = !empty($orderStates[$orderData['billing']['state']]) ? $orderStates[$orderData['billing']['state']] : '';
            $informationArr['region'] = $orderState;
        }
        $orderCompanyId = '';
        if(isset($orderData['billing']['company']) && !empty($orderData['billing']['company'])){
            $orderCompany = stripslashes($orderData['billing']['company']);
            $orderCompanyId = getCompanyId($orderCompany,$accessToken);//Get the company id by company name...
        }
        $contactFirstName = '';
        if(isset($orderData['billing']['first_name']) && !empty($orderData['billing']['first_name'])){
            $contactFirstName = trim($orderData['billing']['first_name']);
        }
        $contactPhone1 = '';
        if(isset($orderData['billing']['phone']) && !empty($orderData['billing']['phone'])){
            $contactPhone1 = $orderData['billing']['phone'];
        }
        $addressArray = json_encode($informationArr);
        $curlUpdateJsonData = '{"addresses": ['.$addressArray.'],"company": {"id": '.$orderCompanyId.'},"phone_numbers": 
          [{"field": "PHONE1","number": "'.$contactPhone1.'"}],"given_name": "'.$contactFirstName.'"}';
        
        $curlUpdateRequesturl = 'https://api.infusionsoft.com/crm/rest/v1/contacts/'.$contactId;
        $curlUpdateRequestInit = curl_init($curlUpdateRequesturl);
        curl_setopt($curlUpdateRequestInit, CURLOPT_RETURNTRANSFER, true);
        $curlUpdateRequestHeader = array('Accept: application/json','Content-Type: application/json','Authorization: Bearer '. $accessToken);
        curl_setopt($curlUpdateRequestInit, CURLOPT_HTTPHEADER, $curlUpdateRequestHeader);
        curl_setopt($curlUpdateRequestInit, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($curlUpdateRequestInit, CURLOPT_POSTFIELDS, $curlUpdateJsonData);
        $curlUpdateResponse = curl_exec($curlUpdateRequestInit);
        $curlUpdateErr = curl_error($curlUpdateRequestInit);
        if($curlUpdateErr){
        }else{
          $curlUpdateData = json_decode($curlUpdateResponse,true);
        }
        curl_close($curlUpdateRequestInit);
    }
    return true;
}


//Function Definiation : This function is used to get the country code on the basis of code....
function countryCode($code){
  global $wpdb,$table_prefix;
  $table_name = 'wp_countries';
  $countryDetails = $wpdb->get_results("SELECT * FROM ".$table_name." WHERE code = '".$code."'");
  $countryCode = "";
  if(!empty($countryDetails[0]->countrycode)){
    $countryCode =$countryDetails[0]->countrycode;
  }
  return $countryCode;
}


//Function Definiation : This function is check or add company with company name then return the company id........
function getCompanyId($companyName,$accessToken){
    $appCompanyId = "";//Define Variables.....
    if(isset($companyName) && !empty($companyName)){
        $curlCompanyRequesturl = "https://api.infusionsoft.com/crm/rest/v1/companies";
        $curlPostParameters = array('company_name'=>$companyName);
        $curlQueryParameters = http_build_query($curlPostParameters);
        $curlCompanyRequestInit = curl_init();
        curl_setopt($curlCompanyRequestInit, CURLOPT_URL, $curlCompanyRequesturl."?".$curlQueryParameters);
        $curlCompanyRequestHeader = array('Accept: application/json','Content-Type: application/json','Authorization: Bearer '. $accessToken);
        curl_setopt($curlCompanyRequestInit, CURLOPT_HTTPHEADER, $curlCompanyRequestHeader);
        curl_setopt($curlCompanyRequestInit, CURLOPT_RETURNTRANSFER, true);
        $curlCompanyResponse = curl_exec($curlCompanyRequestInit);
        $curlCompanyErr = curl_error($curlCompanyRequestInit);
        if($curlCompanyErr){
        }else{
          $curlCompanyData = json_decode($curlCompanyResponse,true);
          if(!empty($curlCompanyData['companies'][0])){
              if(!empty($curlCompanyData['companies'][0]['id'])){
                  $appCompanyId = $curlCompanyData['companies'][0]['id'];
              } 
          }
          //If company already exist then return the company id...
          if(!empty($appCompanyId)){
              return $appCompanyId;
          }else{
          	//If company not already exist then add new company with company name...
            $appCompanyId = addCompanyToApp($companyName,$accessToken);
            return $appCompanyId;
          } 
        }
        curl_close($curlCompanyRequestInit);
    }
    return $appCompanyId;
}

//Function Definiation : This function is used to add company with company name by hit the curl request....
function addCompanyToApp($companyName,$accessToken){
    $company = "";//Define Variables.....
    if(isset($companyName) && !empty($companyName)){
        $curlCompanyAddUrl = "https://api.infusionsoft.com/crm/rest/v1/companies";
        $jsonArray = '{"company_name": "'.$companyName.'"}';
        $curlCompanyAddInit = curl_init($curlCompanyAddUrl);
        curl_setopt($curlCompanyAddInit, CURLOPT_RETURNTRANSFER, true);
        $curlCompanyAddHeader = array('Accept: application/json','Content-Type: application/json','Authorization: Bearer '. $accessToken);
        curl_setopt($curlCompanyAddInit, CURLOPT_HTTPHEADER, $curlCompanyAddHeader);
        curl_setopt($curlCompanyAddInit, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curlCompanyAddInit, CURLOPT_POSTFIELDS, $jsonArray);
        $curlCompanyAddResponse = curl_exec($curlCompanyAddInit);
        $curlCompanyAddErr = curl_error($curlCompanyAddInit);
        if($curlCompanyAddErr){
        }else{
          $curlCompanyAddData = json_decode($curlCompanyAddResponse,true);
          if(!empty($curlCompanyAddData)){
              if(!empty($curlCompanyAddData['id'])){
                $company = $curlCompanyAddData['id'];
                return $company;
              } 
          }
        }
        curl_close($curlCompanyAddInit);
    }
    return $company;
}


//This function is used to push the contact to api goal then send email with wooconnection plugin link.
function achieveTrigger($contactId,$orderDetails,$accessToken,$callname){
	//Check contact id is exist or not....
	if(isset($contactId) && !empty($contactId)) {
		//$wcOrderData = $orderDetails->get_data();//Get the order related data like billing data....
	    updateContactExtraInfo($contactId,$orderDetails,$accessToken);//After adding contact update the another information of contact....
	    //Hit the curl request to push a contact in campaign.....
		$curlUrl = 'https://api.infusionsoft.com/crm/rest/v1/campaigns/goals/wooconnection/'.$callname;
	    $curlJsonData ='{"contact_id":'.$contactId.'}';
	    $curlIntialize = curl_init($curlUrl);
	    curl_setopt($curlIntialize, CURLOPT_RETURNTRANSFER, true);
	  	$headerData = array('Accept: application/json','Content-Type: application/json','Authorization: Bearer '. $accessToken);
	  	curl_setopt($curlIntialize, CURLOPT_HTTPHEADER, $headerData);
	  	curl_setopt($curlIntialize, CURLOPT_CUSTOMREQUEST, "POST");
	  	curl_setopt($curlIntialize, CURLOPT_POSTFIELDS, $curlJsonData);
	  	$curlResponse = curl_exec($curlIntialize);
	  	$curlErr = curl_error($curlIntialize);
	  	if($curlErr){
	  	}else{
	        $returnData = json_decode($curlResponse,true);
	    }
	  	curl_close($curlIntialize);
	}
}

//Allowing adding only one wooconnection product item to cart and displaying an error message
add_filter( 'woocommerce_add_to_cart_validation', 'cart_items_validation', 10, 1 );
function cart_items_validation( $validate ) {
    if( ! WC()->cart->is_empty() ){
        wc_add_notice( __("You can add only one item to cart", "woocommerce" ), 'error' );
        $validate = false;
    }
    return $validate;
}

// Avoiding checkout when there is more than one item and displaying an error message
add_action( 'woocommerce_check_cart_items', 'cart_items_check' ); // Cart and Checkout
function cart_items_check() {
    if( sizeof( WC()->cart->get_cart() ) > 1 ){
        // Display an error message
        wc_add_notice( __("More than one items in cart is not allowed to checkout", "woocommece"), 'error' );
    }
}