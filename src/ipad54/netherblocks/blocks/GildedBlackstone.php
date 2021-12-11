<?php

namespace ipad54\netherblocks\blocks;

use ipad54\netherblocks\utils\CustomIds;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\VanillaItems;

class GildedBlackstone extends Blackstone
{

    public function isAffectedBySilkTouch(): bool
    {
        return true;
    }

    public function getDropsForCompatibleTool(Item $item): array
    {
        return mt_rand(1, 100) <= 10 ? [
            VanillaItems::GOLD_NUGGET()->setCount(mt_rand(2, 5))
        ] : [
            ItemFactory::getInstance()->get(CustomIds::GILDED_BLACKSTONE_ITEM, 0)
        ];
    }
}