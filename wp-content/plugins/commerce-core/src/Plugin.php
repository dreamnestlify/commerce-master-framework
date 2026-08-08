<?php
/**
 * Main Plugin class — lifecycle, module bootstrapping.
 *
 * @package CommerceMaster\Core
 */

declare(strict_types=1);

namespace CommerceMaster\Core;

use CommerceMaster\Core\Module\ModuleRegistry;
use CommerceMaster\Core\Module\SettingsModule;
use CommerceMaster\Core\Module\SecurityModule;
use CommerceMaster\Core\Module\WishlistModule;
use CommerceMaster\Core\Module\RecentlyViewedModule;
use CommerceMaster\Core\Module\PaymentGatewayModule;

class Plugin {

	private ?ModuleRegistry $registry = null;

	/**
	 * Plugin activation: set defaults, flush rewrites.
	 */
	public function activate(): void {
		// Set default options on first activation.
		$this->get_registry()->activate();

		// Flush rewrite rules.
		flush_rewrite_rules();
	}

	/**
	 * Plugin deactivation: flush rewrites, keep data.
	 */
	public function deactivate(): void {
		flush_rewrite_rules();
	}

	/**
	 * Bootstrap the plugin on plugins_loaded.
	 */
	public function boot(): void {
		// Load text domain.
		load_plugin_textdomain(
			'commerce-core',
			false,
			dirname( COMMERCE_CORE_BASENAME ) . '/languages'
		);

		$registry = $this->get_registry();
		$registry->boot();

		// Hook into REST API init.
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
	}

	/**
	 * Register REST API routes.
	 */
	public function register_rest_routes(): void {
		$controller = new Rest\SettingsController();
		$controller->register_routes();
	}

	/**
	 * Register WP-CLI commands.
	 */
	public function register_cli_commands(): void {
		if ( ! class_exists( '\WP_CLI' ) ) {
			return;
		}

		\WP_CLI::add_command( 'commerce-core', \CommerceMaster\Core\Cli\CoreCommand::class );
	}

	/**
	 * Get or create the module registry.
	 */
	private function get_registry(): ModuleRegistry {
		if ( null === $this->registry ) {
			$this->registry = new ModuleRegistry();
		$this->registry->register( new SettingsModule() );
		$this->registry->register( new SecurityModule() );
		$this->registry->register( new WishlistModule() );
		$this->registry->register( new RecentlyViewedModule() );
		$this->registry->register( new PaymentGatewayModule() );
		}

		return $this->registry;
	}
}
