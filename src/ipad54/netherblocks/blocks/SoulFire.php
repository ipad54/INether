<?php

namespace ipad54\netherblocks\blocks;

use pocketmine\block\Fire;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Arrow;
use pocketmine\event\entity\EntityCombustByBlockEvent;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Facing;

class SoulFire extends Fire {

    public function getLightLevel(): int
    {
        return 10;
    }

    public function onEntityInside(Entity $entity) : bool{
        $ev = new EntityDamageByBlockEvent($this, $entity, EntityDamageEvent::CAUSE_FIRE, 2);
        $entity->attack($ev);

        $ev = new EntityCombustByBlockEvent($this, $entity, 8);
        if($entity instanceof Arrow){
            $ev->cancel();
        }
        $ev->call();
        if(!$ev->isCancelled()){
            $entity->setOnFire($ev->getDuration());
        }
        return true;
    }
}