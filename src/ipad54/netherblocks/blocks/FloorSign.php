<?php

namespace ipad54\netherblocks\blocks;


use ipad54\netherblocks\utils\CustomIds;
use pocketmine\block\BaseSign;
use pocketmine\block\Block;
use pocketmine\block\utils\SignLikeRotationTrait;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\BlockTransaction;

class FloorSign extends BaseSign {//TODO: FloorSign is final in PM:(
	use SignLikeRotationTrait;

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->rotation = $stateMeta;
	}

	protected function writeStateToMeta() : int{
		return $this->rotation;
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	protected function getSupportingFace() : int{
		return Facing::DOWN;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($face !== Facing::UP){
			return false;
		}

		if($player !== null){
			$this->rotation = self::getRotationFromYaw($player->getLocation()->getYaw());
		}
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function getDrops(Item $item): array
	{
		return match($this->getId()){
			CustomIds::CRIMSON_FLOOR_SIGN_BLOCK => [ItemFactory::getInstance()->get(CustomIds::CRIMSON_SIGN)],
			CustomIds::WARPED_FLOOR_SIGN_BLOCK => [ItemFactory::getInstance()->get(CustomIds::WARPED_SIGN)],
			default => throw new AssumptionFailedError("Unreachable")
		};
	}
}