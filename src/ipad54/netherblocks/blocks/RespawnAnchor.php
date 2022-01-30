<?php

namespace ipad54\netherblocks\blocks;

use ipad54\netherblocks\sound\AnchorChargeSound;
use pocketmine\block\Opaque;
use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\Explosion;

class RespawnAnchor extends Opaque
{

    protected int $charges = 0;

    public function readStateFromData(int $id, int $stateMeta): void
    {
        $this->charges = BlockDataSerializer::readBoundedInt("charges", $stateMeta, 0, 4);
    }

    public function writeStateToMeta(): int
    {
        return $this->charges;
    }

    public function getStateBitmask(): int
    {
        return 0b111;
    }

    public function setCharges(int $charges): self
    {
        $this->charges = $charges;
        return $this;
    }

    public function getLightLevel(): int
    {
        return ($this->charges == 1 ? 3 : ($this->charges > 1 ? 3 + 4 * ($this->charges - 1) : 0));
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null): bool
    {

        if ($item->getId() == ItemIds::GLOWSTONE && $this->charges < 4) {
            $item->pop();
            $this->charges++;
            $this->position->getWorld()->setBlock($this->position, $this);
            $this->position->getWorld()->addSound($this->position, new AnchorChargeSound());
            return true;
        }
        if ($this->charges >= 1) {
            $this->explode();
            $this->position->getWorld()->setBlock($this->position, VanillaBlocks::AIR());
            return true;
        }
        //TODO: nether is not implemented in PM :(
        return false;
    }

    private function explode(): void
    {
        $explosion = new Explosion($this->position, 5, $this);

        $explosion->explodeA();
        $explosion->explodeB();
    }
}