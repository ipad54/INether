<?php

namespace ipad54\netherblocks\player;

use ipad54\netherblocks\utils\CustomIds;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\math\Vector3;
use pocketmine\player\Player as PMPlayer;
use pocketmine\player\SurvivalBlockBreakHandler;
use pocketmine\world\sound\FireExtinguishSound;

class Player extends PMPlayer {

    public function attackBlock(Vector3 $pos, int $face) : bool{
        if($pos->distanceSquared($this->location) > 10000){
            return false; //TODO: maybe this should throw an exception instead?
        }

        $target = $this->getWorld()->getBlock($pos);

        $ev = new PlayerInteractEvent($this, $this->inventory->getItemInHand(), $target, null, $face, PlayerInteractEvent::LEFT_CLICK_BLOCK);
        if($this->isSpectator()){
            $ev->cancel();
        }
        $ev->call();
        if($ev->isCancelled()){
            return false;
        }
        $this->broadcastAnimation(new ArmSwingAnimation($this), $this->getViewers());
        if($target->onAttack($this->inventory->getItemInHand(), $face, $this)){
            return true;
        }

        $block = $target->getSide($face);
        if($block->getId() === BlockLegacyIds::FIRE or $block->getId() === CustomIds::SOUL_FIRE_BLOCK){
            $this->getWorld()->setBlock($block->getPosition(), VanillaBlocks::AIR());
            $this->getWorld()->addSound($block->getPosition()->add(0.5, 0.5, 0.5), new FireExtinguishSound());
            return true;
        }

        if(!$this->isCreative() && !$block->getBreakInfo()->breaksInstantly()){
            $this->blockBreakHandler = new SurvivalBlockBreakHandler($this, $pos, $target, $face, 16);
        }

        return true;
    }
}