<?php

namespace ipad54\netherblocks\blocks;

use ipad54\netherblocks\items\LodestoneCompass;
use ipad54\netherblocks\Main;
use ipad54\netherblocks\sound\LodestoneCompassLinkSound;
use ipad54\netherblocks\tile\Lodestone as TileLodestone;
use ipad54\netherblocks\utils\CustomIds;
use pocketmine\block\Block;
use pocketmine\block\Opaque;
use pocketmine\item\Compass;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class Lodestone extends Opaque {

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
        }
    }

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool
    {
        if($this->lodestoneId == -1){
            $this->lodestoneId = Main::getInstance()->addLodestone($this->position);
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
            $item = ItemFactory::getInstance()->get(CustomIds::LODESTONE_COMPASS, 0);
            $item->setLodestoneId($this->lodestoneId);
            $this->position->getWorld()->addSound($this->position, new LodestoneCompassLinkSound());
            $player->getInventory()->addItem($item);
            var_dump($this->lodestoneId);
            return true;
        }
        return false;
    }
}