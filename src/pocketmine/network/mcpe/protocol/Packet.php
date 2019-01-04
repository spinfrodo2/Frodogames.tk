<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol;

use pocketmine\network\mcpe\handler\SessionHandler;
use pocketmine\network\mcpe\NetworkBinaryStream;

interface Packet{
	/**
	 * Returns the packet ID.
	 *
	 * @return int
	 */
	public function pid() : int;

	/**
	 * Returns a short human-readable name for this packet.
	 * @return string
	 */
	public function getName() : string;

	/**
	 * @return bool
	 */
	public function canBeSentBeforeLogin() : bool;

	/**
	 * Returns whether the packet may legally have unread bytes left in the buffer.
	 * @return bool
	 */
	public function mayHaveUnreadBytes() : bool;

	/**
	 * @param NetworkBinaryStream $in
	 *
	 * @throws \OutOfBoundsException
	 * @throws \UnexpectedValueException
	 */
	public function decode(NetworkBinaryStream $in) : void;

	/**
	 * @param NetworkBinaryStream $out
	 */
	public function encode(NetworkBinaryStream $out) : void;

	/**
	 * Performs handling for this packet. Usually you'll want an appropriately named method in the session handler for
	 * this.
	 *
	 * This method returns a bool to indicate whether the packet was handled or not. If the packet was unhandled, a
	 * debug message will be logged with a hexdump of the packet.
	 *
	 * Typically this method returns the return value of the handler in the supplied SessionHandler. See other packets
	 * for examples how to implement this.
	 *
	 * @param SessionHandler $handler
	 *
	 * @return bool true if the packet was handled successfully, false if not.
	 */
	public function handle(SessionHandler $handler) : bool;
}
