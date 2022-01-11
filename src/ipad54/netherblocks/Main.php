<?php

namespace ipad54\netherblocks;


use ipad54\netherblocks\blocks\Basalt;
use ipad54\netherblocks\blocks\Blackstone;
use ipad54\netherblocks\blocks\Campfire;
use ipad54\netherblocks\blocks\Chain;
use ipad54\netherblocks\blocks\ChiseledPolishedBlackstone;
use ipad54\netherblocks\blocks\CryingObsidian;
use ipad54\netherblocks\blocks\Fungus;
use ipad54\netherblocks\blocks\GildedBlackstone;
use ipad54\netherblocks\blocks\Hyphae;
use ipad54\netherblocks\blocks\Log;
use ipad54\netherblocks\blocks\NetherGoldOre;
use ipad54\netherblocks\blocks\NetherSprouts;
use ipad54\netherblocks\blocks\NetherWartBlock;
use ipad54\netherblocks\blocks\Nylium;
use ipad54\netherblocks\blocks\Planks;
use ipad54\netherblocks\blocks\PolishedBasalt;
use ipad54\netherblocks\blocks\PolishedBlackStone;
use ipad54\netherblocks\blocks\RespawnAnchor;
use ipad54\netherblocks\blocks\Roots;
use ipad54\netherblocks\blocks\Shroomlight;
use ipad54\netherblocks\blocks\SoulCampfire;
use ipad54\netherblocks\blocks\SoulFire;
use ipad54\netherblocks\blocks\SoulLantern;
use ipad54\netherblocks\blocks\SoulSoil;
use ipad54\netherblocks\blocks\SoulTorch;
use ipad54\netherblocks\blocks\Target;
use ipad54\netherblocks\blocks\TwistingVines;
use ipad54\netherblocks\blocks\WarpedFungus;
use ipad54\netherblocks\blocks\WarpedNylium;
use ipad54\netherblocks\blocks\WarpedWartBlock;
use ipad54\netherblocks\blocks\WeepingVines;
use ipad54\netherblocks\blocks\Wood;
use ipad54\netherblocks\tile\Campfire as TileCampfire;
use ipad54\netherblocks\items\FlintAndSteel;
use ipad54\netherblocks\listener\EventListener;
use ipad54\netherblocks\utils\CustomConfig;
use ipad54\netherblocks\utils\CustomIds;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds as Ids;
use pocketmine\block\BlockIdentifier as BID;
use pocketmine\block\BlockToolType;
use pocketmine\block\Door;
use pocketmine\block\Fence;
use pocketmine\block\FenceGate;
use pocketmine\block\Opaque;
use pocketmine\block\Stair;
use pocketmine\block\tile\TileFactory;
use pocketmine\block\utils\RecordType;
use pocketmine\block\utils\TreeType;
use pocketmine\block\WoodenTrapdoor;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\ItemBlock;
use pocketmine\lang\Translatable;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\Armor;
use pocketmine\item\ArmorTypeInfo;
use pocketmine\item\Axe;
use pocketmine\item\Hoe;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\Pickaxe;
use pocketmine\item\Shovel;
use pocketmine\item\Sword;
use pocketmine\item\Record;
use pocketmine\item\ToolTier;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Config;
use ReflectionMethod;
use const pocketmine\BEDROCK_DATA_PATH;

class Main extends PluginBase
{

    private CustomConfig $config;
    private static self $instance;

    protected function onLoad(): void
    {
        self::$instance = $this;
        $this->saveResource("config.yml");
        $this->config = new CustomConfig(new Config($this->getDataFolder() . "config.yml", Config::YAML));
        self::initializeRuntimeIds();
        $this->initBlocks();
        $this->initTiles();
        $this->initItems();
    }

    protected function onEnable(): void
    { //credits https://github.com/cladevs/VanillaX
        Server::getInstance()->getPluginManager()->registerEvents(new EventListener(), $this);
        Server::getInstance()->getAsyncPool()->addWorkerStartHook(function (int $worker): void {
            Server::getInstance()->getAsyncPool()->submitTaskToWorker(new class() extends AsyncTask {

                public function onRun(): void
                {
                    Main::initializeRuntimeIds();
                }
            }, $worker);
        });
    }


