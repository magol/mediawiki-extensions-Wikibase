<?php

/**
 * File defining the settings for the Wikibase extension.
 * More info can be found at https://www.mediawiki.org/wiki/Extension:Wikibase#Settings
 *
 * NOTICE:
 * Changing one of these settings can be done by assigning to $egWBSettings,
 * AFTER the inclusion of the extension itself.
 *
 * @since 0.1
 *
 * @file Wikibase.settings.php
 * @ingroup Wikibase
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class WBSettings {

	/**
	 * Build version of the settings.
	 * @since 0.1
	 * @var boolean
	 */
	protected $settings = false;

	/**
	 * Returns an array with all settings after making sure they are
	 * initialized (ie set settings have been merged with the defaults).
	 * setting name (string) => setting value (mixed)
	 *
	 * @since 0.1
	 *
	 * @return array
	 */
	public function getSettings() {
		$this->buildSettings();
		return $this->settings;
	}

	/**
	 * Returns an instance of the settings class.
	 *
	 * @since 0.1
	 *
	 * @return WBCSettings
	 */
	public static function singleton() {
		static $instance = false;

		if ( $instance === false ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Does a lazy rebuild of the settings.
	 *
	 * @since 0.1
	 */
	public function rebuildSettings() {
		$this->settings = false;
	}

	/**
	 * Builds the settings if needed.
	 * This includes merging the set settings over the default ones.
	 *
	 * @since 0.1
	 */
	protected function buildSettings() {
		if ( $this->settings === false ) {
			$this->settings = array_merge(
				self::getDefaultSettings(),
				$GLOBALS['egWBSettings']
			);
		}
	}

	/**
	 * Gets the value of the specified setting.
	 *
	 * @since 0.1
	 *
	 * @param string $settingName
	 *
	 * @throws MWException
	 * @return mixed
	 */
	public function getSetting( $settingName, $default=null ) {
		$this->buildSettings();

		if ( !array_key_exists( $settingName, $this->settings ) ) {
			if ($default === null) {
				throw new MWException( 'Attempt to get non-existing setting "' . $settingName . '"' );
			}
			return $default;
		}

		return $this->settings[$settingName];
	}

	/**
	 * Gets the value of the specified setting.
	 * Shortcut to calling getSetting on the singleton instance of the settings class.
	 *
	 * @since 0.1
	 *
	 * @param string $settingName
	 *
	 * @return mixed
	 */
	public static function get( $settingName, $default=null ) {
		return self::singleton()->getSetting( $settingName, $default );
	}

	/**
	 * Returns the default values for the settings.
	 * setting name (string) => setting value (mixed)
	 *
	 * @since 0.1
	 *
	 * @return array
	 */
	protected static function getDefaultSettings() {
		return array(
			// alternative: application/vnd.php.serialized
			'serializationFormat' => CONTENT_FORMAT_JSON,

			// Disables token and post requirements in the API to
			// facilitate testing, do not turn on in production!
			'apiInDebug' => false,

			'apiDebugWithTokens' => false,

			// The site link sites we can link to (not necessarily clients!)
			// They are grouped, each group has a 'sites' element which is an array holding the identifiers.
			// It also can hold defaultSiteUrlPath and defaultSiteFilePath overriding the global default.
			// Each element in the 'sites' array contains the identifier for the site (which should be unique!)
			// pointing to the url of the site, or an array with the url (element: site) and optionally
			// the filepath and urlpath, using these words as keys.
			// TODO: add stuff to hold message keys for short and long names
			'siteIdentifiers' => array(
				'wikipedia' => array(
					'sites' => array(
						'en' => 'https://en.wikipedia.org',
						'de' => 'https://de.wikipedia.org',
						'nl' => 'https://nl.wikipedia.org',
						'fi' => 'https://fi.wikipedia.org',
						'da' => 'https://da.wikipedia.org',
						'no' => 'https://no.wikipedia.org',
						'nn' => 'https://nn.wikipedia.org',
						'sv' => 'https://sv.wikipedia.org',
						'he' => 'https://he.wikipedia.org',
						'ja' => 'https://ja.wikipedia.org',
						//'foobar' => array( 'url' => 'https://www.foo.bar/', 'filepath' => '/folder/', 'urlpath' => '/wikiname/$1' ),
					),
					'defaultSiteType' => 'mediawiki',
				),
				'stuff' => array(
					'sites' => array(
						'stuff-en' => 'https://en.wikipedia.org',
						'stuff-de' => 'https://de.wikipedia.org',
					),
					'defaultSiteUrlPath' => '/stuffwiki/$1',
					'defaultSiteFilePath' => '/somepath/$1',
				),
			),

			'defaultSiteUrlPath' => '/wiki/$1',
			'defaultSiteFilePath' => '/w/$1',
			'defaultSiteType' => 'unknown',
		);
	}

}
