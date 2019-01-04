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

class AddHangingEntityPacket extends BasePacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::ADD_HANGING_ENTITY_PACKET;

	/** @var int|null */
	public $entityUniqueId = null;
	/** @var int */
	public $entityRuntimeId;
	/** @var int */
	public $x;
	/** @var int */
	public $y;
	/** @var int */
	public $z;
	/** @var int */
	public $direction;

	protected function decodePayload(NetworkBinaryStream $in) : void{
		$this->entityUniqueId = $in->getEntityUniqueId();
		$this->entityRuntimeId = $in->getEntityRuntimeId();
		$in->getBlockPosition($this->x, $this->y, $this->z);
		$this->direction = $in->getVarInt();
	}

	protected function encodePayload(NetworkBinaryStream $out) : void{
		$out->putEntityUniqueId($this->entityUniqueId ?? $this->entityRuntimeId);
		$out->putEntityRuntimeId($this->entityRuntimeId);
		$out->putBlockPosition($this->x, $this->y, $this->z);
		$out->putVarInt($this->direction);
	}

	public function handle(SessionHandler $handler) : bool{
		return $handler->handleAddHangingEntity($this);
	}
}
