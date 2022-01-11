<?php

namespace ipad54\netherblocks\object;

use ipad54\netherblocks\blocks\Fungus;
use ipad54\netherblocks\blocks\NetherWartBlock;
use ipad54\netherblocks\utils\CustomIds;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\generator\object\Tree;

class NetherTree extends Tree
{

    protected function canOverride(Block $block): bool
    {
        return $block->canBeReplaced() || $block instanceof Fungus || $block instanceof NetherWartBlock;
    }

    protected function placeCanopy(int $x, int $y, int $z, Random $random, BlockTransaction $transaction): void
    {
        $blankArea = -3;
        $mid = (int)(1 - $blankArea / 2);
        $bf = BlockFactory::getInstance();
        for ($yy = $y - 3 + $this->treeHeight; $yy <= $y + $this->treeHeight - 1; ++$yy) {
            for ($xx = $x - $mid; $xx <= $x + $mid; $xx++) {
                $xOff = abs($xx - $x);
                for ($zz = $z - $mid; $zz <= $z + $mid; $zz += $mid * 2) {
                    $zOff = abs($zz - $z);
                    if ($xOff == $mid && $zOff == $mid && $random->nextBoundedInt(2) === 0) {
                        continue;
                    }
                    if (!$transaction->fetchBlockAt($xx, $yy, $zz)->isSolid()) {
                        if ($random->nextBoundedInt(20) === 0) $transaction->addBlockAt($xx, $yy, $zz, $bf->get(CustomIds::SHROOMLIGHT_BLOCK, 0));
                        else $transaction->addBlockAt($xx, $yy, $zz, $this->leafBlock);
                    }
                }
            }

            for ($zz = $z - $mid; $zz <= $z + $mid; $zz++) {
                $zOff = abs($zz - $z);
                for ($xx = $x - $mid; $xx <= $x + $mid; $xx += $mid * 2) {
                    $xOff = abs($xx - $x);
                    if ($xOff == $mid && $zOff == $mid && ($random->nextBoundedInt(2) === 0)) {
                        continue;
                    }
                    if (!$transaction->fetchBlockAt($xx, $yy, $zz)->isSolid()) {
                        if ($random->nextBoundedInt(20) === 0) $transaction->addBlockAt($xx, $yy, $zz, $bf->get(CustomIds::SHROOMLIGHT_BLOCK, 0));
                        else $transaction->addBlockAt($xx, $yy, $zz, $this->leafBlock);
                    }
                }
            }
        }

        for ($yy = $y - 4 + $this->treeHeight; $yy <= $y + $this->treeHeight - 3; ++$yy) {
            for ($xx = $x - $mid; $xx <= $x + $mid; $xx++) {
                for ($zz = $z - $mid; $zz <= $z + $mid; $zz += $mid * 2) {
                    if (!$transaction->fetchBlockAt($xx, $yy, $zz)->isSolid()) {
                        if ($random->nextBoundedInt(3) === 0) {
                            for ($i = 0; $i < $random->nextBoundedInt(5); $i++) {
                                if (!$transaction->fetchBlockAt($xx, $yy - $i, $zz)->isSolid()) $transaction->addBlockAt($xx, $yy - $i, $zz, $this->leafBlock);
                            }
                        }
                    }
                }
            }

            for ($zz = $z - $mid; $zz <= $z + $mid; $zz++) {
                for ($xx = $x - $mid; $xx <= $x + $mid; $xx += $mid * 2) {
                    if (!$transaction->fetchBlockAt($xx, $yy, $zz)->isSolid()) {
                        if ($random->nextBoundedInt(3) === 0) {
                            for ($i = 0; $i < $random->nextBoundedInt(4); $i++) {
                                if (!$transaction->fetchBlockAt($xx, $yy - $i, $zz)->isSolid()) $transaction->addBlockAt($xx, $yy - $i, $zz, $this->leafBlock);
                            }
                        }
                    }
                }
            }
        }

        for ($xCanopy = $x - $mid + 1; $xCanopy <= $x + $mid - 1; $xCanopy++) {
            for ($zCanopy = $z - $mid + 1; $zCanopy <= $z + $mid - 1; $zCanopy++) {
                if (!$transaction->fetchBlockAt($xCanopy, $y + $this->treeHeight, $zCanopy)->isSolid()) $transaction->addBlockAt($xCanopy, $y + $this->treeHeight, $zCanopy, $this->leafBlock);
            }
        }
    }

    protected function placeTrunk(int $x, int $y, int $z, Random $random, int $trunkHeight, BlockTransaction $transaction): void
    {
        $transaction->addBlockAt($x, $y, $z, $this->trunkBlock);
        for ($yy = 0; $yy <= $trunkHeight; ++$yy) {
            $block = $transaction->fetchBlockAt($x, $y + $yy, $z);
            if ($this->canOverride($block)) {
                $transaction->addBlockAt($x, $y + $yy, $z, $this->trunkBlock);
            }
        }
    }
}
