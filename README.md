# DrawerAPI
[![GitHub license](https://img.shields.io/github/license/ninjaknights/DrawerAPI)](https://github.com/ninjaknights/DrawerAPI/blob/main/LICENSE)
[![GitHub stars](https://img.shields.io/github/stars/ninjaknights/DrawerAPI)](https://github.com/ninjaknights/DrawerAPI/stargazers)
[![GitHub forks](https://img.shields.io/github/forks/ninjaknights/DrawerAPI)](https://github.com/ninjaknights/DrawerAPI/network/members)
[![GitHub issues](https://img.shields.io/github/issues/ninjaknights/DrawerAPI)](https://github.com/ninjaknights/DrawerAPI/issues)
[![Github downloads](https://img.shields.io/github/downloads/ninjaknights/DrawerAPI/total)](https://github.com/ninjaknights/DrawerAPI/releases)

<p align="center">
	<a href="https://github.com/ninjaknights/DrawerAPI">
    <img src="assets/icon.png?raw=true" alt="DrawerAPI Icon" width="150" /></a><br>
	<b>DrawerAPI</b> is a PocketMine-MP virion designed to draw shapes and text in the world using the ServerScriptDebugDrawerPacket.
	<br>
	<a href="https://github.com/ninjaknights/DrawerAPI">View on GitHub</a>
</p>

**NOTE:** You MUST register `DrawerAPI` during plugin enable before you can begin creating `DrawerAPI` instances.
```php
use ninjaknights\drawerAPI\DrawerAPI;
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
- TextShape:
```php
use ninjaknights\drawerAPI\shape\Text;
// Create a text shape
Text::create(
	$world, // Player/World Players to show the text to
	"Hello, World!", // The text to display
	new Vector3(0, 100, 0), // Position in the world or set to null to use player's position
	1.0, // Scale of the text
	"white", // Color name or #f0f0f0, f0f0f0 works too
);
```
- CircleShape:
```php
use ninjaknights\drawerAPI\shape\Circle;
// Create a text shape
Circle::create(
	$world, // Player/World Players to show the text to
	new Vector3(0, 100, 0), // Position in the world or set to null to use player's position
	1.0, // Scale of the text
	"#ff0000ff", // Color name or #f0f0f0, f0f0f0 works too
	3 // 3 means it will have 3 sides so a triangle, default is >50 (not sure)
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
use ninjaknights\drawerAPI\DrawerAPI;
DrawerAPI::clearAll(ShapeType::TEXT, $world); // Clears all text shapes for the specified world
DrawerAPI::clearAll(ShapeType::ARROW, $player); // Clears all arrow shapes for the specified player
```
or clear all shapes of a specific type for all viewers:
```php
use ninjaknights\drawerAPI\shape\Text;
Text::removeById($world, 1); // Removes the text shape with ID 1 for the player/world players
```

#### Getting Active IDs
You can get the list of active IDs for a specific type of shape using the `getIdList` method:
```php
use ninjaknights\drawerAPI\DrawerAPI;
$activeIds = DrawerAPI::getIdList(ShapeType::TEXT);
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
| `sendPacket(World\|Player $viewer, ServerScriptDebugDrawerPacket $packet)` | Sends a custom packet to a viewer or world. |
| `despawnPacketByID(World\|Player $viewer, int $id)` | Removes a specific shape by ID. |
| `getIdList(ShapeType::enum)` | Gets all active IDs for a shape type. |
| `clearAll(World\|Player $viewer, ShapeType::enum)` | Clears all shapes of a type for the viewer or world. (Deault `null` - clears all shapes) |

---

## 📄 License

This project is licensed under the [GPL-3.0 License](https://github.com/ninjaknights/DrawerAPI/blob/main/LICENSE).

---

## 📬 Contact

Have questions or need help? Join out [Discord](https://discord.gg/ZKfh5ycJrU) Server or Join the conversation in the [issues section](https://github.com/ninjaknights/DrawerAPI/issues).
