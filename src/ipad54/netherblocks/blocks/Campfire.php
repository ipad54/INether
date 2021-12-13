<?php

namespace ipad54\netherblocks\blocks;

use ipad54\netherblocks\sound\ItemFrameAddItemSound;
use ipad54\netherblocks\tile\Campfire as TileCampfire;
use pocketmine\block\Block;
use pocketmine\block\Transparent;
use pocketmine\block\utils\HorizontalFacingTrait;
use pocketmine\block\Water;
use pocketmine\entity\Entity;
use pocketmine\entity\projectile\SplashPotion;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\FlintSteel;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\item\PotionType;
use pocketmine\item\Shovel;
use pocketmine\item\VanillaItems;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\world\sound\FireExtinguishSound;
use pocketmine\world\sound\FlintSteelSound;

class Campfire extends Transparent
{
    use HorizontalFacingTrait;

    public const CAMPFIRE_FLAG_EXTINGUISHED = 0X04;

    protected bool $extinguished = false;

    public function writeStateToMeta(): int
    {
        return $this->facing | ($this->extinguished ? self::CAMPFIRE_FLAG_EXTINGUISHED : 0);
    }

    public function readStateFromData(int $id, int $stateMeta): void
    {
        $this->facing = $stateMeta & 0x03;
        $this->extinguished = ($stateMeta & self::CAMPFIRE_FLAG_EXTINGUISHED) === self::CAMPFIRE_FLAG_EXTINGUISHED;
    }

    public function isExtinguished(): bool
    {
        return $this->extinguished;
    }

    public function setExtinguish(bool $extinguish): self
    {
        $this->extinguished = $extinguish;
        return $this;
    }

    public function isSoul(): bool
    {
        return false;
    }

    private function extinguish(): void
    {
        $this->position->getWorld()->addSound($this->position, new FireExtinguishSound());
        $this->position->getWorld()->setBlock($this->position, $this->setExtinguish(true));
    }

    private function fire(): void
    {
        $this->position->getWorld()->addSound($this->position, new FlintSteelSound());
        $this->position->getWorld()->setBlock($this->position, $this->setExtinguish(false));
        $this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 1);
    }

    public function getLightLevel(): int
    {
        return ($this->isSoul() ? (!$this->extinguished ? 10 : 0) : (!$this->extinguished ? 15 : 0));
    }

    public function getStateBitmask(): int
    {
        return 0b1111;
    }

    public function hasEntityCollision(): bool
    {
        return true;
    }


    public function isAffectedBySilkTouch(): bool
    {
        return true;
    }

    public function getDropsForCompatibleTool(Item $item): array
    {
        return [
            VanillaItems::CHARCOAL()->setCount(2)
        ];
    }

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool
    {
        $side = $this->getSide(Facing::DOWN);
        if ($side->getId() === ItemIds::AIR || $side->isTransparent()){
            return false;
        }
        if ($player != null) {
            $facing = [
                4 => 3,
                3 => 2,
                2 => 0,
                5 => 1
            ];
            $this->facing = $facing[$player->getHorizontalFacing()];
        }
        return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null): bool
    {
        if ($player !== null) {
            if ($item instanceof FlintSteel) {
                if($this->extinguished) {
                    $item->applyDamage(1);
                    $this->fire();
                }
                return true;
            }
            if ($item instanceof Shovel && !$this->extinguished) {
                $item->applyDamage(1);
                $this->extinguish();
                return true;
            }

            $tile = $this->position->getWorld()->getTile($this->position);
            if ($tile instanceof TileCampfire && $tile->addItem(clone $item)) {
                $item->pop();
                $this->position->getWorld()->setBlock($this->position, $this);
                $this->position->getWorld()->addSound($this->position, new ItemFrameAddItemSound());
                if (count($tile->getContents()) == 1) $this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 20);
                return true;
            }
        }

        return false;
    }

    public function onNearbyBlockChange(): void
    {
        $block = $this->getSide(Facing::UP);
        if ($block instanceof Water && !$this->extinguished) {
            $this->extinguish();
        }
    }

    public function onEntityInside(Entity $entity): bool
    {
        if ($this->extinguished) {
            return false;
        }
        if ($entity instanceof SplashPotion && $entity->getPotionType()->getDisplayName() == PotionType::WATER()->getDisplayName()) {
            $this->extinguish();
            return true;
        } elseif ($entity instanceof Player && $entity->isSurvival()) {
            $entity->attack(new EntityDamageByBlockEvent($this, $entity, EntityDamageEvent::CAUSE_FIRE, $this->isSoul() ? 2 : 1));
            $entity->setOnFire(8);
        }
        return false;
    }

    public function onScheduledUpdate(): void
    {
        $tile = $this->position->getWorld()->getTile($this->position);

        if ($tile instanceof TileCampfire && !$tile->closed) {
            if (!$this->extinguished) {
                foreach ($tile->getContents() as $slot => $item) {
                    $tile->increaseSlotTime($slot);

                    if ($tile->getItemTime($slot) >= TileCampfire::MAX_COOK_TIME) {
                        $tile->setItem(ItemFactory::air(), $slot);
                        $tile->setSlotTime($slot, 0);
                        $this->position->world->setBlock($this->position, $this);

                        $result = ItemFactory::getInstance()->get($tile->getRecipes()[$item->getId()] ?? $item->getId());
                        $this->position->getWorld()->dropItem($this->position->add(0, 1, 0), $result);
                    }
                }
                if (!empty($tile->getContents())) $this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 20);
            }
        }
    }
}