<?php

declare(strict_types=1);

namespace Britty;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use Britty\FloatingTextCommand;
use Britty\FloatingTextManager;
use NinjaKnights\DrawerAPI\DrawerAPI;
use NinjaKnights\DrawerAPI\Shapes\Text;

class Main extends PluginBase implements Listener{

	public const PREFIX = "§l§g[§r§l§bF§tT§r§g§l]§r §d> §a";
	public static $instance;
	private $commands;
	private FloatingTextManager $textManager;

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
		$this->getServer()->getLogger()->info(self::PREFIX."-----------------------");
		$this->getServer()->getLogger()->info(self::PREFIX."Loading Floating Text Plugin...");
		$this->getServer()->getLogger()->info(self::PREFIX."-----------------------");
		$this->textManager = new FloatingTextManager($this);
		$this->commands = [new FloatingTextCommand($this)];
		$this->textManager->loadFloatingTexts();
		$this->registerCommands();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getLogger()->info(self::PREFIX."Floating Text Plugin Enabled!");
	}

	public function registerCommands(){
		if(count($this->commands) === 0) return;
		foreach($this->commands as $command) {
			$this->getServer()->getCommandMap()->register("floatingtext", $command);
		}
	}

	public function getTextManager(): FloatingTextManager {
		return $this->textManager;
	}
}