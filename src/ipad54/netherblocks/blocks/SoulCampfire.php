<?php

namespace ipad54\netherblocks\blocks;

use ipad54\netherblocks\utils\CustomIds;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;

class SoulCampfire extends Campfire {

    public function isSoul(): bool
    {
        return true;
    }

    public function getDropsForCompatibleTool(Item $item): array
    {
        return [
            ItemFactory::getInstance()->get(CustomIds::SOUL_SOIL_ITEM, 0)
        ];
    }
}