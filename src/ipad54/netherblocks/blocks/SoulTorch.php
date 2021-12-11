<?php

namespace ipad54\netherblocks\blocks;

use pocketmine\block\Torch;

class SoulTorch extends Torch {

    public function getLightLevel() : int{
        return 10;
    }
}
