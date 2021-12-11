<?php

namespace ipad54\netherblocks\items;

use pocketmine\item\Durable;

class WarpedFungusOnAStick extends Durable {

    public function getMaxDurability(): int
    {
        return 100;
    }

    public function getMaxStackSize(): int
    {
        return 1;
    }

    //TODO: Mobs not implemented in PM :(
}