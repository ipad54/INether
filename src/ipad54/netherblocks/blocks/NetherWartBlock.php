<?php

namespace ipad54\netherblocks\blocks;

use pocketmine\block\Opaque;

class NetherWartBlock extends Opaque {

    public function isWarped() : bool {
        return false;
    }
}