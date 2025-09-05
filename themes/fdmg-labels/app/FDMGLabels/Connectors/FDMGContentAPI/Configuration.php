<?php
namespace FDMGLabels\Connectors\FDMGContentAPI;

use Clarkson\Theme\ACFPages\FDMGContentAPI;
use FDMGContentAPIClient\Configuration as FDMGContentAPIClientConfiguration;

class Configuration {

	/**
	 * Domains are provided by FDMG
	 */
	public const DOMAIN_PROD = 'api.prod.fdmg.org';
	public const DOMAIN_ACC  = 'acc-api.ota.fdmg.org';
	public const DOMAIN_DEV  = 'dev-api.ota.fdmg.org';

	/**
	 * Hook names are provided by FDMG
	 */
	public const HOOK_NAMES = array(
		'energeia'     => array(
			'article' => 'ENG',
		),
		'pensioen_pro' => array(
			'article' => 'PPR',
		),
		'backup_fd' => array(
			'article' => 'HFD',
		),
		'backup_bnr' => array(
			'article' => 'BNR',
		),
	);

	public function get_configuration(): FDMGContentAPIClientConfiguration {
		$configuration = FDMGContentAPIClientConfiguration::getDefaultConfiguration();
		$configuration->setHost(
			$configuration->getHostFromSettings(
				0, array(
					'domain' => $this->get_domain(),
				)
			)
		);
		$configuration->setApiKey( 'x-api-key', $this->get_api_key() );
		return $configuration;
	}

	public function get_article_hook_name(): string {
		return $this->get_hooks()['article'] ?? '';
	}

	/**
	 * Get hooks for the current site connection
	 * @return array<string,string>
	 */
	protected function get_hooks(): array {
		$site_connection = (string) ( $this->get_acf_settings()[ FDMGContentAPI::OPTION_SITE_CONNECTION ] ?? '' );
		return self::HOOK_NAMES[ $site_connection ] ?? array();
	}

	public function is_enabled(): bool {
		return $this->get_acf_settings()[ FDMGContentAPI::OPTION_ENABLED ] ?? false;
	}

	protected function get_domain(): string {
		return $this->get_acf_settings()[ FDMGContentAPI::OPTION_DOMAIN ] ?? '';
	}

	protected function get_api_key(): string {
		return $this->get_acf_settings()[ FDMGContentAPI::OPTION_API_KEY ] ?? '';
	}

	/**
	 * Get ACF options for the FDMG Content API
	 *
	 * @return array<string,mixed>
	 */
	public function get_acf_settings(): array {
		$settings = get_field( FDMGContentAPI::OPTION_GROUP, 'options' ) ?: array();
		return is_array( $settings ) ? $settings : array();
	}
}
