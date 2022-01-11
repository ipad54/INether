<?php

namespace ipad54\netherblocks\blocks;

use ipad54\netherblocks\object\NetherTree;
use ipad54\netherblocks\utils\CustomIds;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\Flowable;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\StructureGrowEvent;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;

class Fungus extends Flowable {

    public function isWarped() : bool {
        return false;
    }

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool
    {
        $side = $this->getSide(Facing::DOWN);
        if(in_array($side->getId(), [ItemIds::DIRT, ItemIds::FARMLAND, ItemIds::PODZOL, ItemIds::GRASS, CustomIds::NYLIUM_BLOCK, CustomIds::WARPED_NYLIUM_BLOCK, CustomIds::SOUL_SOIL_BLOCK])){
            return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
        }
        return false;
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null): bool
    {
        if($item instanceof Fertilizer && $this->grow($player)){
            $item->pop();
            return true;
        }
        return false;
    }

    private function grow(?Player $player) : bool{
        $random = new Random(mt_rand());
        $tree = $this->isWarped() ? new NetherTree(BlockFactory::getInstance()->get(CustomIds::WARPED_STEM_BLOCK, 0), BlockFactory::getInstance()->get(CustomIds::WARPED_WART_BLOCK, 0)) : new NetherTree(BlockFactory::getInstance()->get(CustomIds::CRIMSON_STEM_BLOCK, 0), VanillaBlocks::NETHER_WART_BLOCK());
        $transaction = $tree?->getBlockTransaction($this->position->getWorld(), $this->position->getFloorX(), $this->position->getFloorY(), $this->position->getFloorZ(), $random);
        if($transaction === null){
            return false;
        }
        $ev = new StructureGrowEvent($this, $transaction, $player);
        $ev->call();
        if(!$ev->isCancelled()){
            return $transaction->apply();
        }
        return false;
    }

    public function onNearbyBlockChange(): void
    {
        if(!$this->getSide(Facing::DOWN)->isSolid()){
            $this->position->getWorld()->useBreakOn($this->position);
        }
    }
}