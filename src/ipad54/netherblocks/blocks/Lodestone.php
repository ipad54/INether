<?php

namespace ipad54\netherblocks\blocks;

use ipad54\netherblocks\items\LodestoneCompass;
use ipad54\netherblocks\sound\LodestoneCompassLinkSound;
use ipad54\netherblocks\tile\Lodestone as TileLodestone;
use pocketmine\block\Block;
use pocketmine\block\Opaque;
use pocketmine\item\Compass;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class Lodestone extends Opaque {

	private static int $nextId = 0;

	public static function getNextId() : int{
		return self::$nextId++;
	}

    protected int $lodestoneId = -1;

    public function writeStateToWorld(): void
    {
        parent::writeStateToWorld();
        $tile = $this->position->getWorld()->getTile($this->position);
        if($tile instanceof TileLodestone){
            $tile->setLodestoneId($this->lodestoneId);
        }
    }

    public function readStateFromWorld(): void
    {
        parent::readStateFromWorld();
        $tile = $this->position->getWorld()->getTile($this->position);
        if($tile instanceof TileLodestone){
            $this->lodestoneId = $tile->getLodestoneId();
			if(self::$nextId > $this->lodestoneId){
				self::$nextId = $this->lodestoneId + 1;
			}
        }
    }

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool
    {
        if($this->lodestoneId === -1){
            $this->lodestoneId = self::$nextId++;
        }
        return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null): bool
    {
        $tile = $this->position->getWorld()->getTile($this->position);
        if(!$tile instanceof TileLodestone) {
            return false;
        }

        if($item instanceof Compass or $item instanceof LodestoneCompass){
            $item->pop();
			/** @var LodestoneCompass $item */
			$item = StringToItemParser::getInstance()->parse("lodestone_compass");
            $item->setLodestoneId($this->lodestoneId);
            $this->position->getWorld()->addSound($this->position, new LodestoneCompassLinkSound());
            $player->getInventory()->addItem($item);
            return true;
        }
        return false;
    }
}