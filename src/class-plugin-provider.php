<?php
/**
 * Plugin_Provider class.
 *
 * @package soter
 */

namespace Soter;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Defines the plugin provider class.
 */
class Plugin_Provider implements ServiceProviderInterface {
	/**
	 * Provider-specific boot logic.
	 *
	 * @param  Container $container Plugin container instance.
	 *
	 * @return void
	 */
	public function boot( Container $container ) {
		add_action(
			'soter_run_check',
			[ $container->proxy( 'check_site_job' ), 'run' ]
		);

		if ( ! $this->doing_cron() && ! is_admin() ) {
			return;
		}

		add_action( 'init', [ $container['upgrader'], 'perform_upgrade' ] );
	}

	/**
	 * Provider-specific activation logic.
	 *
	 * @param  Container $container Plugin container instance.
	 *
	 * @return void
	 */
	public function activate( Container $container ) {
		if ( false === wp_next_scheduled( 'soter_run_check' ) ) {
			wp_schedule_event( time(), 'twicedaily', 'soter_run_check' );
		}
	}

	/**
	 * Provider-specific deactivation logic.
	 *
	 * @param  Container $container Plugin container instance.
	 *
	 * @return void
	 */
	public function deactivate( Container $container ) {
		wp_clear_scheduled_hook( 'soter_run_check' );
	}

	/**
	 * Provider-specific registration logic.
	 *
	 * @param  Container $container Plugin container instance.
	 *
	 * @return void
	 */
	public function register( Container $container ) {
		$container['check_site_job'] = function( Container $c ) {
			return new Check_Site( $c['core.checker'], $c['options.manager'] );
		};

		$container['upgrader'] = function( Container $c ) {
			return new Upgrader( $c['options.manager'] );
		};

		$container['user-agent'] = function( Container $c ) {
			return sprintf(
				'%s (%s) | %s | v%s | %s',
				get_bloginfo( 'name' ),
				get_home_url(),
				$c['name'],
				$c['version'],
				$c['url']
			);
		};
	}

	/**
	 * Check whether the current request is a cron request.
	 *
	 * @return boolean
	 */
	protected function doing_cron() {
		return defined( 'DOING_CRON' ) && DOING_CRON;
	}
}
