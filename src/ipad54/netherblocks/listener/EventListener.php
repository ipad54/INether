<?php

namespace ipad54\netherblocks\listener;

use ipad54\netherblocks\utils\CustomIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\world\sound\FireExtinguishSound;

class EventListener implements Listener {

	public function onInteract(PlayerInteractEvent $event) : void {
		$face = $event->getFace();
		$target = $event->getBlock();
		$block = $target->getSide($face);
		$world = $target->getPosition()->getWorld();
		if($block->getId() === CustomIds::SOUL_FIRE_BLOCK){
			$world->setBlock($block->getPosition(), VanillaBlocks::AIR());
			$world->addSound($block->getPosition()->add(0.5, 0.5, 0.5), new FireExtinguishSound());
		}
	}
}