<?php

namespace ipad54\netherblocks\blocks;
use pocketmine\block\Opaque;

class Shroomlight extends Opaque {

    public function getLightLevel(): int
    {
        return 15;
    }

    public function isFlammable(): bool
    {
        return true;
    }
}