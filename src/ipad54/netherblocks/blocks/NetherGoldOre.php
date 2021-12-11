<?php

namespace ipad54\netherblocks\blocks;

use pocketmine\block\Opaque;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;

class NetherGoldOre extends Opaque {

    public function isAffectedBySilkTouch(): bool
    {
        return true;
    }

    public function getDropsForCompatibleTool(Item $item): array
    {
        return [VanillaItems::GOLD_NUGGET()->setCount(mt_rand(2, 6))];
    }

    public function getXpDropAmount(): int
    {
        return 1;
    }
}