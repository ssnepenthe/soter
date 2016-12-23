<?php
/**
 * WPVulnDB client.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\WPVulnDB;

use InvalidArgumentException;
use SSNepenthe\Soter\Interfaces\HTTP;
use SSNepenthe\Soter\Interfaces\Cache;

/**
 * The actual WPVulnDB client implementation.
 */
class Client {
	/**
	 * Cache provider.
	 *
	 * @var CacheInterface
	 */
	protected $cache;

	/**
	 * Http client.
	 *
	 * @var HttpInterface
	 */
	protected $http;

	/**
	 * Constructor.
	 *
	 * @param HTTP  $http  Http client.
	 * @param Cache $cache Cache provider.
	 */
	public function __construct( HTTP $http, Cache $cache ) {
		$this->http = $http;
		$this->cache = $cache;
	}

	/**
	 * Make a request to the plugins endpoint.
	 *
	 * @param  string $slug Plugin slug.
	 *
	 * @return SSNepenthe\Soter\WPVulnDB\Response
	 */
	public function plugins( $slug ) {
		return $this->get_and_cache( sprintf( 'plugins/%s', $slug ), $slug );
	}

	/**
	 * Make a request to the themes endpoint.
	 *
	 * @param  string $slug Theme slug.
	 *
	 * @return SSNepenthe\Soter\WPVulnDB\Response
	 */
	public function themes( $slug ) {
		return $this->get_and_cache( sprintf( 'themes/%s', $slug ), $slug );
	}

	/**
	 * Make a request to the WordPresses endpoint.
	 *
	 * @param  string $version WordPress version.
	 *
	 * @return SSNepenthe\Soter\WPVulnDB\Response
	 */
	public function wordpresses( $version ) {
		$slug = str_replace( '.', '', $version );

		return $this->get_and_cache(
			sprintf( 'wordpresses/%s', $slug ),
			$version
		);
	}

	/**
	 * Retrieve response from cache if it exists otherwise make a GET request.
	 *
	 * @param  string $endpoint      Request endpoint.
	 * @param  string $root_property The theme/plugin slug or WordPress version.
	 *
	 * @return SSNepenthe\Soter\WPVulnDB\Response
	 *
	 * @throws InvalidArgumentException When endpoint is not a string.
	 * @throws InvalidArgumentException When root_property is not a string.
	 */
	protected function get_and_cache( $endpoint, $root_property ) {
		if ( ! is_string( $endpoint ) ) {
			throw new InvalidArgumentException( sprintf(
				'The endpoint parameter is required to be string, was: %s',
				gettype( $endpoint )
			) );
		}

		if ( ! is_string( $root_property ) ) {
			throw new InvalidArgumentException( sprintf(
				'The root_property parameter is required to be string, was: %s',
				gettype( $root_property )
			) );
		}

		if ( $this->cache->contains( $endpoint ) ) {
			$response = $this->cache->fetch( $endpoint );

			// Older versions had status, headers and body. We don't need headers.
			if ( 3 === count( $response ) ) {
				$response = [ $response[0], $response[2] ];
			}

			list( $status, $body ) = $response;

			return new Response( $status, $body, $root_property );
		}

		$response = $this->http->get( $endpoint );

		// @todo Filterable cache lifetime?
		$this->cache->save( $endpoint, $response, HOUR_IN_SECONDS );

		list( $status, $body ) = $response;

		return new Response( $status, $body, $root_property );
	}
}