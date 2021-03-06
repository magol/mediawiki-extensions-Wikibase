<?php

namespace Wikibase\Repo\EditEntity;

use Liuggio\StatsdClient\Factory\StatsdDataFactoryInterface;
use User;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\EntityRedirect;
use Wikibase\Lib\Store\EntityStore;

/**
 * EntityStore that collects stats for edit save times.
 * @license GPL-2.0-or-later
 */
class StatsdSaveTimeRecordingEntityStore implements EntityStore {

	private $entityStore;
	private $stats;
	private $timingPrefix;

	/**
	 * @param EntityStore $entityStore
	 * @param StatsdDataFactoryInterface $stats
	 * @param string $timingPrefix Resulting metric will be: $timingPrefix.saveEntity.<entitytype>
	 */
	public function __construct(
		EntityStore $entityStore,
		StatsdDataFactoryInterface $stats,
		$timingPrefix
	) {
		$this->entityStore = $entityStore;
		$this->stats = $stats;
		$this->timingPrefix = $timingPrefix;
	}

	public function assignFreshId( EntityDocument $entity ) {
		return $this->entityStore->assignFreshId( $entity );
	}

	public function saveEntity(
		EntityDocument $entity,
		$summary,
		User $user,
		$flags = 0,
		$baseRevId = false
	) {
		$attemptSaveSaveStart = microtime( true );
		$result = $this->entityStore->saveEntity( $entity, $summary, $user, $flags, $baseRevId );
		$attemptSaveSaveEnd = microtime( true );

		$this->stats->timing(
			"{$this->timingPrefix}.saveEntity.{$entity->getType()}",
			( $attemptSaveSaveEnd - $attemptSaveSaveStart ) * 1000
		);

		return $result;
	}

	public function saveRedirect(
		EntityRedirect $redirect,
		$summary,
		User $user,
		$flags = 0,
		$baseRevId = false
	) {
		return $this->entityStore->saveRedirect( $redirect, $summary, $user, $flags, $baseRevId );
	}

	public function deleteEntity( EntityId $entityId, $reason, User $user ) {
		return $this->entityStore->deleteEntity( $entityId, $reason, $user );
	}

	public function userWasLastToEdit( User $user, EntityId $id, $lastRevId ) {
		return $this->entityStore->userWasLastToEdit( $user, $id, $lastRevId );
	}

	public function updateWatchlist( User $user, EntityId $id, $watch ) {
		$this->entityStore->updateWatchlist( $user, $id, $watch );
	}

	public function isWatching( User $user, EntityId $id ) {
		return $this->entityStore->isWatching( $user, $id );
	}

	public function canCreateWithCustomId( EntityId $id ) {
		return $this->entityStore->canCreateWithCustomId( $id );
	}

}
