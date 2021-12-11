<?php

namespace ipad54\netherblocks\blocks;

use pocketmine\block\Opaque;

class SoulSoil extends Opaque {

    public function burnsForever(): bool
    {
        return true;
    }
}