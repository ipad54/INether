<?php

namespace ipad54\netherblocks\sound;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\world\sound\Sound;

class AnchorChargeSound implements Sound{

    public function encode(Vector3 $pos): array
    {
        return [LevelSoundEventPacket::nonActorSound(LevelSoundEvent::RESPAWN_ANCHOR_CHARGE, $pos, false)];
    }
}