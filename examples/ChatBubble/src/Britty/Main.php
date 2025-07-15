<?php

declare(strict_types=1);

namespace Britty;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\world\World;
use pocketmine\utils\TextFormat as TF;
use ninjaknights\drawerAPI\DrawerAPI;
use ninjaknights\drawerAPI\Shapes\Text;
use pocketmine\event\block\BlockBreakEvent;

class Main extends PluginBase implements Listener{

	public static $instance;

	public function onLoad(): void{
		self::$instance = $this; 
	}

	public static function getInstance(): self{
		return self::$instance;
	}

	public function onEnable(): void{
		if(!DrawerAPI::isRegistered()){
			DrawerAPI::register($this);
		}
		$this->getServer()->getLogger()->info("§7-----------------------");
		$this->getServer()->getLogger()->info("§eLoading ChatBubbles Plugin...");
		$this->getServer()->getLogger()->info("§7-----------------------");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getLogger()->info("§aChatBubbles Plugin Enabled!");
	}

	public function onPlayerBlockBreak(BlockBreakEvent $event){
		$player = $event->getPlayer();
		$world = $player->getWorld();
		$block = $event->getBlock();
		$blockName = $block->asItem()->getVanillaName();
		Text::create(
			$player->getWorld(),
			$block->getPosition()->add(0, 2, 0),
			"§q§l+1 §r§7{$blockName}",
			2.5,
			"white"
		);
		$this->getScheduler()->scheduleDelayedTask(new class($world) extends \pocketmine\scheduler\Task {
				private World $world;
				public function __construct(World $world) {
					$this->world = $world;
				}
				public function onRun(): void {
					DrawerAPI::clearAll($this->world, "text");
				}
		}, 3 * 20);
		$player->getInventory()->addItem($event->getItem());
		$event->setDrops([]);
	}

	public function onPlayerChat(PlayerChatEvent $event): void {
		$player = $event->getPlayer();
		$world = $player->getWorld();
		$message = $event->getMessage();
		if($message === "") {
			return;
		}else{
			Text::create(
				$world,
				position: $player->getPosition()->add(0, 2.5, 0),
				text: TF::colorize($message),
				size: 1.10,
				color: "white" 
			);
			$this->getScheduler()->scheduleDelayedTask(new class($world) extends \pocketmine\scheduler\Task {
				private World $world;
				public function __construct(World $world) {
					$this->world = $world;
				}
				public function onRun(): void {
					DrawerAPI::clearAll($this->world, "text");
				}
			}, 5 * 20);
		}
	}
}