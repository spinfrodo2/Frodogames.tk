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


use pocketmine\entity\Skin;
use pocketmine\network\mcpe\handler\SessionHandler;
use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use function count;

class PlayerListPacket extends BasePacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::PLAYER_LIST_PACKET;

	public const TYPE_ADD = 0;
	public const TYPE_REMOVE = 1;

	/** @var PlayerListEntry[] */
	public $entries = [];
	/** @var int */
	public $type;

	protected function decodePayload(NetworkBinaryStream $in) : void{
		$this->type = $in->getByte();
		$count = $in->getUnsignedVarInt();
		for($i = 0; $i < $count; ++$i){
			$entry = new PlayerListEntry();

			if($this->type === self::TYPE_ADD){
				$entry->uuid = $in->getUUID();
				$entry->entityUniqueId = $in->getEntityUniqueId();
				$entry->username = $in->getString();

				$skinId = $in->getString();
				$skinData = $in->getString();
				$capeData = $in->getString();
				$geometryName = $in->getString();
				$geometryData = $in->getString();

				$entry->skin = new Skin(
					$skinId,
					$skinData,
					$capeData,
					$geometryName,
					$geometryData
				);
				$entry->xboxUserId = $in->getString();
				$entry->platformChatId = $in->getString();
			}else{
				$entry->uuid = $in->getUUID();
			}

			$this->entries[$i] = $entry;
		}
	}

	protected function encodePayload(NetworkBinaryStream $out) : void{
		$out->putByte($this->type);
		$out->putUnsignedVarInt(count($this->entries));
		foreach($this->entries as $entry){
			if($this->type === self::TYPE_ADD){
				$out->putUUID($entry->uuid);
				$out->putEntityUniqueId($entry->entityUniqueId);
				$out->putString($entry->username);
				$out->putString($entry->skin->getSkinId());
				$out->putString($entry->skin->getSkinData());
				$out->putString($entry->skin->getCapeData());
				$out->putString($entry->skin->getGeometryName());
				$out->putString($entry->skin->getGeometryData());
				$out->putString($entry->xboxUserId);
				$out->putString($entry->platformChatId);
			}else{
				$out->putUUID($entry->uuid);
			}
		}
	}

	public function handle(SessionHandler $handler) : bool{
		return $handler->handlePlayerList($this);
	}
}
