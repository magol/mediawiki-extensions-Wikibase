<?php

namespace Wikibase\Lib\Store;

use OutOfBoundsException;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Term\FingerprintProvider;

/**
 * @since 0.5
 *
 * @licence GNU GPL v2+
 * @author Katie Filbert < aude.wiki@gmail.com >
 */
class EntityRetrievingTermLookup implements TermLookup {

	/**
	 * @var EntityLookup
	 */
	private $entityLookup;

	/**
	 * @var Fingerprint[]
	 */
	private $fingerprints;

	/**
	 * @param EntityLookup $entityLookup
	 */
	public function __construct( EntityLookup $entityLookup ) {
		$this->entityLookup = $entityLookup;
	}

	/**
	 * Gets the label of an Entity with the specified EntityId and language code.
	 *
	 * @param EntityId $entityId
	 * @param string $languageCode
	 *
	 * @throws OutOfBoundsException
	 * @throws StorageException for entity not found
	 * @return string
	 */
	public function getLabel( EntityId $entityId, $languageCode ) {
		$labels = $this->getFingerprint( $entityId )->getLabels();
		return $labels->getByLanguage( $languageCode )->getText();
	}

	/**
	 * Gets all labels of an Entity with the specified EntityId.
	 *
	 * @param EntityId $entityId
	 *
	 * @throws StorageException for entity not found
	 * @return string[]
	 */
	public function getLabels( EntityId $entityId ) {
		return $this->getFingerprint( $entityId )->getLabels()->toTextArray();
	}

	/**
	 * Gets the description of an Entity with the specified EntityId and language code.
	 *
	 * @param EntityId $entityId
	 * @param string $languageCode
	 *
	 * @throws OutOfBoundsException
	 * @throws StorageException for entity not found
	 * @return string
	 */
	public function getDescription( EntityId $entityId, $languageCode ) {
		$descriptions = $this->getFingerprint( $entityId )->getDescriptions();
		return $descriptions->getByLanguage( $languageCode )->getText();
	}

	/**
	 * Gets all descriptions of an Entity with the specified EntityId.
	 *
	 * @param EntityId $entityId
	 *
	 * @throws StorageException for entity not found
	 * @return string[]
	 */
	public function getDescriptions( EntityId $entityId ) {
		return $this->getFingerprint( $entityId )->getDescriptions()->toTextArray();
	}

	/**
	 * @param EntityId $entityId
	 *
	 * @return Fingerprint
	 */
	private function getFingerprint( EntityId $entityId ) {
		$idSerialization = $entityId->getSerialization();

		if ( !isset( $this->fingerprints[$idSerialization] ) ) {
			$this->fingerprints[$idSerialization] = $this->fetchFingerprint( $entityId );
		}

		return $this->fingerprints[$idSerialization];
	}

	/**
	 * @param EntityId $entityId
	 *
	 * @throws StorageException
	 * @return Fingerprint
	 */
	private function fetchFingerprint( EntityId $entityId ) {
		try {
			$entity = $this->entityLookup->getEntity( $entityId );
		} catch ( UnresolvedRedirectException $ex )  {
			$entity = null;
		} catch ( StorageException $ex )  {
			$entity = null;
			wfLogWarning( 'Failed to load entity: '
				. $entityId->getSerialization() . ': '
				. $ex->getMessage() );
		}

		if ( $entity === null ) {
			// double redirect, deleted entity, etc
			throw new StorageException( "An Entity with the id $entityId could not be loaded" );
		}

		return $entity instanceof FingerprintProvider ? $entity->getFingerprint() : Fingerprint::newEmpty();
	}

}
