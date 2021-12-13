<?php

namespace ipad54\netherblocks\tile;

use Exception;
use pocketmine\block\tile\Container;
use pocketmine\block\tile\Spawnable;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\nbt\NBT;
use pocketmine\nbt\NbtDataException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;

class Campfire extends Spawnable
{

    public const TAG_ITEM_TIME = "ItemTimes";

    public const MAX_COOK_TIME = 30;

    /** @var int[] */
    private array $recipes = [
        ItemIds::RAW_BEEF => ItemIds::COOKED_BEEF,
        ItemIds::RAW_CHICKEN => ItemIds::COOKED_CHICKEN,
        ItemIds::RAW_PORKCHOP => ItemIds::COOKED_PORKCHOP,
        ItemIds::RAW_MUTTON => ItemIds::COOKED_MUTTON,
        ItemIds::RAW_FISH => ItemIds::COOKED_FISH,
        ItemIds::RAW_SALMON => ItemIds::COOKED_SALMON,
        ItemIds::POTATO => ItemIds::BAKED_POTATO,
        ItemIds::KELP => ItemIds::DRIED_KELP
    ];

    /** @var Item[] */
    private array $items = [];
    /** @var int[] */
    private array $itemTime = [];

    public function close(): void
    {
        foreach ($this->items as $item) {
            $this->position->getWorld()->dropItem($this->position->add(0, 1, 0), $item);
        }
        $this->items = [];
        parent::close();
    }


    public function setItem(Item $item, ?int $slot = null): void
    {
        if ($slot === null) {
            $slot = count($this->items) + 1;
        }
        if ($slot < 1 or $slot > 4) {
            throw new Exception("Slot must be in range 0-4, got " . $slot);
        }
        if ($item->isNull()) {
            if (isset($this->items[$slot])) unset($this->items[$slot]);
        } else {
            $this->items[$slot] = $item;
        }
    }


    public function addItem(Item $item): bool
    {
        $item->setCount(1);
        if (!$this->canAddItem($item)) {
            return false;
        }
        $this->setItem($item);
        return true;
    }

    public function canCook(Item $item): bool
    {
        return isset($this->recipes[$item->getId()]);
    }

    public function canAddItem(Item $item): bool
    {
        if (count($this->items) >= 4) {
            return false;
        }
        return $this->canCook($item);
    }

    public function setSlotTime(int $slot, int $time): void
    {
        $this->itemTime[$slot] = $time;
    }

    public function increaseSlotTime(int $slot): void
    {
        $this->setSlotTime($slot, $this->getItemTime($slot) + 1);
    }

    public function getItemTime(int $slot): int
    {
        return $this->itemTime[$slot] ?? 0;
    }

    /**
     * @return int[]
     */
    public function getRecipes(): array
    {
        return $this->recipes;
    }

    /**
     * @return Item[]
     */
    public function getContents(): array
    {
        return $this->items;
    }

    public function readSaveData(CompoundTag $nbt): void
    {
        if (($tag = $nbt->getTag(Container::TAG_ITEMS)) !== null) {
            $inventoryTag = $tag->getValue();

            /** @var CompoundTag $itemNBT */
            foreach ($inventoryTag as $itemNBT) {
                $this->setItem(Item::nbtDeserialize($itemNBT), $itemNBT->getByte("Slot"));
            }
        }

        if (($tag = $nbt->getTag(self::TAG_ITEM_TIME)) !== null) {
            /** @var IntTag $time */
            foreach ($tag->getValue() as $slot => $time) {
                $this->itemTime[$slot + 1] = $time->getValue();
            }
        }
    }

    protected function writeSaveData(CompoundTag $nbt): void
    {
        $items = [];
        foreach ($this->getContents() as $slot => $item) {
            $items[] = $item->nbtSerialize($slot);
        }
        $nbt->setTag(Container::TAG_ITEMS, new ListTag($items, NBT::TAG_Compound));

        $times = [];
        foreach ($this->itemTime as $time) {
            $times[] = new IntTag($time);
        }
        $nbt->setTag(self::TAG_ITEM_TIME, new ListTag($times));
    }

    protected function addAdditionalSpawnData(CompoundTag $nbt): void
    {
        foreach ($this->items as $slot => $item) {
            $nbt->setTag("Item" . $slot, $item->nbtSerialize());
            $nbt->setInt("ItemTime" . $slot, $this->getItemTime($slot));
        }
    }
}