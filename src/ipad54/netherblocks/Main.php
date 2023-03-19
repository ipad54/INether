<?php

namespace ipad54\netherblocks;

use ipad54\netherblocks\blocks\Basalt;
use ipad54\netherblocks\blocks\Blackstone;
use ipad54\netherblocks\blocks\Campfire;
use ipad54\netherblocks\blocks\Chain;
use ipad54\netherblocks\blocks\ChiseledPolishedBlackstone;
use ipad54\netherblocks\blocks\CryingObsidian;
use ipad54\netherblocks\blocks\FloorSign;
use ipad54\netherblocks\blocks\Fungus;
use ipad54\netherblocks\blocks\GildedBlackstone;
use ipad54\netherblocks\blocks\Hyphae;
use ipad54\netherblocks\blocks\Log;
use ipad54\netherblocks\blocks\NetherGoldOre;
use ipad54\netherblocks\blocks\Nylium;
use ipad54\netherblocks\blocks\Planks;
use ipad54\netherblocks\blocks\PolishedBasalt;
use ipad54\netherblocks\blocks\PolishedBlackStone;
use ipad54\netherblocks\blocks\PolishedBlackstoneButton;
use ipad54\netherblocks\blocks\RespawnAnchor;
use ipad54\netherblocks\blocks\Roots;
use ipad54\netherblocks\blocks\Shroomlight;
use ipad54\netherblocks\blocks\Slab;
use ipad54\netherblocks\blocks\SoulCampfire;
use ipad54\netherblocks\blocks\SoulFire;
use ipad54\netherblocks\blocks\SoulLantern;
use ipad54\netherblocks\blocks\SoulSoil;
use ipad54\netherblocks\blocks\SoulTorch;
use ipad54\netherblocks\blocks\Target;
use ipad54\netherblocks\blocks\TwistingVines;
use ipad54\netherblocks\blocks\WallSign;
use ipad54\netherblocks\blocks\WarpedFungus;
use ipad54\netherblocks\blocks\WarpedNylium;
use ipad54\netherblocks\blocks\WarpedWartBlock;
use ipad54\netherblocks\blocks\WeepingVines;
use ipad54\netherblocks\blocks\WoodenButton;
use ipad54\netherblocks\tile\Campfire as TileCampfire;
use ipad54\netherblocks\items\FlintAndSteel;
use ipad54\netherblocks\listener\EventListener;
use ipad54\netherblocks\utils\CustomConfig;
use ipad54\netherblocks\utils\CustomIds;
use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIdentifierFlattened as BIDFlattened;
use pocketmine\block\BlockLegacyIds as Ids;
use pocketmine\block\BlockIdentifier as BID;
use pocketmine\block\BlockToolType;
use pocketmine\block\Door;
use pocketmine\block\Fence;
use pocketmine\block\FenceGate;
use pocketmine\block\Opaque;
use pocketmine\block\Stair;
use pocketmine\block\StonePressurePlate;
use pocketmine\block\tile\Sign as TileSign;
use pocketmine\block\tile\TileFactory;
use pocketmine\block\utils\RecordType;
use pocketmine\block\utils\SlabType;
use pocketmine\block\utils\TreeType;
use pocketmine\block\Wall;
use pocketmine\block\WoodenPressurePlate;
use pocketmine\block\WoodenTrapdoor;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\ItemBlock;
use pocketmine\item\ItemBlockWallOrFloor;
use pocketmine\item\StringToItemParser;
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
use const pocketmine\BEDROCK_BLOCK_UPGRADE_SCHEMA_PATH;

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

	public static function initializeRuntimeIds(): void
	{
		$instance = RuntimeBlockMapping::getInstance();
		$method = new ReflectionMethod(RuntimeBlockMapping::class, "registerMapping");
		$method->setAccessible(true);

		$blockIdMap = json_decode(file_get_contents(BEDROCK_BLOCK_UPGRADE_SCHEMA_PATH . 'block_legacy_id_map.json'), true);
		$metaMap = [];

		foreach ($instance->getBedrockKnownStates() as $runtimeId => $nbt) {
			$mcpeName = $nbt->getString("name");
			$meta = isset($metaMap[$mcpeName]) ? ($metaMap[$mcpeName] + 1) : 0;
			$id = $blockIdMap[$mcpeName] ?? Ids::AIR;

			if ($id !== Ids::AIR && $meta <= 15 && !BlockFactory::getInstance()->isRegistered($id, $meta)) {
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

		$cfg = $this->getCustomConfig();
		if ($cfg->isEnabledDebris()) {
			$this->registerBlock(new Opaque(new BID(CustomIds::ANCIENT_DEBRIS_BLOCK, 0, CustomIds::ANCIENT_DEBRIS_ITEM), "Ancient Debris", new BlockBreakInfo(30, BlockToolType::PICKAXE, ToolTier::DIAMOND()->getHarvestLevel(), 6000)));
		}
		if ($cfg->isEnabledBasalt()) {
			$this->registerBlock(new Basalt(new BID(CustomIds::BASALT_BLOCK, 0, CustomIds::BASALT_ITEM), "Basalt", new BlockBreakInfo(1.25, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 4.2)));
			$this->registerBlock(new PolishedBasalt(new BID(CustomIds::POLISHED_BASALT_BLOCK, 0, CustomIds::POLISHED_BASALT_ITEM), "Polished Basalt", new BlockBreakInfo(1.25, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 4.2)));
		}
		if ($cfg->isEnabledFungus()) {
			$this->registerBlock(new Fungus(new BID(CustomIds::FUNGUS_BLOCK, 0, CustomIds::FUNGUS_ITEM), "Crimson Fungus", BlockBreakInfo::instant()));
			$this->registerBlock(new WarpedFungus(new BID(CustomIds::WARPED_FUNGUS_BLOCK, 0, CustomIds::WARPED_FUNGUS_ITEM), "Warped Fungus", BlockBreakInfo::instant()));
		}
		if ($cfg->isEnabledSoulSoil()) {
			$this->registerBlock(new SoulSoil(new BID(CustomIds::SOUL_SOIL_BLOCK, 0, CustomIds::SOUL_SOIL_ITEM), "Soul Soil", new BlockBreakInfo(0.5, BlockToolType::SHOVEL)));
			$this->registerBlock(new SoulFire(new BID(CustomIds::SOUL_FIRE_BLOCK, 0, CustomIds::SOUL_FIRE_ITEM), "Soul Fire", BlockBreakInfo::instant()), true, false);
		}
		if ($cfg->isEnabledNylium()) {
			$this->registerBlock(new Nylium(new BID(CustomIds::NYLIUM_BLOCK, 0, CustomIds::NYLIUM_ITEM), "Crimson Nylium", new BlockBreakInfo(1, BlockToolType::PICKAXE)));
			$this->registerBlock(new WarpedNylium(new BID(CustomIds::WARPED_NYLIUM_BLOCK, 0, CustomIds::WARPED_NYLIUM_ITEM), "Warped Nylium", new BlockBreakInfo(1, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
		}
		if ($cfg->isEnableNetheriteBlock()) {
			$this->registerBlock(new Opaque(new BID(CustomIds::NETHERITE_BLOCK, 0, CustomIds::NETHERITE_BLOCK_ITEM), "Netherite Block", new BlockBreakInfo(50, BlockToolType::PICKAXE, ToolTier::DIAMOND()->getHarvestLevel(), 6000)));
		}
		//$this->registerBlock(new NetherSprouts(new BID(CustomIds::NETHER_SPROUTS_BLOCK, 0, CustomIds::NETHER_SPROUTS_ITEM), "Nether Sprouts", BlockBreakInfo::instant(BlockToolType::SHEARS)));
		if ($cfg->isEnableShroomlight()) {
			$this->registerBlock(new Shroomlight(new BID(CustomIds::SHROOMLIGHT_BLOCK, 0, CustomIds::SHROOMLIGHT_ITEM), "Shroomlight", new BlockBreakInfo(1, BlockToolType::HOE)));
		}
		if ($cfg->isEnableSoullantern()) {
			$this->registerBlock(new SoulLantern(new BID(CustomIds::SOUL_LANTERN_BLOCK, 0, CustomIds::SOUL_LANTERN_ITEM), "Soul Lantern", new BlockBreakInfo(3.5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 3.5)));
		}
		if ($cfg->isEnableSoulTorch()) {
			$this->registerBlock(new SoulTorch(new BID(CustomIds::SOUL_TORCH_BLOCK, 0, CustomIds::SOUL_TORCH_ITEM), "Soul Torch", BlockBreakInfo::instant()));
		}
		if ($cfg->isEnableNetherwart()) {
			$this->registerBlock(new WarpedWartBlock(new BID(CustomIds::WARPED_WART_BLOCK, 0, CustomIds::WARPED_WART_ITEM), "Warped Wart Block", new BlockBreakInfo(1, BlockToolType::HOE, 0, 5)));
		}
		if ($cfg->isEnableCryingObsidian()) {
			$this->registerBlock(new CryingObsidian(new BID(CustomIds::CRYING_OBSIDIAN_BLOCK, 0, CustomIds::CRYING_OBSIDIAN_ITEM), "Crying Obsidian", new BlockBreakInfo(50, BlockToolType::PICKAXE, ToolTier::DIAMOND()->getHarvestLevel(), 6000)));
		}
		if ($cfg->isEnableTarget()) {
			$this->registerBlock(new Target(new BID(CustomIds::TARGET_BLOCK, 0, CustomIds::TARGET_ITEM), "Target", BlockBreakInfo::instant(BlockToolType::HOE)));
		}
		if ($cfg->isEnableGoldOre()) {
			$this->registerBlock(new NetherGoldOre(new BID(CustomIds::NETHER_GOLD_ORE_BLOCK, 0, CustomIds::NETHER_GOLD_ORE_ITEM), "Nether Gold Ore", new BlockBreakInfo(3, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 3)));
		}
		if ($cfg->isEnabledRespawnAnchor()) {
			$this->registerBlock(new RespawnAnchor(new BID(CustomIds::RESPAWN_ANCHOR_BLOCK, 0, CustomIds::RESPAWN_ANCHOR_ITEM), "Respawn Anchor", new BlockBreakInfo(50, BlockToolType::PICKAXE, ToolTier::DIAMOND()->getHarvestLevel(), 6000)));
		}

		if ($cfg->isEnableBlackstone()) {

			$blackstoneBreakInfo = new BlockBreakInfo(1.5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 6);

			$this->registerBlock(new Blackstone(new BID(CustomIds::BLACKSTONE_BLOCK, 0, CustomIds::BLACKSTONE_ITEM), "Blackstone", $blackstoneBreakInfo));
			$this->registerBlock(new PolishedBlackStone(new BID(CustomIds::POLISHED_BLACKSTONE_BLOCK, 0, CustomIds::POLISHED_BLACKSTONE_ITEM), "Polished Blackstone", $blackstoneBreakInfo));
			$this->registerBlock(new ChiseledPolishedBlackstone(new BID(CustomIds::CHISELED_POLISHED_BLACKSTONE_BLOCK, 0, CustomIds::CHISELED_POLISHED_BLACKSTONE_ITEM), "Chiseled Polished Blackstone", $blackstoneBreakInfo));
			$this->registerBlock(new GildedBlackstone(new BID(CustomIds::GILDED_BLACKSTONE_BLOCK, 0, CustomIds::GILDED_BLACKSTONE_ITEM), "Gilded Blackstone", $blackstoneBreakInfo));
			$this->registerBlock(new Opaque(new BID(CustomIds::POLISHED_BLACKSTONE_BRICKS_BLOCK, 0, CustomIds::POLISHED_BLACKSTONE_BRICKS_ITEM), "Polished Blackstone Bricks", $blackstoneBreakInfo));
			$this->registerBlock(new Opaque(new BID(CustomIds::CRACKED_POLISHED_BLACKSTONE_BRICKS_BLOCK, 0, CustomIds::CRACKED_POLISHED_BLACKSTONE_BRICKS_ITEM), "Cracked Polished Blackstone Bricks", $blackstoneBreakInfo));

			$this->registerSlab(new Slab(new BIDFlattened(CustomIds::BLACKSTONE_SLAB_BLOCK, [CustomIds::BLACKSTONE_DOUBLE_SLAB], 0, CustomIds::BLACKSTONE_SLAB_ITEM), "Blackstone Slab", $blackstoneBreakInfo));
			$this->registerSlab(new Slab(new BIDFlattened(CustomIds::POLISHED_BLACKSTONE_SLAB_BLOCK, [CustomIds::POLISHED_BLACKSTONE_DOUBLE_SLAB], 0, CustomIds::POLISHED_BLACKSTONE_SLAB_ITEM), "Polished Blackstone Slab", $blackstoneBreakInfo));
			$this->registerSlab(new Slab(new BIDFlattened(CustomIds::POLISHED_BLACKSTONE_BRICK_SLAB_BLOCK, [CustomIds::POLISHED_BLACKSTONE_BRICK_DOUBLE_SLAB], 0, CustomIds::POLISHED_BLACKSTONE_BRICK_SLAB_ITEM), "Polished Blackstone Brick Slab", $blackstoneBreakInfo));

			$this->registerBlock(new StonePressurePlate(new BID(CustomIds::POLISHED_BLACKSTONE_PRESSURE_PLATE_BLOCK, 0, CustomIds::POLISHED_BLACKSTONE_PRESSURE_PLATE_ITEM), "Polished Blackstone Pressure Plate", new BlockBreakInfo(0.5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));

			$this->registerBlock(new PolishedBlackstoneButton(new BID(CustomIds::POLISHED_BLACKSTONE_BUTTON_BLOCK, 0, CustomIds::POLISHED_BLACKSTONE_BUTTON_ITEM), "Polished Blackstone Button", new BlockBreakInfo(0.5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
			$wallBreakInfo = new BlockBreakInfo(2.0, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0);
			$this->registerBlock(new Wall(new BID(CustomIds::BLACKSTONE_WALL_BLOCK, 0, CustomIds::BLACKSTONE_WALL_ITEM), "Blackstone Wall", $wallBreakInfo));
			$this->registerBlock(new Wall(new BID(CustomIds::POLISHED_BLACKSTONE_WALL_BLOCK, 0, CustomIds::POLISHED_BLACKSTONE_WALL_ITEM), "Polished Blackstone Wall", $wallBreakInfo));
			$this->registerBlock(new Wall(new BID(CustomIds::POLISHED_BLACKSTONE_BRICK_WALL_BLOCK, 0, CustomIds::POLISHED_BLACKSTONE_BRICK_WALL_ITEM), "Polished Blackstone Brick Wall", $wallBreakInfo));
		}
		if ($cfg->isEnableChain()) {
			$this->registerBlock(new Chain(new BID(CustomIds::CHAIN_BLOCK, 0, CustomIds::CHAIN_BLOCK_ITEM), "Chain", new BlockBreakInfo(5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 6)), false, false);
		}
		if ($cfg->isEnableVines()) {
			$this->registerBlock(new TwistingVines(new BID(CustomIds::TWISTING_VINES_BLOCK, 0, CustomIds::TWISTING_VINES_ITEM), "Twisting Vines", BlockBreakInfo::instant()));
			$this->registerBlock(new WeepingVines(new BID(CustomIds::WEEPING_VINES_BLOCK, 0, CustomIds::WEEPING_VINES_ITEM), "Weeping Vines", BlockBreakInfo::instant()));
		}
		if ($cfg->isEnableRoots()) {
			$this->registerBlock(new Roots(new BID(CustomIds::CRIMSON_ROOTS_BLOCK, 0, CustomIds::CRIMSON_ROOTS_ITEM), "Crimson Roots", BlockBreakInfo::instant()));
			$this->registerBlock(new Roots(new BID(CustomIds::WARPED_ROOTS_BLOCK, 0, CustomIds::WARPED_ROOTS_ITEM), "Warped Roots", BlockBreakInfo::instant()));
		}
		if ($cfg->isEnableWood()) {
			$planksBreakInfo = new BlockBreakInfo(2.0, BlockToolType::AXE, 0, 15.0);

			$this->registerBlock(new Planks(new BID(CustomIds::CRIMSON_PLANKS_BLOCK, 0, CustomIds::CRIMSON_PLANKS_ITEM), "Crimson Planks", $planksBreakInfo));
			$this->registerBlock(new Planks(new BID(CustomIds::WARPED_PLANKS_BLOCK, 0, CustomIds::WARPED_PLANKS_ITEM), "Warped Planks", $planksBreakInfo));

			$this->registerSlab(new Slab(new BIDFlattened(CustomIds::CRIMSON_SLAB_BLOCK, [CustomIds::CRIMSON_DOUBLE_SLAB], 0, CustomIds::CRIMSON_SLAB_ITEM), "Crimson Slab", $planksBreakInfo));
			$this->registerSlab(new Slab(new BIDFlattened(CustomIds::WARPED_SLAB_BLOCK, [CustomIds::WARPED_DOUBLE_SLAB], 0, CustomIds::WARPED_SLAB_ITEM), "Warped Slab", $planksBreakInfo));

			$this->registerBlock(new Log(new BID(CustomIds::CRIMSON_STEM_BLOCK, 0, CustomIds::CRIMSON_STEM_ITEM), "Crimson Stem", new BlockBreakInfo(2, BlockToolType::AXE, 0, 10), TreeType::CRIMSON(), false));
			$this->registerBlock(new Log(new BID(CustomIds::WARPED_STEM_BLOCK, 0, CustomIds::WARPED_STEM_ITEM), "Warped Stem", new BlockBreakInfo(2, BlockToolType::AXE, 0, 10), TreeType::WARPED(), false));
			$this->registerBlock(new Log(new BID(CustomIds::CRIMSON_STRIPPED_STEM_BLOCK, 0, CustomIds::CRIMSON_STRIPPED_STEM_ITEM), "Crimson Stripped Stem", new BlockBreakInfo(2, BlockToolType::AXE, 0, 10), TreeType::CRIMSON(), true));
			$this->registerBlock(new Log(new BID(CustomIds::WARPED_STRIPPED_STEM_BLOCK, 0, CustomIds::WARPED_STRIPPED_STEM_ITEM), "Warped Stripped Stem", new BlockBreakInfo(2, BlockToolType::AXE, 0, 10), TreeType::WARPED(), true));

			$this->registerBlock(new Hyphae(new BID(CustomIds::CRIMSON_HYPHAE_BLOCK, 0, CustomIds::CRIMSON_HYPHAE_ITEM), "Crimson Hyphae", new BlockBreakInfo(2, BlockToolType::AXE, 0, 10), TreeType::CRIMSON(), false));
			$this->registerBlock(new Hyphae(new BID(CustomIds::WARPED_HYPHAE_BLOCK, 0, CustomIds::WARPED_HYPHAE_ITEM), "Warped Hyphae", new BlockBreakInfo(2, BlockToolType::AXE, 0, 10), TreeType::WARPED(), false));

			$this->registerBlock(new Hyphae(new BID(CustomIds::CRIMSON_STRIPPED_HYPHAE_BLOCK, 0, CustomIds::CRIMSON_STRIPPED_HYPHAE_ITEM), "Crimson Stripped Hyphae", new BlockBreakInfo(2, BlockToolType::AXE, 0, 10), TreeType::CRIMSON(), true));
			$this->registerBlock(new Hyphae(new BID(CustomIds::WARPED_STRIPPED_HYPHAE_BLOCK, 0, CustomIds::WARPED_STRIPPED_HYPHAE_ITEM), "Warped Stripped Hyphae", new BlockBreakInfo(2, BlockToolType::AXE, 0, 10), TreeType::WARPED(), true));
			$this->registerBlock(new Door(new BID(CustomIds::CRIMSON_DOOR_BLOCK, 0, CustomIds::CRIMSON_DOOOR_ITEM), "Crimson Door", new BlockBreakInfo(3, BlockToolType::AXE)), false);
			$this->registerBlock(new Door(new BID(CustomIds::WARPED_DOOR_BLOCK, 0, CustomIds::WARPED_DOOR_ITEM), "Warped Door", new BlockBreakInfo(3, BlockToolType::AXE)), false);
			$this->registerBlock(new Fence(new BID(CustomIds::CRIMSON_FENCE_BLOCK, 0, CustomIds::CRIMSON_FENCE_ITEM), "Crimson Fence", new BlockBreakInfo(2, BlockToolType::AXE, 0, 3)));
			$this->registerBlock(new Fence(new BID(CustomIds::WARPED_FENCE_BLOCK, 0, CustomIds::WARPED_FENCE_ITEM), "Warped Fence", new BlockBreakInfo(2, BlockToolType::AXE, 0, 3)));
			$this->registerBlock(new FenceGate(new BID(CustomIds::CRIMSON_FENCE_GATE_BLOCK, 0, CustomIds::CRIMSON_FENCE_GATE_ITEM), "Crimson Fence Gate", new BlockBreakInfo(2, BlockToolType::AXE, 0, 3)));
			$this->registerBlock(new FenceGate(new BID(CustomIds::WARPED_FENCE_GATE_BLOCK, 0, CustomIds::WARPED_FENCE_GATE_ITEM), "Warped Fence Gate", new BlockBreakInfo(2, BlockToolType::AXE, 0, 3)));
			$this->registerBlock(new WoodenTrapdoor(new BID(CustomIds::CRIMSON_TRAPDOOR_BLOCK, 0, CustomIds::CRIMSON_TRAPDOOR_ITEM), "Crimson Trapdoor", new BlockBreakInfo(3, BlockToolType::AXE, 0, 15)));
			$this->registerBlock(new WoodenTrapdoor(new BID(CustomIds::WARPED_TRAPDOOR_BLOCK, 0, CustomIds::WARPED_TRAPDOOR_ITEM), "Warped Trapdoor", new BlockBreakInfo(3, BlockToolType::AXE, 0, 15)));

			$signBreakInfo = new BlockBreakInfo(1.0, BlockToolType::AXE);
			$this->registerBlock(new FloorSign(new BID(CustomIds::CRIMSON_FLOOR_SIGN_BLOCK, 0, CustomIds::CRIMSON_FLOOR_SIGN_ITEM, TileSign::class), "Crimson Floor Sign", $signBreakInfo), true, false);
			$this->registerBlock(new WallSign(new BID(CustomIds::CRIMSON_WALL_SIGN_BLOCK, 0, CustomIds::CRIMSON_WALL_SIGN_ITEM, TileSign::class), "Crimson Wall Sign", $signBreakInfo), true, false);
			$this->registerBlock(new FloorSign(new BID(CustomIds::WARPED_FLOOR_SIGN_BLOCK, 0, CustomIds::WARPED_FLOOR_SIGN_ITEM, TileSign::class), "Warped Floor Sign", $signBreakInfo), true, false);
			$this->registerBlock(new WallSign(new BID(CustomIds::WARPED_WALL_SIGN_BLOCK, 0, CustomIds::WARPED_WALL_SIGN_ITEM, TileSign::class), "Warped Wall Sign", $signBreakInfo), true, false);

			$woodenButtonBreakInfo = new BlockBreakInfo(0.5, BlockToolType::AXE);
			$this->registerBlock(new WoodenButton(new BID(CustomIds::CRIMSON_BUTTON_BLOCK, 0, CustomIds::CRIMSON_BUTTON_ITEM), "Crimson Button", $woodenButtonBreakInfo));
			$this->registerBlock(new WoodenButton(new BID(CustomIds::WARPED_BUTTON_BLOCK, 0, CustomIds::WARPED_BUTTON_ITEM), "Warped Button", $woodenButtonBreakInfo));

			$pressurePlateBreakInfo = new BlockBreakInfo(0.5, BlockToolType::AXE);
			$this->registerBlock(new WoodenPressurePlate(new BID(CustomIds::CRIMSON_PRESSURE_PLATE_BLOCK, 0), "Crimson Pressure Plate", $pressurePlateBreakInfo));
			$this->registerBlock(new WoodenPressurePlate(new BID(CustomIds::WARPED_PRESSURE_PLATE_BLOCK, 0), "Warped Pressure Plate", $pressurePlateBreakInfo));
		}
		if ($cfg->isEnableStairs()) {
			$this->registerBlock(new Stair(new BID(CustomIds::BLACKSTONE_STAIRS_BLOCK, 0, CustomIds::BLACKSTONE_STAIRS_ITEM), "Blackstone Stairs", new BlockBreakInfo(3, BlockToolType::AXE, 0, 6)));
			$this->registerBlock(new Stair(new BID(CustomIds::CRIMSON_STAIRS_BLOCK, 0, CustomIds::CRIMSON_STAIRS_ITEM), "Crimson Stairs", new BlockBreakInfo(3, BlockToolType::AXE, 0, 6)));
			$this->registerBlock(new Stair(new BID(CustomIds::POLISHED_BLACKSTONE_STAIRS_BLOCK, 0, CustomIds::POLISHED_BLACKSTONE_STAIRS_ITEM), "Polished Blackstone Stairs", new BlockBreakInfo(3, BlockToolType::AXE, 0, 6)));
			$this->registerBlock(new Stair(new BID(CustomIds::WARPED_STAIRS_BLOCK, 0, CustomIds::WARPED_STAIRS_ITEM), "Warped Stairs", new BlockBreakInfo(3, BlockToolType::AXE, 0, 6)));
			$this->registerBlock(new Stair(new BID(CustomIds::POLISHED_BLACKSTONE_BRICK_STAIRS_BLOCK, 0, CustomIds::POLISHED_BLACKSTONE_BRICK_STAIRS_ITEM), "Polished Blackstone Brick Stairs", new BlockBreakInfo(3, BlockToolType::AXE, 0, 6)));
		}
		if ($cfg->isEnableCampfire()) {
			$this->registerBlock(new Campfire(new BID(Ids::CAMPFIRE, 0, CustomIds::CAMPFIRE_ITEM, TileCampfire::class), "Campfire", new BlockBreakInfo(2, BlockToolType::AXE, 0, 10)), false, false);
			$this->registerBlock(new SoulCampfire(new BID(CustomIds::SOUL_CAMPFIRE_BLOCK, 0, CustomIds::SOUL_CAMPFIRE_ITEM, TileCampfire::class), "Soul Campfire", new BlockBreakInfo(2, BlockToolType::AXE, 0, 10)), false, false);
		}

	}

	public function initTiles(): void
	{
		$cfg = $this->getCustomConfig();
		$tf = TileFactory::getInstance();
		if ($cfg->isEnableCampfire()) {
			$tf->register(TileCampfire::class, ["Campfire", "minecraft:campfire"]);
		}
	}

	public function initItems(): void
	{
		$cfg = $this->getCustomConfig();
		if ($cfg->isEnabledSoulSoil()) {
			$this->registerItem(new FlintAndSteel(new ItemIdentifier(ItemIds::FLINT_AND_STEEL, 0), "Flint and Steel"), false);
		}
		if ($cfg->isEnablePigstep()) {
			$class = new \ReflectionClass(RecordType::class);
			$register = $class->getMethod('register');
			$register->setAccessible(true);
			$constructor = $class->getConstructor();
			$constructor->setAccessible(true);
			$instance = $class->newInstanceWithoutConstructor();
			$constructor->invoke($instance, 'disk_pigstep', 'Lena Raine - Pigstep', CustomIds::RECORD_PIGSTEP_SOUND_ID, new Translatable('item.record_pigstep.desc', []));
			$register->invoke(null, $instance);
			
			$this->registerItem(new Record(new ItemIdentifier(CustomIds::RECORD_PIGSTEP, 0), RecordType::DISK_PIGSTEP(), "Record Pigstep"));
		}
		if ($cfg->isEnableWood()) {
			$this->registerItem(new ItemBlock(new ItemIdentifier(CustomIds::CRIMSON_DOOOR_ITEM, 0), BlockFactory::getInstance()->get(CustomIds::CRIMSON_DOOR_BLOCK, 0)));
			$this->registerItem(new ItemBlock(new ItemIdentifier(CustomIds::WARPED_DOOR_ITEM, 0), BlockFactory::getInstance()->get(CustomIds::WARPED_DOOR_BLOCK, 0)));
			$this->registerItem(new ItemBlockWallOrFloor(new ItemIdentifier(CustomIds::CRIMSON_SIGN, 0), BlockFactory::getInstance()->get(CustomIds::CRIMSON_FLOOR_SIGN_BLOCK, 0), BlockFactory::getInstance()->get(CustomIds::CRIMSON_WALL_SIGN_BLOCK, 0)), false);
			$this->registerItem(new ItemBlockWallOrFloor(new ItemIdentifier(CustomIds::WARPED_SIGN, 0), BlockFactory::getInstance()->get(CustomIds::WARPED_FLOOR_SIGN_BLOCK, 0), BlockFactory::getInstance()->get(CustomIds::WARPED_WALL_SIGN_BLOCK, 0)), false);
			StringToItemParser::getInstance()->register("crimson_sign", fn() => ItemFactory::getInstance()->get(CustomIds::CRIMSON_SIGN));
			StringToItemParser::getInstance()->register("warped_sign", fn() => ItemFactory::getInstance()->get(CustomIds::WARPED_SIGN));
		}
		if ($cfg->isEnableCampfire()) {
			$this->registerItem(new ItemBlock(new ItemIdentifier(CustomIds::CAMPFIRE_ITEM, 0), BlockFactory::getInstance()->get(Ids::CAMPFIRE, 0)));
			$this->registerItem(new ItemBlock(new ItemIdentifier(CustomIds::SOUL_CAMPFIRE_ITEM, 0), BlockFactory::getInstance()->get(CustomIds::SOUL_CAMPFIRE_BLOCK, 0)));
		}
		if ($cfg->isEnableChain()) {
			$this->registerItem(new ItemBlock(new ItemIdentifier(CustomIds::CHAIN_ITEM, 0), BlockFactory::getInstance()->get(CustomIds::CHAIN_BLOCK, 0)));
		}
		if ($cfg->isEnableNetheriteTools()) {
			$class = new \ReflectionClass(ToolTier::class);
			$register = $class->getMethod('register');
			$register->setAccessible(true);
			$constructor = $class->getConstructor();
			$constructor->setAccessible(true);
			$instance = $class->newInstanceWithoutConstructor();
			$constructor->invoke($instance, 'netherite', 6, 2031, 9, 10);
			$register->invoke(null, $instance);
			
			$this->registerItem(new Item(new ItemIdentifier(CustomIds::ITEM_NETHERITE_INGOT, 0), 'Netherite Ingot'));
			$this->registerItem(new Item(new ItemIdentifier(CustomIds::ITEM_NETHERITE_SCRAP, 0), 'Netherite Scrap'));
			$this->registerItem(new Sword(new ItemIdentifier(CustomIds::ITEM_NETHERITE_SWORD, 0), 'Netherite Sword', ToolTier::NETHERITE()));
			$this->registerItem(new Shovel(new ItemIdentifier(CustomIds::ITEM_NETHERITE_SHOVEL, 0), 'Netherite Shovel', ToolTier::NETHERITE()));
			$this->registerItem(new Pickaxe(new ItemIdentifier(CustomIds::ITEM_NETHERITE_PICKAXE, 0), 'Netherite Pickaxe', ToolTier::NETHERITE()));
			$this->registerItem(new Axe(new ItemIdentifier(CustomIds::ITEM_NETHERITE_AXE, 0), 'Netherite Axe', ToolTier::NETHERITE()));
			$this->registerItem(new Hoe(new ItemIdentifier(CustomIds::ITEM_NETHERITE_HOE, 0), 'Netherite Hoe', ToolTier::NETHERITE()));

			$this->registerItem(new Armor(new ItemIdentifier(CustomIds::NETHERITE_HELMET, 0), 'Netherite Helmet', new ArmorTypeInfo(6, 407, ArmorInventory::SLOT_HEAD)));
			$this->registerItem(new Armor(new ItemIdentifier(CustomIds::NETHERITE_CHESTPLATE, 0), 'Netherite Chestplate', new ArmorTypeInfo(3, 592, ArmorInventory::SLOT_CHEST)));
			$this->registerItem(new Armor(new ItemIdentifier(CustomIds::NETHERITE_LEGGINGS, 0), 'Netherite Leggings', new ArmorTypeInfo(3, 481, ArmorInventory::SLOT_LEGS)));
			$this->registerItem(new Armor(new ItemIdentifier(CustomIds::NETHERITE_BOOTS, 0), 'Netherite Boots', new ArmorTypeInfo(6, 555, ArmorInventory::SLOT_FEET)));
		}
	}

	private function registerBlock(Block $block, bool $registerToParser = true, bool $addToCreative = true): void
	{
		BlockFactory::getInstance()->register($block, true);
		if ($addToCreative && !CreativeInventory::getInstance()->contains($block->asItem())) {
			CreativeInventory::getInstance()->add($block->asItem());
		}
		if ($registerToParser) {
			$name = strtolower($block->getName());
			$name = str_replace(" ", "_", $name);
			StringToItemParser::getInstance()->registerBlock($name, fn() => $block);
		}
	}

	private function registerItem(Item $item, bool $registerToParser = true): void
	{
		ItemFactory::getInstance()->register($item, true);
		if (!CreativeInventory::getInstance()->contains($item)) {
			CreativeInventory::getInstance()->add($item);
		}
		if ($registerToParser) {
			$name = strtolower($item->getName());
			$name = str_replace(" ", "_", $name);
			StringToItemParser::getInstance()->register($name, fn() => $item);
		}
	}

	private function registerSlab(Slab $slab) : void{
		$this->registerBlock($slab);
		$identifierFlattened = $slab->getIdInfo();
		if($identifierFlattened instanceof BIDFlattened){
			BlockFactory::getInstance()->remap($identifierFlattened->getSecondId(), $identifierFlattened->getVariant() | 0x1, $slab->setSlabType(SlabType::DOUBLE()));
		}
	}
}
