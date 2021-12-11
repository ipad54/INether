<?php

namespace ipad54\netherblocks\blocks;

use pocketmine\block\Opaque;

class CryingObsidian extends Opaque {

    public function getLightLevel(): int
    {
        return 10;
    }
}