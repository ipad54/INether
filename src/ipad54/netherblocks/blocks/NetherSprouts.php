<?php

namespace ipad54\netherblocks\blocks;

use ipad54\netherblocks\utils\CustomIds;
use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\Flowable;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;


class NetherSprouts extends Flowable {

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool
    {
        $down = $this->getSide(Facing::DOWN);
        if(in_array($down->getId(), [BlockLegacyIds::FARMLAND, BlockLegacyIds::PODZOL, BlockLegacyIds::GRASS, CustomIds::NYLIUM_BLOCK, CustomIds::WARPED_NYLIUM_BLOCK, CustomIds::SOUL_SOIL_BLOCK])){
            return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
        }
        return false;
    }
}