<?php

namespace ipad54\netherblocks\sound;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelEvent;
use pocketmine\world\sound\Sound;

class ItemFrameAddItemSound implements Sound {

    public function encode(Vector3 $pos): array
    {
        return [LevelSoundEventPacket::nonActorSound(LevelEvent::SOUND_ITEMFRAME_ADD_ITEM, $pos, false)];
    }
}