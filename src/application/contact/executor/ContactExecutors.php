<?php

namespace App\application\contact\executor;

class ContactExecutors {

	private array $executors;

	public function __construct(AddPersonContactExecutor       $addPersonContactExecutor,
								UpdatePersonContactExecutor    $updatePersonContactExecutor,
								RemovePersonContactExecutor    $removePersonContactExecutor,
								AddSponsorContactExecutor      $addSponsorContactExecutor,
								UpdateSponsorContactExecutor   $updateSponsorContactExecutor,
								ChockingContentContactExecutor $chockingContentContactExecutor,
								BugContactExecutor             $bugContactExecutor,
								RemoveSponsorContactExecutor   $removeSponsorContactExecutor,
								OtherContactExecutor           $otherContactExecutor) {

		$this->executors = array($addPersonContactExecutor,
			$updatePersonContactExecutor,
			$removePersonContactExecutor,
			$addSponsorContactExecutor,
			$updateSponsorContactExecutor,
			$chockingContentContactExecutor,
			$bugContactExecutor,
			$removeSponsorContactExecutor,
			$otherContactExecutor);
	}

	public function getExecutorsById(int $id): array {
		return array_filter($this->executors, fn($executor) => $executor->getId() === $id);
	}

}