<?php
/**
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 *
 * @author Björn Schießle <bjoern@schiessle.org>
 * @author Julius Härtl <jus@bitgrid.net>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 *
 */
namespace OC\Encryption;

use OC\Files\Filesystem;
use OC\Files\View;
use OC\Files\SetupManager;
use Psr\Log\LoggerInterface;

class HookManager {
	private static ?Update $updater = null;
	private static bool $shouldTearDown = false;

	public static function postShared($params): void {
		self::getUpdate()->postShared($params);
		self::tearDown();
	}
	public static function postUnshared($params): void {
		self::getUpdate()->postUnshared($params);
		self::tearDown();
	}

	public static function postRename($params): void {
		self::getUpdate()->postRename($params);
		self::tearDown();
	}

	public static function postRestore($params): void {
		self::getUpdate()->postRestore($params);
		self::tearDown();
	}

	private static function tearDown(): void {
		if (!self::$shouldTearDown) {
			return;
		}

		$setupManager = \OC::$server->get(SetupManager::class);
		$setupManager->tearDownFS(); // TODO ideally we should only tear down the user fs and not the root
		self::$shouldTearDown = false;
	}

	private static function getUpdate(): Update {
		if (is_null(self::$updater)) {
			$user = \OC::$server->getUserSession()->getUser();

			$uid = '';
			if ($user) {
				$uid = $user->getUID();
			}

			$setupManager = \OC::$server->get(SetupManager::class);
			if (!$setupManager->isSetupComplete($user)) {
				self::$shouldTearDown = true;
				$setupManager->setupForUser($user);
			}

			self::$updater = new Update(
				new View(),
				new Util(
					new View(),
					\OC::$server->getUserManager(),
					\OC::$server->getGroupManager(),
					\OC::$server->getConfig()),
				Filesystem::getMountManager(),
				\OC::$server->getEncryptionManager(),
				\OC::$server->getEncryptionFilesHelper(),
				\OC::$server->get(LoggerInterface::class),
				$uid
			);
		}

		return self::$updater;
	}
}
