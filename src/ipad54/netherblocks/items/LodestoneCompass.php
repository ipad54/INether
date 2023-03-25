<?php

namespace ipad54\netherblocks\items;

use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;

class LodestoneCompass extends Item
{

    public const TAG_TRACKING = "trackingHandle";

    protected int $lodestoneId = -1;

    public function getMaxStackSize(): int
    {
        return 1;
    }

    public function setLodestoneId(int $id) : self {
        $this->lodestoneId = $id;
        return $this;
    }

    public function deserializeCompoundTag(CompoundTag $tag): void
    {
		parent::deserializeCompoundTag($tag);
		$this->lodestoneId = $tag->getInt(self::TAG_TRACKING, -1);
    }

    public function serializeCompoundTag(CompoundTag $tag): void
    {
		parent::serializeCompoundTag($tag);
        if($this->lodestoneId > -1){
            $tag->setInt(self::TAG_TRACKING, $this->lodestoneId);
        }
    }
}