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


use pocketmine\math\Vector3;
use pocketmine\network\mcpe\handler\SessionHandler;
use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use function count;
use function file_get_contents;
use function json_decode;

class StartGamePacket extends BasePacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::START_GAME_PACKET;

	/** @var string|null */
	private static $runtimeIdTable;

	/** @var int */
	public $entityUniqueId;
	/** @var int */
	public $entityRuntimeId;
	/** @var int */
	public $playerGamemode;

	/** @var Vector3 */
	public $playerPosition;

	/** @var float */
	public $pitch;
	/** @var float */
	public $yaw;

	/** @var int */
	public $seed;
	/** @var int */
	public $dimension;
	/** @var int */
	public $generator = 1; //default infinite - 0 old, 1 infinite, 2 flat
	/** @var int */
	public $worldGamemode;
	/** @var int */
	public $difficulty;
	/** @var int */
	public $spawnX;
	/** @var int */
	public $spawnY;
	/** @var int */
	public $spawnZ;
	/** @var bool */
	public $hasAchievementsDisabled = true;
	/** @var int */
	public $time = -1;
	/** @var bool */
	public $eduMode = false;
	/** @var bool */
	public $hasEduFeaturesEnabled = false;
	/** @var float */
	public $rainLevel;
	/** @var float */
	public $lightningLevel;
	/** @var bool */
	public $isMultiplayerGame = true;
	/** @var bool */
	public $hasLANBroadcast = true;
	/** @var bool */
	public $hasXboxLiveBroadcast = false;
	/** @var bool */
	public $commandsEnabled;
	/** @var bool */
	public $isTexturePacksRequired = true;
	/** @var array */
	public $gameRules = []; //TODO: implement this
	/** @var bool */
	public $hasBonusChestEnabled = false;
	/** @var bool */
	public $hasStartWithMapEnabled = false;
	/** @var bool */
	public $hasTrustPlayersEnabled = false;
	/** @var int */
	public $defaultPlayerPermission = PlayerPermissions::MEMBER; //TODO
	/** @var int */
	public $xboxLiveBroadcastMode = 0; //TODO: find values
	/** @var int */
	public $serverChunkTickRadius = 4; //TODO (leave as default for now)
	/** @var bool */
	public $hasPlatformBroadcast = false;
	/** @var int */
	public $platformBroadcastMode = 0;
	/** @var bool */
	public $xboxLiveBroadcastIntent = false;
	/** @var bool */
	public $hasLockedBehaviorPack = false;
	/** @var bool */
	public $hasLockedResourcePack = false;
	/** @var bool */
	public $isFromLockedWorldTemplate = false;
	/** @var bool */
	public $useMsaGamertagsOnly = false;
	/** @var bool */
	public $isFromWorldTemplate = false;
	/** @var bool */
	public $isWorldTemplateOptionLocked = false;

	/** @var string */
	public $levelId = ""; //base64 string, usually the same as world folder name in vanilla
	/** @var string */
	public $worldName;
	/** @var string */
	public $premiumWorldTemplateId = "";
	/** @var bool */
	public $isTrial = false;
	/** @var int */
	public $currentTick = 0; //only used if isTrial is true
	/** @var int */
	public $enchantmentSeed = 0;
	/** @var string */
	public $multiplayerCorrelationId = ""; //TODO: this should be filled with a UUID of some sort

	protected function decodePayload(NetworkBinaryStream $in) : void{
		$this->entityUniqueId = $in->getEntityUniqueId();
		$this->entityRuntimeId = $in->getEntityRuntimeId();
		$this->playerGamemode = $in->getVarInt();

		$this->playerPosition = $in->getVector3();

		$this->pitch = $in->getLFloat();
		$this->yaw = $in->getLFloat();

		//Level settings
		$this->seed = $in->getVarInt();
		$this->dimension = $in->getVarInt();
		$this->generator = $in->getVarInt();
		$this->worldGamemode = $in->getVarInt();
		$this->difficulty = $in->getVarInt();
		$in->getBlockPosition($this->spawnX, $this->spawnY, $this->spawnZ);
		$this->hasAchievementsDisabled = $in->getBool();
		$this->time = $in->getVarInt();
		$this->eduMode = $in->getBool();
		$this->hasEduFeaturesEnabled = $in->getBool();
		$this->rainLevel = $in->getLFloat();
		$this->lightningLevel = $in->getLFloat();
		$this->isMultiplayerGame = $in->getBool();
		$this->hasLANBroadcast = $in->getBool();
		$this->hasXboxLiveBroadcast = $in->getBool();
		$this->commandsEnabled = $in->getBool();
		$this->isTexturePacksRequired = $in->getBool();
		$this->gameRules = $in->getGameRules();
		$this->hasBonusChestEnabled = $in->getBool();
		$this->hasStartWithMapEnabled = $in->getBool();
		$this->hasTrustPlayersEnabled = $in->getBool();
		$this->defaultPlayerPermission = $in->getVarInt();
		$this->xboxLiveBroadcastMode = $in->getVarInt();
		$this->serverChunkTickRadius = $in->getLInt();
		$this->hasPlatformBroadcast = $in->getBool();
		$this->platformBroadcastMode = $in->getVarInt();
		$this->xboxLiveBroadcastIntent = $in->getBool();
		$this->hasLockedBehaviorPack = $in->getBool();
		$this->hasLockedResourcePack = $in->getBool();
		$this->isFromLockedWorldTemplate = $in->getBool();
		$this->useMsaGamertagsOnly = $in->getBool();
		$this->isFromWorldTemplate = $in->getBool();
		$this->isWorldTemplateOptionLocked = $in->getBool();

		$this->levelId = $in->getString();
		$this->worldName = $in->getString();
		$this->premiumWorldTemplateId = $in->getString();
		$this->isTrial = $in->getBool();
		$this->currentTick = $in->getLLong();

		$this->enchantmentSeed = $in->getVarInt();

		$count = $in->getUnsignedVarInt();
		for($i = 0; $i < $count; ++$i){
			$in->getString();
			$in->getLShort();
		}

		$this->multiplayerCorrelationId = $in->getString();
	}

	protected function encodePayload(NetworkBinaryStream $out) : void{
		$out->putEntityUniqueId($this->entityUniqueId);
		$out->putEntityRuntimeId($this->entityRuntimeId);
		$out->putVarInt($this->playerGamemode);

		$out->putVector3($this->playerPosition);

		$out->putLFloat($this->pitch);
		$out->putLFloat($this->yaw);

		//Level settings
		$out->putVarInt($this->seed);
		$out->putVarInt($this->dimension);
		$out->putVarInt($this->generator);
		$out->putVarInt($this->worldGamemode);
		$out->putVarInt($this->difficulty);
		$out->putBlockPosition($this->spawnX, $this->spawnY, $this->spawnZ);
		$out->putBool($this->hasAchievementsDisabled);
		$out->putVarInt($this->time);
		$out->putBool($this->eduMode);
		$out->putBool($this->hasEduFeaturesEnabled);
		$out->putLFloat($this->rainLevel);
		$out->putLFloat($this->lightningLevel);
		$out->putBool($this->isMultiplayerGame);
		$out->putBool($this->hasLANBroadcast);
		$out->putBool($this->hasXboxLiveBroadcast);
		$out->putBool($this->commandsEnabled);
		$out->putBool($this->isTexturePacksRequired);
		$out->putGameRules($this->gameRules);
		$out->putBool($this->hasBonusChestEnabled);
		$out->putBool($this->hasStartWithMapEnabled);
		$out->putBool($this->hasTrustPlayersEnabled);
		$out->putVarInt($this->defaultPlayerPermission);
		$out->putVarInt($this->xboxLiveBroadcastMode);
		$out->putLInt($this->serverChunkTickRadius);
		$out->putBool($this->hasPlatformBroadcast);
		$out->putVarInt($this->platformBroadcastMode);
		$out->putBool($this->xboxLiveBroadcastIntent);
		$out->putBool($this->hasLockedBehaviorPack);
		$out->putBool($this->hasLockedResourcePack);
		$out->putBool($this->isFromLockedWorldTemplate);
		$out->putBool($this->useMsaGamertagsOnly);
		$out->putBool($this->isFromWorldTemplate);
		$out->putBool($this->isWorldTemplateOptionLocked);

		$out->putString($this->levelId);
		$out->putString($this->worldName);
		$out->putString($this->premiumWorldTemplateId);
		$out->putBool($this->isTrial);
		$out->putLLong($this->currentTick);

		$out->putVarInt($this->enchantmentSeed);

		if(self::$runtimeIdTable === null){
			//this is a really nasty hack, but it'll do for now
			$stream = new NetworkBinaryStream();
			$data = json_decode(file_get_contents(\pocketmine\RESOURCE_PATH . "runtimeid_table.json"), true);
			$stream->putUnsignedVarInt(count($data));
			foreach($data as $v){
				$stream->putString($v["name"]);
				$stream->putLShort($v["data"]);
			}
			self::$runtimeIdTable = $stream->getBuffer();
		}
		$out->put(self::$runtimeIdTable);

		$out->putString($this->multiplayerCorrelationId);
	}

	public function handle(SessionHandler $handler) : bool{
		return $handler->handleStartGame($this);
	}
}
