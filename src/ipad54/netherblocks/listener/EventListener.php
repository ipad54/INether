<?php

namespace ipad54\netherblocks\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use ipad54\netherblocks\player\Player as MyPlayer;

class EventListener implements Listener {

    public function onPlayerCreation(PlayerCreationEvent $event){
        $event->setPlayerClass(MyPlayer::class);
    }
}