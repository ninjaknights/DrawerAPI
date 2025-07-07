<?php

namespace Britty;

use pocketmine\utils\Config;
use pocketmine\math\Vector3;
use pocketmine\world\World;
use pocketmine\player\Player;
use NinjaKnights\DrawerAPI\DrawerAPI;
use NinjaKnights\DrawerAPI\Shapes\Text;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;

class FloatingTextManager {

	private string $dataFolder;
	/** @var array<int, array{message: string, color: string, x: float, y: float, z: float, world: string}> */
	private array $floatingTexts = [];
	 /** @var array<int, Vector3> */
	private array $textPositions = [];

	public function __construct(Main $plugin){
		$this->dataFolder = $plugin->getDataFolder() . "saved_texts/";
		@mkdir($this->dataFolder);
	}

	public function getAllFloatingTexts(): array {
		$files = glob($this->dataFolder . "ft_*.json");
		sort($files, SORT_NATURAL);
		return $files;
	}

	public function getNextId(): int {
		return count($this->getAllFloatingTexts()) + 1;
	}

	public function createFloatingText(Player $player, ?string $message, ?string $color): void {
		$id = $this->getNextId();
		$file = $this->dataFolder . "ft_$id.json";
		$pos = $player->getPosition();
		$data = [
			"message" => $message,
			"color" => $color,
			"x" => $pos->getX(),
			"y" => $pos->getY(),
			"z" => $pos->getZ(),
			"world" => $pos->getWorld()->getFolderName()
		];

		file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
		$this->floatingTexts[$id] = $data;
		$this->spawnFloatingText($id, $data);
		$player->sendMessage(Main::PREFIX . "Created FloatingText with ID: §e{$id}§r!");
	}

	public function removeFloatingText(Player $player, int $id): void {
		$file = $this->dataFolder . "ft_$id.json";
		if(!file_exists($file)){
			$player->sendMessage(Main::PREFIX . "ID: §e{$id}§r does not exist!");
			return;
		}
		$this->despawnFloatingText($id);
		unlink($file);
		unset($this->floatingTexts[$id]);
		$this->reorderFiles();
		$player->sendMessage(Main::PREFIX . "Removed FloatingText ID: §e{$id}§r!");
	}

	public function reorderFiles(): void {
		$files = $this->getAllFloatingTexts();
		$newId = 1;
		$newTexts = [];
		foreach($files as $file){
			$data = json_decode(file_get_contents($file), true);
			$newFile = $this->dataFolder . "ft_$newId.json";
			rename($file, $newFile);
			$newTexts[$newId] = $data;
			$newId++;
		}
		$this->floatingTexts = $newTexts;
	}

	public function loadFloatingTexts(): void {
		$files = $this->getAllFloatingTexts();
		$this->floatingTexts = [];
		foreach($files as $index => $file){
			$id = $index + 1;
			$data = json_decode(file_get_contents($file), true);
			if (!isset($data['world'])) continue;
			$this->floatingTexts[$id] = $data;
			$this->spawnFloatingText($id, $data);
		}
	}

	public function spawnFloatingText(int $id, array $data): void {
		$world = Main::getInstance()->getServer()->getWorldManager()->getWorldByName($data["world"]);
		if (!$world instanceof World) return;
		$pos = new Vector3((float)$data["x"], (float)$data["y"], (float)$data["z"]);
		$text = $this->formatMessage($data["message"]);
		$color = $data["color"] ?? "white";
		Text::create($world, $pos, $text, 1.0, $color);
		$this->textPositions[$id] = $pos;
	}

	public function despawnFloatingText(int $id): void {
		if (!isset($this->floatingTexts[$id])) return;
		$data = $this->floatingTexts[$id];
		$world = Main::getInstance()->getServer()->getWorldManager()->getWorldByName($data["world"]);
		if (!$world instanceof World) return;
		Text::removeById($world, $id);
		unset($this->textPositions[$id]);
	}

	public function editFloatingText(Player $player, int $id, string $newMessage): void {
		$file = $this->dataFolder . "ft_$id.json";
		if(!file_exists($file)){
			$player->sendMessage(Main::PREFIX . "ID: §e{$id}§r does not exist!");
			return;
		}
		$data = json_decode(file_get_contents($file), true);
		$data["message"] = $newMessage;
		file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
		$this->floatingTexts[$id] = $data;
		$this->despawnFloatingText($id);
		$this->spawnFloatingText($id, $data);
		$player->sendMessage(Main::PREFIX . "Updated FloatingText ID: §e{$id}§r!");
	}

	public function getTextDataById(int $id): ?array {
		return $this->floatingTexts[$id] ?? null;
	}

	private function formatMessage(string $msg): string {
		return str_replace("\\n", "\n", $msg);
	}
}