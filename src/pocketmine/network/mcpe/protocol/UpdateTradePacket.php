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


use pocketmine\network\mcpe\handler\SessionHandler;
use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\network\mcpe\protocol\types\WindowTypes;

class UpdateTradePacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::UPDATE_TRADE_PACKET;

	//TODO: find fields

	/** @var int */
	public $windowId;
	/** @var int */
	public $windowType = WindowTypes::TRADING; //Mojang hardcoded this -_-
	/** @var int */
	public $varint1;
	/** @var int */
	public $varint2;
	/** @var int */
	public $varint3;
	/** @var bool */
	public $isWilling;
	/** @var int */
	public $traderEid;
	/** @var int */
	public $playerEid;
	/** @var string */
	public $displayName;
	/** @var string */
	public $offers;

	protected function decodePayload(NetworkBinaryStream $in) : void{
		$this->windowId = $in->getByte();
		$this->windowType = $in->getByte();
		$this->varint1 = $in->getVarInt();
		$this->varint2 = $in->getVarInt();
		$this->varint3 = $in->getVarInt();
		$this->isWilling = $in->getBool();
		$this->traderEid = $in->getEntityUniqueId();
		$this->playerEid = $in->getEntityUniqueId();
		$this->displayName = $in->getString();
		$this->offers = $in->getRemaining();
	}

	protected function encodePayload(NetworkBinaryStream $out) : void{
		$out->putByte($this->windowId);
		$out->putByte($this->windowType);
		$out->putVarInt($this->varint1);
		$out->putVarInt($this->varint2);
		$out->putVarInt($this->varint3);
		$out->putBool($this->isWilling);
		$out->putEntityUniqueId($this->traderEid);
		$out->putEntityUniqueId($this->playerEid);
		$out->putString($this->displayName);
		$out->put($this->offers);
	}

	public function handle(SessionHandler $handler) : bool{
		return $handler->handleUpdateTrade($this);
	}
}
