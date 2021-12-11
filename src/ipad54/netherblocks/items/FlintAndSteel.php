<?php

namespace ipad54\netherblocks\items;

use ipad54\netherblocks\utils\CustomIds;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\SoulSand;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\FlintSteel;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\sound\FlintSteelSound;

class FlintAndSteel extends FlintSteel {

    public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : ItemUseResult{
        //var_dump($blockReplace->getSide(Facing::DOWN));
        if($blockReplace->getId() === BlockLegacyIds::AIR){
            $world = $player->getWorld();
            $side = $blockReplace->getSide(Facing::DOWN);
            $world->setBlock($blockReplace->getPosition(), ($side instanceof SoulSand or $side->getId() == CustomIds::SOUL_SOIL_BLOCK) ? BlockFactory::getInstance()->get(CustomIds::SOUL_FIRE_BLOCK, 0) : VanillaBlocks::FIRE());
            $world->addSound($blockReplace->getPosition()->add(0.5, 0.5, 0.5), new FlintSteelSound());

            $this->applyDamage(1);

            return ItemUseResult::SUCCESS();
        }

        return ItemUseResult::NONE();
    }
}