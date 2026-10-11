<?php
/**
 * Plugin Name:       taboToggle: Hide Admin Bar Toggle
 * Description:       Adds a toggle in the admin bar to temporarily hide or show it. 
 * Version:           2.6
 * Author:            tabomango
 * Author URI:        https://tabomango.com
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * License:           GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) exit;

final class FS_Hide_Admin_Bar_Toggle {
	const LS_KEY       = 'fs_hat_adminbar_hidden';
	const VER          = '2.6';
	const PARENT_SLUG  = 'tabomango';
	const SETTINGS_SLUG = 'tabotoggle';
	const SUPPORT_URL  = 'https://tabomango.com/support';
	const PLUGINS_URL  = 'https://tabomango.com/plugins/';

	public static function init(): void {
		add_action( 'admin_bar_menu', [ __CLASS__, 'add_toggle_node' ], 9999 );

		// Early head output to prevent flash
		add_action( 'wp_head', [ __CLASS__, 'print_critical' ], 1 );
		add_action( 'admin_head', [ __CLASS__, 'print_critical' ], 1 );

		// Enqueued assets for behavior + full styling
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ], 9999 );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ], 9999 );

		// Shared tabomango admin menu.
		add_action( 'admin_menu', [ __CLASS__, 'register_parent_menu' ], 9 );
		add_action( 'admin_menu', [ __CLASS__, 'register_settings_submenu' ], 10 );
		add_action( 'admin_menu', [ __CLASS__, 'register_shared_links' ], 99 );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_menu_assets' ] );
	}

	/**
	 * Create the top-level "tabomango" menu only if another tabomango plugin has not already.
	 */
	public static function register_parent_menu(): void {
		if ( ! empty( $GLOBALS['admin_page_hooks'][ self::PARENT_SLUG ] ) ) return;

		add_menu_page(
			'tabomango',
			'tabomango',
			'manage_options',
			self::PARENT_SLUG,
			[ __CLASS__, 'render_parent_page' ],
			'dashicons-superhero',
			80
		);
	}

	public static function register_settings_submenu(): void {
		add_submenu_page(
			self::PARENT_SLUG,
			'taboToggle',
			'taboToggle',
			'manage_options',
			self::SETTINGS_SLUG,
			[ __CLASS__, 'render_settings_page' ]
		);
	}

	/**
	 * Add Get Support and More Plugins (in that order, last) if no other tabomango plugin added them.
	 */
	public static function register_shared_links(): void {
		global $submenu;

		if ( ! self::submenu_has( self::SUPPORT_URL ) ) {
			$submenu[ self::PARENT_SLUG ][] = [ 'Get Support', 'manage_options', self::SUPPORT_URL ]; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}
		if ( ! self::submenu_has( self::PLUGINS_URL ) ) {
			$submenu[ self::PARENT_SLUG ][] = [ 'More Plugins', 'manage_options', self::PLUGINS_URL ]; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}

		// Drop the auto-generated duplicate "tabomango" first item so the first real plugin is the landing link.
		remove_submenu_page( self::PARENT_SLUG, self::PARENT_SLUG );
	}

	private static function submenu_has( string $slug ): bool {
		global $submenu;
		if ( empty( $submenu[ self::PARENT_SLUG ] ) ) return false;
		foreach ( $submenu[ self::PARENT_SLUG ] as $item ) {
			if ( isset( $item[2] ) && $item[2] === $slug ) return true;
		}
		return false;
	}

	/**
	 * Open tabomango.com menu links in a new tab.
	 */
	public static function enqueue_menu_assets(): void {
		wp_register_script( 'fs-hat-menu', false, [], self::VER, true );
		wp_enqueue_script( 'fs-hat-menu' );
		wp_add_inline_script(
			'fs-hat-menu',
			"document.querySelectorAll('#adminmenu a[href^=\"https://tabomango.com/\"]').forEach(function(a){a.target='_blank';a.rel='noopener';});"
		);
	}

	public static function render_parent_page(): void {
		echo '<div class="wrap"><h1>tabomango</h1></div>';
	}

	public static function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) return;
		$img = plugins_url( 'assets/images/thanks.png', __FILE__ );

		echo '<div class="wrap">';
		echo '<h1>taboToggle</h1>';
		echo '<h2>' . esc_html__( 'How to Use taboToggle', 'hide-admin-bar-toggle' ) . '</h2>';
		echo '<ol>';
		echo '<li>' . wp_kses(
			__( 'Look at the top right of the admin bar for the button labeled <strong>Hide Bar</strong>.', 'hide-admin-bar-toggle' ),
			[ 'strong' => [] ]
		) . '</li>';
		echo '<li>' . wp_kses(
			__( 'Click <strong>Hide Bar</strong>. The admin bar slides out of view and the page shifts up to use the space.', 'hide-admin-bar-toggle' ),
			[ 'strong' => [] ]
		) . '</li>';
		echo '<li>' . wp_kses(
			__( 'To bring it back, click the small <strong>green dot</strong> that appears in the top right corner of the screen.', 'hide-admin-bar-toggle' ),
			[ 'strong' => [] ]
		) . '</li>';
		echo '</ol>';
		echo '<p>' . esc_html__( 'Your choice is saved in your browser, so the admin bar stays hidden or visible as you move between pages. It only affects you, not other users or site visitors.', 'hide-admin-bar-toggle' ) . '</p>';

		echo '<a href="' . esc_url( self::PLUGINS_URL ) . '" target="_blank" rel="noopener">';
		echo '<img src="' . esc_url( $img ) . '" alt="Thanks for using taboToggle. See more plugins from tabomango." style="max-width:100%;height:auto;margin-top:12px;">';
		echo '</a>';
		echo '</div>';
	}

	public static function add_toggle_node( WP_Admin_Bar $wp_admin_bar ): void {
		if ( ! is_user_logged_in() || ! is_admin_bar_showing() ) return;

		$wp_admin_bar->add_node([
			'id'     => 'fs-hat-toggle',
			'parent' => 'top-secondary',
			'title'  => '<span class="fs-hat-toggle-text" aria-hidden="true">Hide Bar</span><span class="screen-reader-text">Toggle admin bar visibility</span>',
			'href'   => '#',
			'meta'   => [
				'class' => 'fs-hat-toggle-node',
				'title' => 'Hide/Show Admin Bar',
			],
		]);
	}

	public static function enqueue_assets(): void {
		if ( ! is_user_logged_in() || ! is_admin_bar_showing() ) return;

		wp_register_style( 'fs-hat', false, [], self::VER );
		wp_register_script( 'fs-hat', false, [], self::VER, true );
		wp_enqueue_style( 'fs-hat' );
		wp_enqueue_script( 'fs-hat' );
		wp_add_inline_style( 'fs-hat', self::css() );
		wp_add_inline_script( 'fs-hat', self::js() );
	}

	public static function print_critical(): void {
		if ( ! is_user_logged_in() || ! is_admin_bar_showing() ) return;

		// Minimal CSS to hide immediately if class is applied.
		$critical_css =
			"html.fs-hat-adminbar-hidden{margin-top:0!important;}" .
			"html.fs-hat-adminbar-hidden body{margin-top:0!important;padding-top:0!important;}" .
			"html.fs-hat-adminbar-hidden #wpadminbar{transform:translateY(-110%);}" .
			"#wpadminbar{will-change:transform;}";

		$ls_key = esc_js( self::LS_KEY );

		// Early JS to apply the HTML class before first paint.
		$critical_js = "(function(){try{if(window.localStorage&&localStorage.getItem('{$ls_key}')==='1'){document.documentElement.classList.add('fs-hat-adminbar-hidden');}}catch(e){}})();";

		// Output with escaping and safe tags.
		echo wp_kses(
			'<style id="fs-hat-critical">' . $critical_css . '</style>',
			[ 'style' => [ 'id' => true ] ]
		);

		// Allow only a script tag with an id attribute. JS is built from controlled strings.
		echo wp_kses(
			'<script id="fs-hat-critical-js">' . $critical_js . '</script>',
			[ 'script' => [ 'id' => true ] ]
		);
	}

	private static function css(): string {
		$css  = '';
		$css .= '#wpadminbar.fs-hat-hidden,html.fs-hat-adminbar-hidden #wpadminbar{transform:translateY(-110%);transition:transform 220ms ease;}';
		$css .= '#wpadminbar{will-change:transform;}';
		$css .= 'html.fs-hat-adminbar-hidden{margin-top:0!important;}';
		$css .= 'html.fs-hat-adminbar-hidden body{margin-top:0!important;padding-top:0!important;}';
		$css .= '#wpadminbar .fs-hat-toggle-node>.ab-item{cursor:pointer;}';

		// WordPress hides most custom admin bar items on small screens. Force ours to show.
		$css .= '@media screen and (max-width:782px){';
		$css .= '#wpadminbar #wp-admin-bar-fs-hat-toggle{display:block!important;}';
		$css .= '#wpadminbar #wp-admin-bar-fs-hat-toggle>.ab-item{display:block!important;height:46px;line-height:46px;padding:0 12px;font-size:14px;}';
		$css .= '#wpadminbar #wp-admin-bar-fs-hat-toggle .fs-hat-toggle-text{display:inline!important;}';
		$css .= '}';

		$css .= '#fs-hat-floating-toggle{position:fixed;top:8px;right:10px;z-index:999999;display:none;border:1px solid rgba(0,0,0,.2);border-radius:6px;padding:8px 10px;background:#111;color:#fff;cursor:pointer;font:13px/1.2 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;box-shadow:0 6px 18px rgba(0,0,0,.22);}';
		$css .= '#fs-hat-floating-toggle:focus{outline:2px solid #fff;outline-offset:2px;}';
		$css .= 'html.fs-hat-adminbar-hidden #fs-hat-floating-toggle{display:inline-flex;align-items:center;gap:8px;}';
		$css .= '#fs-hat-floating-toggle .fs-hat-dot{width:8px;height:8px;border-radius:50%;background:#29dd94;display:inline-block;}';

		return $css;
	}

	private static function js(): string {
		$ls_key = esc_js( self::LS_KEY );

		$js  = "(function(){'use strict';";
		$js .= "var LS_KEY='{$ls_key}';";
		$js .= "var bar=document.getElementById('wpadminbar');";

		$js .= "function getHidden(){try{return window.localStorage.getItem(LS_KEY)==='1';}catch(e){return false;}}";
		$js .= "function setHidden(v){try{window.localStorage.setItem(LS_KEY,v?'1':'0');}catch(e){}}";

		$js .= "function ensureFloatingButton(){";
		$js .= "var existing=document.getElementById('fs-hat-floating-toggle');if(existing)return existing;";
		$js .= "var btn=document.createElement('button');btn.type='button';btn.id='fs-hat-floating-toggle';btn.setAttribute('aria-label','Show admin bar');";
		$js .= "btn.title='Show admin bar';";
		$js .= "btn.innerHTML='<span class=\"fs-hat-dot\" aria-hidden=\"true\"></span>';";
		$js .= "btn.addEventListener('click',function(){applyHidden(false);});document.body.appendChild(btn);return btn;}";
		
		$js .= "function applyHidden(hidden){";
		$js .= "var html=document.documentElement;var floating=ensureFloatingButton();if(!bar)bar=document.getElementById('wpadminbar');";
		$js .= "if(hidden){html.classList.add('fs-hat-adminbar-hidden');if(bar)bar.classList.add('fs-hat-hidden');floating.style.display='inline-flex';setHidden(true);}else{html.classList.remove('fs-hat-adminbar-hidden');if(bar)bar.classList.remove('fs-hat-hidden');floating.style.display='none';setHidden(false);}";
		$js .= "var nodeText=document.querySelector('#wpadminbar #wp-admin-bar-fs-hat-toggle .fs-hat-toggle-text');if(nodeText)nodeText.textContent=hidden?'Show Bar':'Hide Bar';";
		$js .= "}";

		$js .= "function bindAdminBarToggle(){";
		$js .= "var link=document.querySelector('#wpadminbar #wp-admin-bar-fs-hat-toggle > .ab-item');if(!link)return;";
		$js .= "if(link.dataset.fsHatBound==='1')return;link.dataset.fsHatBound='1';";
		$js .= "link.addEventListener('click',function(e){e.preventDefault();applyHidden(!getHidden());});";
		$js .= "}";

		$js .= "document.addEventListener('DOMContentLoaded',function(){ensureFloatingButton();bindAdminBarToggle();applyHidden(getHidden());});";
		$js .= "window.addEventListener('load',function(){bindAdminBarToggle();});";

		$js .= "})();";

		return $js;
	}
}

FS_Hide_Admin_Bar_Toggle::init();
