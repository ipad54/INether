<?php

namespace ipad54\netherblocks\blocks;

use pocketmine\block\Lantern;

class SoulLantern extends Lantern {

    public function getLightLevel(): int
    {
        return 10;
    }
}