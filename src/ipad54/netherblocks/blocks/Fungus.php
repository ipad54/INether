<?php

namespace ipad54\netherblocks\blocks;

use ipad54\netherblocks\utils\CustomIds;
use pocketmine\block\Block;
use pocketmine\block\Flowable;
use pocketmine\block\Sapling;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class Fungus extends Flowable {

    public function isWarped() : bool {
        return false;
    }

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool
    {
        $side = $this->getSide(Facing::DOWN);
        if(in_array($side->getId(), [ItemIds::DIRT, ItemIds::FARMLAND, ItemIds::PODZOL, ItemIds::GRASS, CustomIds::NYLIUM_BLOCK, CustomIds::WARPED_NYLIUM_BLOCK, CustomIds::SOUL_SOIL_BLOCK])){
            return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
        }
        return false;
    }

    public function onNearbyBlockChange(): void
    {
        if(!$this->getSide(Facing::DOWN)->isSolid()){
            $this->position->getWorld()->useBreakOn($this->position);
        }
    }
}