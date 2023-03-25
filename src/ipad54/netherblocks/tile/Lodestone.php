<?php

namespace ipad54\netherblocks\tile;

use pocketmine\block\tile\Spawnable;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\PositionTrackingDBServerBroadcastPacket;

class Lodestone extends Spawnable
{

    public const TAG_TRACKING = "trackingHandle";

    protected int $lodestoneId = -1;

    public function getLodestoneId(): int
    {
        return $this->lodestoneId;
    }

    public function setLodestoneId(int $id) : self{
        $this->lodestoneId = $id;
        return $this;
    }

    protected function addAdditionalSpawnData(CompoundTag $nbt): void
    {
		if($this->lodestoneId > -1){
			$nbt->setInt(self::TAG_TRACKING, $this->lodestoneId); // only exists to the client after a compass exists to track it
		}
    }

    public function readSaveData(CompoundTag $nbt): void
    {
        $this->lodestoneId = $nbt->getInt(self::TAG_TRACKING, -1);
    }

    protected function writeSaveData(CompoundTag $nbt): void
    {
        $nbt->setInt(self::TAG_TRACKING, $this->lodestoneId);
    }

	protected function onBlockDestroyedHook() : void{
		if($this->lodestoneId > -1){ // only exists to the client after a compass exists to track it
			$this->getPosition()->getWorld()->broadcastPacketToViewers($this->getPosition(), PositionTrackingDBServerBroadcastPacket::create(
				PositionTrackingDBServerBroadcastPacket::ACTION_DESTROY,
				$this->getLodestoneId(),
				$this->getSerializedSpawnCompound()
			));
		}
	}
}