    public static function getInstance(): self
    {
        return self::$instance;
    }

    public function getCustomConfig(): CustomConfig
    {
        return $this->config;
    }

    public static function initializeRuntimeIds(): void{
        $instance = RuntimeBlockMapping::getInstance();
        $method = new ReflectionMethod(RuntimeBlockMapping::class, "registerMapping");
        $method->setAccessible(true);

        $blockIdMap = json_decode(file_get_contents(BEDROCK_DATA_PATH . 'block_id_map.json'), true);
        $metaMap = [];

        foreach($instance->getBedrockKnownStates() as $runtimeId => $nbt){
            $mcpeName = $nbt->getString("name");
            $meta = isset($metaMap[$mcpeName]) ? ($metaMap[$mcpeName] + 1) : 0;
            $id = $blockIdMap[$mcpeName] ?? Ids::AIR;

            if($id !== Ids::AIR && $meta <= 15 && !BlockFactory::getInstance()->isRegistered($id, $meta)){
                $metaMap[$mcpeName] = $meta;
                $method->invoke($instance, $runtimeId, $id, $meta);
            }
        }
    }




    public function initBlocks(): void
    {
        $class = new \ReflectionClass(TreeType::class);
        $register = $class->getMethod('register');
        $register->setAccessible(true);
        $constructor = $class->getConstructor();
        $constructor->setAccessible(true);
        $instance = $class->newInstanceWithoutConstructor();
        $constructor->invoke($instance, 'crimson', 'Crimson', 6);
        $register->invoke(null, $instance);

        $instance = $class->newInstanceWithoutConstructor();
        $constructor->invoke($instance, 'warped', 'Warped', 7);
        $register->invoke(null, $instance);

        $bf = BlockFactory::getInstance();
        $cfg = $this->getCustomConfig();
        if ($cfg->isEnabledDebris()) {
            $bf->register(new Opaque(new BID(CustomIds::ANCIENT_DEBRIS_BLOCK, 0, CustomIds::ANCIENT_DEBRIS_ITEM), "Ancient Debris", new BlockBreakInfo(30, BlockToolType::PICKAXE, ToolTier::DIAMOND()->getHarvestLevel(), 6000)), true);
        }
        if ($cfg->isEnabledBasalt()) {
            $bf->register(new Basalt(new BID(CustomIds::BASALT_BLOCK, 0, CustomIds::BASALT_ITEM), "Basalt", new BlockBreakInfo(1.25, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 4.2)), true);
            $bf->register(new PolishedBasalt(new BID(CustomIds::POLISHED_BASALT_BLOCK, 0, CustomIds::POLISHED_BASALT_ITEM), "Polished Basalt", new BlockBreakInfo(1.25, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 4.2)), true);
        }
        if ($cfg->isEnabledFungus()) {
            $bf->register(new Fungus(new BID(CustomIds::FUNGUS_BLOCK, 0, CustomIds::FUNGUS_ITEM), "Crimson Fungus", BlockBreakInfo::instant()), true);
            $bf->register(new WarpedFungus(new BID(CustomIds::WARPED_FUNGUS_BLOCK, 0, CustomIds::WARPED_FUNGUS_ITEM), "Warped Fungus", BlockBreakInfo::instant()), true);
        }
        if ($cfg->isEnabledSoulSoil()) {
            $bf->register(new SoulSoil(new BID(CustomIds::SOUL_SOIL_BLOCK, 0, CustomIds::SOUL_SOIL_ITEM), "Soul Soil", new BlockBreakInfo(0.5, BlockToolType::SHOVEL)), true);
            $bf->register(new SoulFire(new BID(CustomIds::SOUL_FIRE_BLOCK, 0, CustomIds::SOUL_FIRE_ITEM), "Soul Fire", BlockBreakInfo::instant()), true);
        }
        if ($cfg->isEnabledNylium()) {
            $bf->register(new Nylium(new BID(CustomIds::NYLIUM_BLOCK, 0, CustomIds::NYLIUM_ITEM), "Crimson Nylium", new BlockBreakInfo(1, BlockToolType::PICKAXE)), true);
            $bf->register(new WarpedNylium(new BID(CustomIds::WARPED_NYLIUM_BLOCK, 0, CustomIds::WARPED_NYLIUM_ITEM), "Warped Nylium", new BlockBreakInfo(1, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())), true);
        }
        if ($cfg->isEnableNetheriteBlock()) {
            $bf->register(new Opaque(new BID(CustomIds::NETHERITE_BLOCK, 0, CustomIds::NETHERITE_BLOCK_ITEM), "Netherite Block", new BlockBreakInfo(50, BlockToolType::PICKAXE, ToolTier::DIAMOND()->getHarvestLevel(), 6000)), true);
        }
        //$bf->register(new NetherSprouts(new BID(CustomIds::NETHER_SPROUTS_BLOCK, 0, CustomIds::NETHER_SPROUTS_ITEM), "Nether Sprouts", BlockBreakInfo::instant(BlockToolType::SHEARS)));
        if ($cfg->isEnableShroomlight()) {
            $bf->register(new Shroomlight(new BID(CustomIds::SHROOMLIGHT_BLOCK, 0, CustomIds::SHROOMLIGHT_ITEM), "Shroomlight", new BlockBreakInfo(1, BlockToolType::HOE)), true);
        }
        if ($cfg->isEnableSoullantern()) {
            $bf->register(new SoulLantern(new BID(CustomIds::SOUL_LANTERN_BLOCK, 0, CustomIds::SOUL_LANTERN_ITEM), "Soul Lantern", new BlockBreakInfo(3.5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 3.5)), true);
        }
        if ($cfg->isEnableSoulTorch()) {
            $bf->register(new SoulTorch(new BID(CustomIds::SOUL_TORCH_BLOCK, 0, CustomIds::SOUL_TORCH_ITEM), "Soul Torch", BlockBreakInfo::instant()), true);
        }
        if ($cfg->isEnableNetherwart()) {
            $bf->register(new NetherWartBlock(new BID(CustomIds::NETHER_WART_BLOCK, 0), "Nether Wart Block", new BlockBreakInfo(1, BlockToolType::HOE, 0, 5)), true);
            $bf->register(new WarpedWartBlock(new BID(CustomIds::WARPED_WART_BLOCK, 0, CustomIds::WARPED_WART_ITEM), "Warped Wart Block", new BlockBreakInfo(1, BlockToolType::HOE, 0, 5)), true);
        }
        if ($cfg->isEnableCryingObsidian()) {
            $bf->register(new CryingObsidian(new BID(CustomIds::CRYING_OBSIDIAN_BLOCK, 0, CustomIds::CRYING_OBSIDIAN_ITEM), "Crying Obsidian", new BlockBreakInfo(50, BlockToolType::PICKAXE, ToolTier::DIAMOND()->getHarvestLevel(), 6000)), true);
        }
        if ($cfg->isEnableTarget()) {
            $bf->register(new Target(new BID(CustomIds::TARGET_BLOCK, 0, CustomIds::TARGET_ITEM), "Target", BlockBreakInfo::instant(BlockToolType::HOE)), true);
        }
        if ($cfg->isEnableGoldOre()) {
            $bf->register(new NetherGoldOre(new BID(CustomIds::NETHER_GOLD_ORE_BLOCK, 0, CustomIds::NETHER_GOLD_ORE_ITEM), "Nether Gold Ore", new BlockBreakInfo(3, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 3)), true);
        }
        if ($cfg->isEnabledRespawnAnchor()) {
            $bf->register(new RespawnAnchor(new BID(CustomIds::RESPAWN_ANCHOR_BLOCK, 0, CustomIds::RESPAWN_ANCHOR_ITEM), "Respawn Anchor", new BlockBreakInfo(50, BlockToolType::PICKAXE, ToolTier::DIAMOND()->getHarvestLevel(), 6000)), true);
        }

        if ($cfg->isEnableBlackstone()) {

            $blackstoneBreakInfo = new BlockBreakInfo(1.5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 6);

            $bf->register(new Blackstone(new BID(CustomIds::BLACKSTONE_BLOCK, 0, CustomIds::BLACKSTONE_ITEM), "Blackstone", $blackstoneBreakInfo), true);
            $bf->register(new PolishedBlackStone(new BID(CustomIds::POLISHED_BLACKSTONE_BLOCK, 0, CustomIds::POLISHED_BLACKSTONE_ITEM), "Polished Blackstone", $blackstoneBreakInfo), true);
            $bf->register(new ChiseledPolishedBlackstone(new BID(CustomIds::CHISELED_POLISHED_BLACKSTONE_BLOCK, 0, CustomIds::CHISELED_POLISHED_BLACKSTONE_ITEM), "Chiseled Polished Blackstone", $blackstoneBreakInfo), true);
            $bf->register(new GildedBlackstone(new BID(CustomIds::GILDED_BLACKSTONE_BLOCK, 0, CustomIds::GILDED_BLACKSTONE_ITEM), "Gilded Blackstone", $blackstoneBreakInfo), true);
        }
        if ($cfg->isEnableChain()) {
            $bf->register(new Chain(new BID(CustomIds::CHAIN_BLOCK, 0, CustomIds::CHAIN_BLOCK_ITEM), "Chain", new BlockBreakInfo(5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 6)), true);
        }
        if ($cfg->isEnableVines()) {
            $bf->register(new TwistingVines(new BID(CustomIds::TWISTING_VINES_BLOCK, 0, CustomIds::TWISTING_VINES_ITEM), "Twisting Vines", BlockBreakInfo::instant()), true);
            $bf->register(new WeepingVines(new BID(CustomIds::WEEPING_VINES_BLOCK, 0, CustomIds::WEEPING_VINES_ITEM), "Weeping Vines", BlockBreakInfo::instant()), true);
        }
        if ($cfg->isEnableRoots()) {
            $bf->register(new Roots(new BID(CustomIds::CRIMSON_ROOTS_BLOCK, 0, CustomIds::CRIMSON_ROOTS_ITEM), "Crimson Roots", BlockBreakInfo::instant()), true);
            $bf->register(new Roots(new BID(CustomIds::WARPED_ROOTS_BLOCK, 0, CustomIds::WARPED_ROOTS_ITEM), "Warped Roots", BlockBreakInfo::instant()), true);
        }
        if ($cfg->isEnableWood()) {
            $bf->register(new Planks(new BID(CustomIds::CRIMSON_PLANKS_BLOCK, 0, CustomIds::CRIMSON_PLANKS_ITEM), "Crimson Planks", new BlockBreakInfo(2, BlockToolType::AXE, 0, 3)), true);
            $bf->register(new Planks(new BID(CustomIds::WARPED_PLANKS_BLOCK, 0, CustomIds::WARPED_PLANKS_ITEM), "Warped Planks", new BlockBreakInfo(2, BlockToolType::AXE, 0, 3)), true);
            $bf->register(new Wood(new BID(CustomIds::CRIMSON_STEM_BLOCK, 0, CustomIds::CRIMSON_STEM_ITEM), "Crimson Stem", new BlockBreakInfo(2, BlockToolType::AXE, 0, 10), TreeType::CRIMSON(), false), true);
            $bf->register(new Wood(new BID(CustomIds::WARPED_STEM_BLOCK, 0, CustomIds::WARPED_STEM_ITEM), "Warped Stem", new BlockBreakInfo(2, BlockToolType::AXE, 0, 10), TreeType::WARPED(), false), true);
            $bf->register(new Log(new BID(CustomIds::CRIMSON_STRIPPED_STEM_BLOCK, 0, CustomIds::CRIMSON_STRIPPED_STEM_ITEM), "Crimson Stripped Stem", new BlockBreakInfo(2, BlockToolType::AXE, 0, 10), TreeType::CRIMSON(), true), true);
            $bf->register(new Log(new BID(CustomIds::WARPED_STRIPPED_STEM_BLOCK, 0, CustomIds::WARPED_STRIPPED_STEM_ITEM), "Warped Stripped Stem", new BlockBreakInfo(2, BlockToolType::AXE, 0, 10), TreeType::WARPED(), true));

            $bf->register(new Hyphae(new BID(CustomIds::CRIMSON_HYPHAE_BLOCK, 0, CustomIds::CRIMSON_HYPHAE_ITEM), "Crimson Hyphae", new BlockBreakInfo(2, BlockToolType::AXE, 0, 10), TreeType::CRIMSON(), false), true);
            $bf->register(new Hyphae(new BID(CustomIds::WARPED_HYPHAE_BLOCK, 0, CustomIds::WARPED_HYPHAE_ITEM), "Warped Hyphae", new BlockBreakInfo(2, BlockToolType::AXE, 0, 10), TreeType::WARPED(), false));

            $bf->register(new Hyphae(new BID(CustomIds::CRIMSON_STRIPPED_HYPHAE_BLOCK, 0, CustomIds::CRIMSON_STRIPPED_HYPHAE_ITEM), "Crimson Stripped Hyphae", new BlockBreakInfo(2, BlockToolType::AXE, 0, 10), TreeType::CRIMSON(), true), true);
            $bf->register(new Hyphae(new BID(CustomIds::WARPED_STRIPPED_HYPHAE_BLOCK, 0, CustomIds::WARPED_STRIPPED_HYPHAE_ITEM), "Warped Stripped Hyphae", new BlockBreakInfo(2, BlockToolType::AXE, 0, 10), TreeType::WARPED(), true), true);
            $bf->register(new Door(new BID(CustomIds::CRIMSON_DOOR_BLOCK, 0, CustomIds::CRIMSON_DOOOR_ITEM), "Crimson Door", new BlockBreakInfo(3, BlockToolType::AXE)), true);
            $bf->register(new Door(new BID(CustomIds::WARPED_DOOR_BLOCK, 0, CustomIds::WARPED_DOOR_ITEM), "Warped Door", new BlockBreakInfo(3, BlockToolType::AXE)), true);
            $bf->register(new Fence(new BID(CustomIds::CRIMSON_FENCE_BLOCK, 0, CustomIds::CRIMSON_FENCE_ITEM), "Crimson Fence", new BlockBreakInfo(2, BlockToolType::AXE, 0, 3)), true);
            $bf->register(new Fence(new BID(CustomIds::WARPED_FENCE_BLOCK, 0, CustomIds::WARPED_FENCE_ITEM), "Warped Fence", new BlockBreakInfo(2, BlockToolType::AXE, 0, 3)), true);
            $bf->register(new FenceGate(new BID(CustomIds::CRIMSON_FENCE_GATE_BLOCK, 0, CustomIds::CRIMSON_FENCE_GATE_ITEM), "Crimson Fence Gate", new BlockBreakInfo(2, BlockToolType::AXE, 0, 3)), true);
            $bf->register(new FenceGate(new BID(CustomIds::WARPED_FENCE_GATE_BLOCK, 0, CustomIds::WARPED_FENCE_GATE_ITEM), "Warped Fence Gate", new BlockBreakInfo(2, BlockToolType::AXE, 0, 3)), true);
            $bf->register(new WoodenTrapdoor(new BID(CustomIds::CRIMSON_TRAPDOOR_BLOCK, 0, CustomIds::CRIMSON_TRAPDOOR_ITEM), "Crimson Trapdoor", new BlockBreakInfo(3, BlockToolType::AXE, 0, 15)), true);
            $bf->register(new WoodenTrapdoor(new BID(CustomIds::WARPED_TRAPDOOR_BLOCK, 0, CustomIds::WARPED_TRAPDOOR_ITEM), "Warped Trapdoor", new BlockBreakInfo(3, BlockToolType::AXE, 0, 15)), true);
        }
        if ($cfg->isEnableStairs()) {
            $bf->register(new Stair(new BID(CustomIds::BLACKSTONE_STAIRS_BLOCK, 0, CustomIds::BLACKSTONE_STAIRS_ITEM), "Blackstone Stairs", new BlockBreakInfo(3, BlockToolType::AXE, 0, 6)), true);
            $bf->register(new Stair(new BID(CustomIds::CRIMSON_STAIRS_BLOCK, 0, CustomIds::CRIMSON_STAIRS_ITEM), "Crimson Stairs", new BlockBreakInfo(3, BlockToolType::AXE, 0, 6)), true);
            $bf->register(new Stair(new BID(CustomIds::POLISHED_BLACKSTONE_STAIRS_BLOCK, 0, CustomIds::POLISHED_BLACKSTONE_STAIRS_ITEM), "Polished Blackstone Stairs", new BlockBreakInfo(3, BlockToolType::AXE, 0, 6)), true);
            $bf->register(new Stair(new BID(CustomIds::WARPED_STAIRS_BLOCK, 0, CustomIds::WARPED_STAIRS_ITEM), "Warped Stairs", new BlockBreakInfo(3, BlockToolType::AXE, 0, 6)), true);
            $bf->register(new Stair(new BID(CustomIds::POLISHED_BLACKSTONE_BRICK_STAIRS_BLOCK, 0, CustomIds::POLISHED_BLACKSTONE_BRICK_STAIRS_ITEM), "Polished Blackstone Brick Stairs", new BlockBreakInfo(3, BlockToolType::AXE, 0, 6)), true);
            $bf->register(new Stair(new BID(CustomIds::POLISHED_BLACKSTONE_STAIRS_BLOCK, 0, CustomIds::POLISHED_BLACKSTONE_STAIRS_ITEM), "Polished Blackstone Stairs", new BlockBreakInfo(3, BlockToolType::AXE, 0, 6)), true);
            $bf->register(new Stair(new BID(CustomIds::WARPED_STAIRS_BLOCK, 0, CustomIds::WARPED_STAIRS_ITEM), "Warped Stairs", new BlockBreakInfo(3, BlockToolType::AXE, 0, 6)), true);
        }
        if($cfg->isEnableCampfire()){
            $bf->register(new Campfire(new BID(Ids::CAMPFIRE, 0, CustomIds::CAMPFIRE_ITEM, TileCampfire::class), "Campfire", new BlockBreakInfo(2, BlockToolType::AXE, 0, 10)));
            $bf->register(new SoulCampfire(new BID(CustomIds::SOUL_CAMPFIRE_BLOCK, 0, CustomIds::SOUL_CAMPFIRE_ITEM, TileCampfire::class), "Soul Campfire", new BlockBreakInfo(2, BlockToolType::AXE, 0, 10)));
        }

    }

