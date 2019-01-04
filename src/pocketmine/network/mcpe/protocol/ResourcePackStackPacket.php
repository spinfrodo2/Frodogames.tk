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
use pocketmine\resourcepacks\ResourcePack;
use function count;

class ResourcePackStackPacket extends BasePacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::RESOURCE_PACK_STACK_PACKET;

	/** @var bool */
	public $mustAccept = false;

	/** @var ResourcePack[] */
	public $behaviorPackStack = [];
	/** @var ResourcePack[] */
	public $resourcePackStack = [];

	/** @var bool */
	public $isExperimental = false;

	protected function decodePayload(NetworkBinaryStream $in) : void{
		$this->mustAccept = $in->getBool();
		$behaviorPackCount = $in->getUnsignedVarInt();
		while($behaviorPackCount-- > 0){
			$in->getString();
			$in->getString();
			$in->getString();
		}

		$resourcePackCount = $in->getUnsignedVarInt();
		while($resourcePackCount-- > 0){
			$in->getString();
			$in->getString();
			$in->getString();
		}

		$this->isExperimental = $in->getBool();
	}

	protected function encodePayload(NetworkBinaryStream $out) : void{
		$out->putBool($this->mustAccept);

		$out->putUnsignedVarInt(count($this->behaviorPackStack));
		foreach($this->behaviorPackStack as $entry){
			$out->putString($entry->getPackId());
			$out->putString($entry->getPackVersion());
			$out->putString(""); //TODO: subpack name
		}

		$out->putUnsignedVarInt(count($this->resourcePackStack));
		foreach($this->resourcePackStack as $entry){
			$out->putString($entry->getPackId());
			$out->putString($entry->getPackVersion());
			$out->putString(""); //TODO: subpack name
		}

		$out->putBool($this->isExperimental);
	}

	public function handle(SessionHandler $handler) : bool{
		return $handler->handleResourcePackStack($this);
	}
}
