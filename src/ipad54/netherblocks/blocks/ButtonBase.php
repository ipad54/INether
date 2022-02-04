<?php

namespace ipad54\netherblocks\blocks;

use pocketmine\block\Button;

abstract class ButtonBase extends Button{ //credits: https://github.com/CLADevs/VanillaX/blob/4.0/src/CLADevs/VanillaX/blocks/block/button/NewWoodenButton.php for metadata values
	public const BUTTON_FLAG_POWERED = 0x06;

	public function readStateFromData(int $id, int $stateMeta): void{
		$this->facing = $stateMeta;
		$this->pressed = $stateMeta >= self::BUTTON_FLAG_POWERED;
	}

	protected function writeStateToMeta(): int{
		$state = $this->facing;

		if($this->facing >= self::BUTTON_FLAG_POWERED){
			if(!$this->pressed){
				$state -= self::BUTTON_FLAG_POWERED;
			}
		}elseif($this->pressed){
			$state += self::BUTTON_FLAG_POWERED;
		}
		return $state;
	}
}