    public function initTiles() : void{
        $cfg = $this->getCustomConfig();
        $tf = TileFactory::getInstance();
        if($cfg->isEnableCampfire()){
            $tf->register(TileCampfire::class, ["Campfire", "minecraft:campfire"]);
        }
    }

    public function initItems(): void
    {
        $class = new \ReflectionClass(ToolTier::class);
        $register = $class->getMethod('register');
        $register->setAccessible(true);
        $constructor = $class->getConstructor();
        $constructor->setAccessible(true);
        $instance = $class->newInstanceWithoutConstructor();
        $constructor->invoke($instance, 'netherite', 6, 2031, 9, 10);
        $register->invoke(null, $instance);

        $class = new \ReflectionClass(RecordType::class);
        $register = $class->getMethod('register');
        $register->setAccessible(true);
        $constructor = $class->getConstructor();
        $constructor->setAccessible(true);
        $instance = $class->newInstanceWithoutConstructor();
        $constructor->invoke($instance, 'disk_pigstep', 'Lena Raine - Pigstep', CustomIds::RECORD_PIGSTEP_SOUND_ID, new Translatable('item.record_pigstep.desc', []));
        $register->invoke(null, $instance);

        $factory = ItemFactory::getInstance();
        $cfg = $this->getCustomConfig();
        if ($cfg->isEnabledSoulSoil()) {
            $factory->register(new FlintAndSteel(new ItemIdentifier(ItemIds::FLINT_AND_STEEL, 0), "Flint and Steel"), true);
        }
        if ($cfg->isEnablePigstep()) {
            $factory->register(new Record(new ItemIdentifier(CustomIds::RECORD_PIGSTEP, 0), RecordType::DISK_PIGSTEP(), "Pigstep"), true);
        }
        if($cfg->isEnableWood()){
            $factory->register(new ItemBlock(new ItemIdentifier(CustomIds::CRIMSON_DOOOR_ITEM, 0), BlockFactory::getInstance()->get(CustomIds::CRIMSON_DOOR_BLOCK, 0)), true);
            $factory->register(new ItemBlock(new ItemIdentifier(CustomIds::WARPED_DOOR_ITEM, 0), BlockFactory::getInstance()->get(CustomIds::WARPED_DOOR_BLOCK, 0)), true);
        }
        if($cfg->isEnableCampfire()){
            $factory->register(new ItemBlock(new ItemIdentifier(CustomIds::CAMPFIRE_ITEM, 0), BlockFactory::getInstance()->get(Ids::CAMPFIRE, 0)), true);
            $factory->register(new ItemBlock(new ItemIdentifier(CustomIds::SOUL_CAMPFIRE_ITEM, 0), BlockFactory::getInstance()->get(CustomIds::SOUL_CAMPFIRE_BLOCK, 0)), true);
        }
        if($cfg->isEnableChain()){
            $factory->register(new ItemBlock(new ItemIdentifier(CustomIds::CHAIN_ITEM, 0), BlockFactory::getInstance()->get(CustomIds::CHAIN_BLOCK, 0)), true);
        }
        if ($cfg->isEnableNetheriteTools()) {
            $factory->register(new Item(new ItemIdentifier(CustomIds::ITEM_NETHERITE_INGOT, 0), 'Netherite Ingot'), true);
            $factory->register(new Item(new ItemIdentifier(CustomIds::ITEM_NETHERITE_SCRAP, 0), 'Netherite Scrap'), true);
            $factory->register(new Sword(new ItemIdentifier(CustomIds::ITEM_NETHERITE_SWORD, 0), 'Netherite Sword', ToolTier::NETHERITE()), true);
            $factory->register(new Shovel(new ItemIdentifier(CustomIds::ITEM_NETHERITE_SHOVEL, 0), 'Netherite Shovel', ToolTier::NETHERITE()), true);
            $factory->register(new Pickaxe(new ItemIdentifier(CustomIds::ITEM_NETHERITE_PICKAXE, 0), 'Netherite Pickaxe', ToolTier::NETHERITE()), true);
            $factory->register(new Axe(new ItemIdentifier(CustomIds::ITEM_NETHERITE_AXE, 0), 'Netherite Axe', ToolTier::NETHERITE()), true);
            $factory->register(new Hoe(new ItemIdentifier(CustomIds::ITEM_NETHERITE_HOE, 0), 'Netherite Hoe', ToolTier::NETHERITE()), true);

            $factory->register(new Armor(new ItemIdentifier(CustomIds::NETHERITE_HELMET, 0), 'Netherite Helmet', new ArmorTypeInfo(6, 407, ArmorInventory::SLOT_HEAD)), true);
            $factory->register(new Armor(new ItemIdentifier(CustomIds::NETHERITE_CHESTPLATE, 0), 'Netherite Chestplate', new ArmorTypeInfo(3, 592, ArmorInventory::SLOT_CHEST)), true);
            $factory->register(new Armor(new ItemIdentifier(CustomIds::NETHERITE_LEGGINGS, 0), 'Netherite Leggings', new ArmorTypeInfo(3, 481, ArmorInventory::SLOT_LEGS)), true);
            $factory->register(new Armor(new ItemIdentifier(CustomIds::NETHERITE_BOOTS, 0), 'Netherite Boots', new ArmorTypeInfo(6, 555, ArmorInventory::SLOT_FEET)), true);
        }
    }
}
