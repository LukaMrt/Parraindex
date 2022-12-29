<?php

namespace unit\application\contact\executor;

use App\application\contact\executor\AddPersonContactExecutor;
use App\application\contact\executor\AddSponsorContactExecutor;
use App\application\contact\executor\BugContactExecutor;
use App\application\contact\executor\ChockingContentContactExecutor;
use App\application\contact\executor\ContactExecutors;
use App\application\contact\executor\OtherContactExecutor;
use App\application\contact\executor\RemovePersonContactExecutor;
use App\application\contact\executor\RemoveSponsorContactExecutor;
use App\application\contact\executor\UpdatePersonContactExecutor;
use App\application\contact\executor\UpdateSponsorContactExecutor;
use PHPUnit\Framework\TestCase;

class ContactExecutorsTest extends TestCase {

	private ContactExecutors $contactExecutors;

	private AddPersonContactExecutor $addPersonContactExecutor;
	private RemovePersonContactExecutor $removePersonContactExecutor;
	private UpdatePersonContactExecutor $updatePersonContactExecutor;
	private AddSponsorContactExecutor $addSponsorContactExecutor;
	private RemoveSponsorContactExecutor $removeSponsorContactExecutor;
	private UpdateSponsorContactExecutor $updateSponsorContactExecutor;
	private BugContactExecutor $bugContactExecutor;
	private ChockingContentContactExecutor $chockingContentContactExecutor;
	private OtherContactExecutor $otherContactExecutor;

	public function setUp(): void {

		$this->addPersonContactExecutor = $this->createMock(AddPersonContactExecutor::class);
		$this->removePersonContactExecutor = $this->createMock(RemovePersonContactExecutor::class);
		$this->updatePersonContactExecutor = $this->createMock(UpdatePersonContactExecutor::class);
		$this->addSponsorContactExecutor = $this->createMock(AddSponsorContactExecutor::class);
		$this->removeSponsorContactExecutor = $this->createMock(RemoveSponsorContactExecutor::class);
		$this->updateSponsorContactExecutor = $this->createMock(UpdateSponsorContactExecutor::class);
		$this->bugContactExecutor = $this->createMock(BugContactExecutor::class);
		$this->chockingContentContactExecutor = $this->createMock(ChockingContentContactExecutor::class);
		$this->otherContactExecutor = $this->createMock(OtherContactExecutor::class);

		$this->contactExecutors = new ContactExecutors(
			$this->addPersonContactExecutor,
			$this->updatePersonContactExecutor,
			$this->removePersonContactExecutor,
			$this->addSponsorContactExecutor,
			$this->updateSponsorContactExecutor,
			$this->chockingContentContactExecutor,
			$this->bugContactExecutor,
			$this->removeSponsorContactExecutor,
			$this->otherContactExecutor
		);
	}

	public function testGetexecutorsbyidReturnsTheExecutorMatching(): void {

		$this->addPersonContactExecutor->method('getId')->willReturn(0);
		$this->removePersonContactExecutor->method('getId')->willReturn(1);
		$this->updatePersonContactExecutor->method('getId')->willReturn(2);
		$this->addSponsorContactExecutor->method('getId')->willReturn(3);
		$this->updateSponsorContactExecutor->method('getId')->willReturn(4);
		$this->chockingContentContactExecutor->method('getId')->willReturn(5);
		$this->bugContactExecutor->method('getId')->willReturn(6);
		$this->removeSponsorContactExecutor->method('getId')->willReturn(7);
		$this->otherContactExecutor->method('getId')->willReturn(8);

		$executors = $this->contactExecutors->getExecutorsById(0);

		$this->assertCount(1, $executors);
		$this->assertContains($this->addPersonContactExecutor, $executors);
	}

}