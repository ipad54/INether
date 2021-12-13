<?php

namespace ipad54\netherblocks\utils;

use pocketmine\utils\Config;

class CustomConfig
{

    public function __construct(private Config $config)
    {
    }

    public function match(string $key): bool
    {
        $value = $this->config->getNested($key, true);
        return boolval($value);
    }

    public function isEnableCryingObsidian()
    {
        return $this->match("blocks.crying_obsidian");
    }

    public function isEnableNetherwart()
    {
        return $this->match("blocks.nether_wart");
    }

    public function isEnableSoulTorch()
    {
        return $this->match("blocks.soul_torch");
    }

    public function isEnableSoullantern()
    {
        return $this->match("blocks.soul_lantern");
    }

    public function isEnableShroomlight()
    {
        return $this->match("blocks.shroomlight");
    }

    public function isEnableNetheriteBlock()
    {
        return $this->match("blocks.netherite_block");
    }

    public function isEnabledNylium()
    {
        return $this->match("blocks.nylium");
    }

    public function isEnabledSoulSoil()
    {
        return $this->match("blocks.soul_soil");
    }

    public function isEnabledFungus()
    {
        return $this->match("blocks.fungus");
    }

    public function isEnabledBasalt()
    {
        return $this->match("blocks.basalt");
    }

    public function isEnabledDebris()
    {
        return $this->match("blocks.ancient_debris");
    }

    public function isEnableTarget()
    {
        return $this->match("blocks.target");
    }

    public function isEnableGoldOre()
    {
        return $this->match("blocks.nether_gold_ore");
    }

    public function isEnabledRespawnAnchor()
    {
        return $this->match("blocks.respawn_anchor");
    }

    public function isEnableBlackstone()
    {
        return $this->match("blocks.blackstone");
    }

    public function isEnableStairs()
    {
        return $this->match("blocks.stairs");
    }

    public function isEnableWood()
    {
        return $this->match("blocks.wood");
    }

    public function isEnableRoots()
    {
        return $this->match("blocks.roots");
    }

    public function isEnableVines()
    {
        return $this->match("blocks.vines");
    }

    public function isEnableChain()
    {
        return $this->match("blocks.chain");
    }

    public function isEnableCampfire()
    {
        return $this->match("blocks.campfire");
    }

    public function isEnableNetheriteTools()
    {
        return $this->match("items.netherite_tools");
    }

    public function isEnablePigstep()
    {
        return $this->match("items.pigstep");
    }
}
    
    