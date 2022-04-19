<?php

namespace OCP\User\Events;

use OCP\EventDispatcher\Event;

/**
 * @since 25.0.0
 */
class BeforeUserIdUnAssignedEvent extends Event {
	private string $name;

	/**
	 * @since 25.0.0
	 */
	public function __construct(string $name) {
		parent::__construct();
		$this->name = $name;
	}

	/**
	 * @since 25.0.0
	 */
	public function getName(): string {
		return $this->name;
	}
}
