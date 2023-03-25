<?php

namespace ipad54\netherblocks\listener;

use ipad54\netherblocks\player\Player as MyPlayer;
use ipad54\netherblocks\tile\Lodestone;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\PositionTrackingDBClientRequestPacket;
use pocketmine\network\mcpe\protocol\PositionTrackingDBServerBroadcastPacket;

class EventListener implements Listener {

    public function onPlayerCreation(PlayerCreationEvent $event){
        $event->setPlayerClass(MyPlayer::class);
    }

	public function onDataPacket(DataPacketReceiveEvent $event) : void {
		$packet = $event->getPacket();
		if($packet instanceof PositionTrackingDBClientRequestPacket) {
			$trackingId = $packet->getTrackingId();
			$session = $event->getOrigin();
			$world = $session->getPlayer()->getWorld();
			foreach($world->getLoadedChunks() as $chunk) {
				foreach($chunk->getTiles() as $tile) {
					if($tile instanceof Lodestone and $tile->getLodestoneId() === $trackingId) {
						$session->sendDataPacket(PositionTrackingDBServerBroadcastPacket::create(
							PositionTrackingDBServerBroadcastPacket::ACTION_UPDATE,
							$trackingId,
							$tile->getSerializedSpawnCompound()
						));
					}
				}
			}
		}
	}
}