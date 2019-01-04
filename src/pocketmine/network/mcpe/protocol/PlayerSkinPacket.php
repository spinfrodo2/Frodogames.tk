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
use pocketmine\utils\UUID;

class PlayerSkinPacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::PLAYER_SKIN_PACKET;

	/** @var UUID */
	public $uuid;
	/** @var string */
	public $oldSkinName = "";
	/** @var string */
	public $newSkinName = "";
	/** @var Skin */
	public $skin;
	/** @var bool */
	public $premiumSkin = false;

	protected function decodePayload(NetworkBinaryStream $in) : void{
		$this->uuid = $in->getUUID();

		$skinId = $in->getString();
		$this->newSkinName = $in->getString();
		$this->oldSkinName = $in->getString();
		$skinData = $in->getString();
		$capeData = $in->getString();
		$geometryModel = $in->getString();
		$geometryData = $in->getString();

		$this->skin = new Skin($skinId, $skinData, $capeData, $geometryModel, $geometryData);

		$this->premiumSkin = $in->getBool();
	}

	protected function encodePayload(NetworkBinaryStream $out) : void{
		$out->putUUID($this->uuid);

		$out->putString($this->skin->getSkinId());
		$out->putString($this->newSkinName);
		$out->putString($this->oldSkinName);
		$out->putString($this->skin->getSkinData());
		$out->putString($this->skin->getCapeData());
		$out->putString($this->skin->getGeometryName());
		$out->putString($this->skin->getGeometryData());

		$out->putBool($this->premiumSkin);
	}

	public function handle(SessionHandler $handler) : bool{
		return $handler->handlePlayerSkin($this);
	}
}
