<?php

namespace ipad54\netherblocks\blocks;

use pocketmine\block\Grass;
use pocketmine\block\Netherrack;
use pocketmine\block\Opaque;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\math\Facing;


class Nylium extends Opaque
{

    public function isWarped(): bool
    {
        return false;
    }

    public function ticksRandomly(): bool
    {
        return true;
    }

    public function isAffectedBySilkTouch(): bool
    {
        return true;
    }

    public function onRandomTick(): void
    {
        $block = $this->getSide(Facing::UP);
        if($block instanceof Netherrack && mt_rand(2, 5) == 5){
            $this->position->getWorld()->setBlock($this->position, VanillaBlocks::NETHERRACK());
        }
    }

    public function getDropsForCompatibleTool(Item $item): array
    {
        return [
            VanillaBlocks::NETHERRACK()->asItem()
        ];
    }
}