<?php

namespace Britty;

use Britty\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class FloatingTextCommand extends Command {

    private $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("floatingtext", "manage floating text settings", "/floatingtext", ["ft"]);
        $this->setPermission("ft.command.admin");
        $this->plugin = $plugin;
    }
    
    public function getPlugin(): Main{
        return $this->plugin;
    }
    
    public function getServer(){
        return $this->plugin->getServer();
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool{
        if (!$sender instanceof Player) {
            $sender->sendMessage(Main::PREFIX."This command can only be used in-game.");
            return true;
        }
		$textManager = $this->plugin->getTextManager();
		switch($args[0] ?? '') {
			case "create":
				$sender->sendForm(new class($this->plugin, $sender) implements \pocketmine\form\Form {
					public function __construct(private Main $plugin, private Player $player) {}
					public function jsonSerialize(): array {
						return [
							"type" => "custom_form",
							"title" => "Create Floating Text",
							"content" => [
								[
									"type" => "input",
									"text" => "Enter the floating text:\nUse '§' codes for color (e.g. §a§r), \\n for new line"
								],
								[
									"type" => "input",
									"text" => "Enter the color (e.g. white, red, blue)\nLeave empty for default color"
								],
							]
						];
					}
					public function handleResponse(Player $player, $data): void {
						if ($data === null || empty($data[0])) return;
						$this->plugin->getTextManager()->createFloatingText($player, (string) $data[0], (string) $data[1] ?? "white");
					}
				});
				break;
			case "remove":
				$sender->sendForm(new class($this->plugin, $sender) implements \pocketmine\form\Form {
					public function __construct(private Main $plugin, private Player $player) {}
					public function jsonSerialize(): array {
						$texts = $this->plugin->getTextManager()->getAllFloatingTexts();
						$options = array_map(fn($f) => basename($f, ".json"), $texts);
						return [
							"type" => "custom_form",
							"title" => "Remove Floating Text",
							"content" => [[
								"type" => "dropdown",
								"text" => "Select FloatingText ID",
								"options" => $options
							]]
						];
					}
					public function handleResponse(Player $player, $data): void {
						if ($data === null) return;
						$id = $data[0] + 1;
						$this->plugin->getTextManager()->removeFloatingText($player, $id);
					}
				});
				break;
			case "edit":
				 $sender->sendForm(new class($this->plugin, $sender) implements \pocketmine\form\Form {
					public function __construct(private Main $plugin, private Player $player) {}
					public function jsonSerialize(): array {
						$texts = $this->plugin->getTextManager()->getAllFloatingTexts();
						$options = array_map(fn($f) => basename($f, ".json"), $texts);
						return [
							"type" => "custom_form",
							"title" => "Edit Floating Text",
							"content" => [
								[
									"type" => "dropdown",
									"text" => "Select FloatingText ID",
									"options" => $options
								],
								[
									"type" => "input",
									"text" => "New message:\nUse '§' codes for color (e.g. §c§r), \\n for new line"
								]
							]
						];
					}

					public function handleResponse(Player $player, $data): void {
						if ($data === null || empty($data[1])) return;
						$id = $data[0] + 1;
						$newMsg = $data[1];
						$this->plugin->getTextManager()->editFloatingText($player, $id, $newMsg);
					}
				});
				break;
			case "list":
				$texts = $textManager->getAllFloatingTexts();
				if (empty($texts)) {
					$sender->sendMessage(Main::PREFIX . "No FloatingTexts found.");
				} else {
					$message = Main::PREFIX . "FloatingTexts:\n";
					foreach ($texts as $index => $file) {
						$data = json_decode(file_get_contents($file), true);
						$message .= "ID: §e" . ($index + 1) . "§r - Message: §a" . $data['message'] . "§r\n";
					}
					$sender->sendMessage($message);
				}
				break;
			default:
				$sender->sendMessage(Main::PREFIX . "Usage: /floatingtext <create|remove|edit>");
		}
        return true;
    }
}