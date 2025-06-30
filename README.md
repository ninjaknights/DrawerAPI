# DrawerAPI
[![GitHub license](https://img.shields.io/github/license/HydroGames-dev/DrawerAPI)](https://github.com/HydroGames-dev/DrawerAPI/blob/main/LICENSE)
[![GitHub stars](https://img.shields.io/github/stars/HydroGames-dev/DrawerAPI)](https://github.com/HydroGames-dev/DrawerAPI/stargazers)
[![GitHub forks](https://img.shields.io/github/forks/HydroGames-dev/DrawerAPI)](https://github.com/HydroGames-dev/DrawerAPI/network/members)
[![GitHub issues](https://img.shields.io/github/issues/HydroGames-dev/DrawerAPI)](https://github.com/HydroGames-dev/DrawerAPI/issues)
[![Github downloads](https://img.shields.io/github/downloads/HydroGames-dev/DrawerAPI/total)](https://github.com/HydroGames-dev/DrawerAPI/releases)

<p align="center">
	<a href="https://github.com/HydroGames-dev/DrawerAPI">
    <img src="assets/icon.png?raw=true" alt="DrawerAPI Icon" width="150" /></a><br>
	<b>DrawerAPI</b> is a PocketMine-MP virion designed to draw shapes and text in the world using the ServerScriptDebugDrawerPacket.
	<br>
	<a href="https://github.com/HydroGames-dev/DrawerAPI">View on GitHub</a>
</p>

**NOTE:** You MUST register `DrawerAPI` during plugin enable before you can begin creating `DrawerAPI` instances.
```php
use NinjaKnights\DrawerAPI\DrawerAPI;
class MyPlugin extends PluginBase {
    protected function onLoad(): void {
		if(!DrawerAPI::isRegistered()){
			DrawerAPI::register($this);
		}
    }
}
```

#### Creating Shapes
You can create various shapes using the `DrawerAPI` methods. Here are some examples:
```php
use NinjaKnights\DrawerAPI\shape\Text;
// Create a text shape
Text::create(
	$world, // Player/World Players to show the text to
	"Hello, World!", // The text to display
	$player->getPosition(), // Position in the world
	1.0, // Scale of the text
	"white", // Color of the text
);
```

#### Available Shape Types
- **Text**: Displays text in the world.
- **Arrow**: Draws an arrow pointing in a specific direction.
- **Box**: Draws a box in the world.
- **Line**: Draws a line between two points.
- **Sphere**: Draws a sphere in the world.
- **Circle**: Draws a circle in the world.

#### Clearing Shapes
You can clear all shapes of a specific type for a viewer or world using the `clearAll` method:
```php
use NinjaKnights\DrawerAPI\DrawerAPI;
DrawerAPI::clearAll("text", $world); // Clears all text shapes for the specified world
DrawerAPI::clearAll("arrow", $player); // Clears all arrow shapes for the specified player
```
or clear all shapes of a specific type for all viewers:
```php
use NinjaKnights\DrawerAPI\shape\Text;
Text::removeById($world, 1); // Removes the text shape with ID 1 for the player/world players
```

#### Getting Active IDs
You can get the list of active IDs for a specific type of shape using the `getIdList` method:
```php
use NinjaKnights\DrawerAPI\DrawerAPI;
$activeIds = DrawerAPI::getIdList("text");
if (empty($activeIds)) {
	throw new \RuntimeException("No active IDs found for the specified type.");
}
foreach ($activeIds as $id) {
	echo "Active ID: $id\n";
}
```

## 🧪 API Reference

| Method | Description |
|--------|-------------|
| `register(PluginBase $plugin)` | Registers DrawerAPI with your plugin. |
| `isRegistered()` | Checks if the API is already registered. |
| `sendPacket(World\|Player $viewer, ?ServerScriptDebugDrawerPacket $packet)` | Sends a custom packet to a viewer or world. |
| `despawnPacketByID(World\|Player $viewer, int $id)` | Removes a specific shape by ID. |
| `getIdList(string $type)` | Gets all active IDs for a shape type. |
| `clearAll(World\|Player $viewer, string $type)` | Clears all shapes of a type for the viewer or world. |

---

## 📄 License

This project is licensed under the [MIT License](https://github.com/HydroGames-dev/DrawerAPI/blob/main/LICENSE).

---

## 📬 Contact

Have questions or need help? Join the conversation in the [issues section](https://github.com/HydroGames-dev/DrawerAPI/issues).