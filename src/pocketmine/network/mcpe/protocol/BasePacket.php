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

#include <rules/DataPacket.h>

use pocketmine\network\BadPacketException;
use pocketmine\network\mcpe\handler\SessionHandler;
use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\utils\BinaryDataException;
use function get_class;

abstract class BasePacket implements Packet{

	public const NETWORK_ID = 0;

	/** @var int */
	public $senderSubId = 0;
	/** @var int */
	public $recipientSubId = 0;

	public function pid() : int{
		return $this::NETWORK_ID;
	}

	public function getName() : string{
		return (new \ReflectionClass($this))->getShortName();
	}

	public function canBeSentBeforeLogin() : bool{
		return false;
	}

	/**
	 * Returns whether the packet may legally have unread bytes left in the buffer.
	 * @return bool
	 */
	public function mayHaveUnreadBytes() : bool{
		return false;
	}

	/**
	 * @param NetworkBinaryStream $in
	 *
	 * @throws BadPacketException
	 */
	final public function decode(NetworkBinaryStream $in) : void{
		try{
			$this->decodeHeader($in);
			$this->decodePayload($in);
		}catch(BinaryDataException | BadPacketException $e){
			throw new BadPacketException($this->getName() . ": " . $e->getMessage(), 0, $e);
		}
	}

	/**
	 * @param NetworkBinaryStream $in
	 *
	 * @throws BinaryDataException
	 * @throws \UnexpectedValueException
	 */
	protected function decodeHeader(NetworkBinaryStream $in) : void{
		$pid = $in->getUnsignedVarInt();
		if($pid !== static::NETWORK_ID){
			//TODO: this means a logical error in the code, but how to prevent it from happening?
			throw new \UnexpectedValueException("Expected " . static::NETWORK_ID . " for packet ID, got $pid");
		}
	}

	/**
	 * Decodes the packet body, without the packet ID or other generic header fields.
	 *
	 * @param NetworkBinaryStream $in
	 *
	 * @throws BadPacketException
	 * @throws BinaryDataException
	 */
	abstract protected function decodePayload(NetworkBinaryStream $in) : void;

	final public function encode(NetworkBinaryStream $out) : void{
		$this->encodeHeader($out);
		$this->encodePayload($out);
	}

	protected function encodeHeader(NetworkBinaryStream $out) : void{
		$out->putUnsignedVarInt($this->pid());
	}

	/**
	 * Encodes the packet body, without the packet ID or other generic header fields.
	 *
	 * @param NetworkBinaryStream $out
	 */
	abstract protected function encodePayload(NetworkBinaryStream $out) : void;

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
	 * @throws BadPacketException if broken data was found in the packet
	 */
	abstract public function handle(SessionHandler $handler) : bool;

	public function __get($name){
		throw new \Error("Undefined property: " . get_class($this) . "::\$" . $name);
	}

	public function __set($name, $value){
		throw new \Error("Undefined property: " . get_class($this) . "::\$" . $name);
	}
}
