<?php

namespace ipad54\netherblocks\blocks;


use ipad54\netherblocks\utils\CustomIds;
use pocketmine\block\BaseSign;
use pocketmine\block\Block;
use pocketmine\block\utils\NormalHorizontalFacingInMetadataTrait;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\BlockTransaction;

class WallSign extends BaseSign{ //TODO: WallSign is final in PM:(
	use NormalHorizontalFacingInMetadataTrait;

	protected function getSupportingFace() : int{
		return Facing::opposite($this->facing);
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if(Facing::axis($face) === Axis::Y){
			return false;
		}
		$this->facing = $face;
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function getDrops(Item $item): array
	{
		return match($this->getId()){
			CustomIds::CRIMSON_WALL_SIGN_BLOCK => [ItemFactory::getInstance()->get(CustomIds::CRIMSON_SIGN)],
			CustomIds::WARPED_WALL_SIGN_BLOCK => [ItemFactory::getInstance()->get(CustomIds::WARPED_SIGN)],
			default => throw new AssumptionFailedError("Unreachable")
		};
	}
}