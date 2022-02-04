<?php

namespace ipad54\netherblocks\blocks;

use pocketmine\block\Slab as PMSlab;
use pocketmine\block\utils\SlabType;

class Slab extends PMSlab
{
	public const SLAB_FLAG_UPPER = 0x01;

	protected function writeStateToMeta(): int
	{
		if (!$this->slabType->equals(SlabType::DOUBLE())) {
			return ($this->slabType->equals(SlabType::TOP()) ? self::SLAB_FLAG_UPPER : 0);
		}
		return 0;
	}

	public function readStateFromData(int $id, int $stateMeta): void
	{
		if ($id === $this->idInfoFlattened->getSecondId()) {
			$this->slabType = SlabType::DOUBLE();
		} else {
			$this->slabType = ($stateMeta & self::SLAB_FLAG_UPPER) !== 0 ? SlabType::TOP() : SlabType::BOTTOM();
		}
	}

	public function getStateBitmask(): int
	{
		return 0b1;
	}
}