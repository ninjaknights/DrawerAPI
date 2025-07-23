# DrawerAPI
[![GitHub license](https://img.shields.io/github/license/ninjaknights/DrawerAPI)](https://github.com/ninjaknights/DrawerAPI/blob/main/LICENSE)
[![GitHub stars](https://img.shields.io/github/stars/ninjaknights/DrawerAPI)](https://github.com/ninjaknights/DrawerAPI/stargazers)
[![GitHub forks](https://img.shields.io/github/forks/ninjaknights/DrawerAPI)](https://github.com/ninjaknights/DrawerAPI/network/members)
[![GitHub issues](https://img.shields.io/github/issues/ninjaknights/DrawerAPI)](https://github.com/ninjaknights/DrawerAPI/issues)
[![Github downloads](https://img.shields.io/github/downloads/ninjaknights/DrawerAPI/total)](https://github.com/ninjaknights/DrawerAPI/releases)

<p align="center">
	<a href="https://github.com/ninjaknights/DrawerAPI">
	<img src="assets/icon.png?raw=true" alt="DrawerAPI Icon" width="150" /></a><br>
	<b>DrawerAPI</b> is a PocketMine-MP virion designed to draw shapes and text in the world using the <code>ServerScriptDebugDrawerPacket</code>.
	<br>
	<a href="https://learn.microsoft.com/en-us/minecraft/creator/scriptapi/minecraft/debug-utilities/debugshape?view=minecraft-bedrock-experimental">View on Microsoft Docs</a>
</p>

---

## Setup
Before using the API, you **must register** it in your plugin’s `onLoad()` or `onEnable()` method:
```php
use ninjaknights\drawerAPI\DrawerAPI;
class MyPlugin extends PluginBase {
	public function onLoad(): void {
		if(!DrawerAPI::isRegistered()){
			DrawerAPI::register($this);
		}
	}
}
```

---

#### Creating Shapes
You can draw various shapes using the provided shape classes.

### ➤ TextShape
```php
use ninjaknights\drawerAPI\shape\Text;
// Create a text shape
Text::create(
	$world, // Player/World Players to show the text to
	"Hello, World!", // The text to display
	new Vector3(0, 100, 0), // Position in the world or set to null to use player's position
	ShapeColor::WHITE, // Color name "red" or #f0f0f0, f0f0f0 or ShapeColor enum works too
);
```

### ➤ CircleShape
```php
use ninjaknights\drawerAPI\shape\Circle;
// Create a text shape
Circle::create(
	$world, // Player/World Players to show the text to
	new Vector3(0, 100, 0), // Position in the world or set to null to use player's position
	1.0, // Scale of the text
	"#ff0000", // Color name or #f0f0f0, f0f0f0 or ShapeColor enum works too
	3 // 3 means it will have 3 sides so a triangle, default is 20 (not sure)
);
```

---

## Available Shapes
| Shape     | Description                            |
|-----------|----------------------------------------|
| `Text`    | Renders a floating text label          |
| `Arrow`   | Renders a directional arrow            |
| `Box`     | Renders a 3D bounding box              |
| `Line`    | Renders a straight line between points |
| `Sphere`  | Renders a full sphere                  |
| `Circle`  | Renders a circle or polygon            |

## Available Colors
You can use color **names**, **enum**, or **hex codes** (e.g. `"red"`, `"light_gray"`, `"#ff0000"`, `"f0f0f0"`).  
All names are **case-insensitive**.

| Color Name     | Hex Code   | Enum                        |
|----------------|------------|-----------------------------|
| `white`        | `#f0f0f0`  | `ShapeColor::WHITE`         |
| `orange`       | `#f9801d`  | `ShapeColor::ORANGE`        |
| `magenta`      | `#c74ebd`  | `ShapeColor::MAGENTA`       |
| `light_blue`   | `#3ab3da`  | `ShapeColor::LIGHT_BLUE`    |
| `yellow`       | `#fed83d`  | `ShapeColor::YELLOW`        |
| `lime`         | `#80c71f`  | `ShapeColor::LIME`          |
| `pink`         | `#f38baa`  | `ShapeColor::PINK`          |
| `gray`         | `#474f52`  | `ShapeColor::GRAY`          |
| `light_gray`   | `#9d9d97`  | `ShapeColor::LIGHT_GRAY`    |
| `cyan`         | `#169c9c`  | `ShapeColor::CYAN`          |
| `purple`       | `#8932b8`  | `ShapeColor::PURPLE`        |
| `blue`         | `#3c44aa`  | `ShapeColor::BLUE`          |
| `brown`        | `#835432`  | `ShapeColor::BROWN`         |
| `green`        | `#5e7c16`  | `ShapeColor::GREEN`         |
| `red`          | `#b02e26`  | `ShapeColor::RED`           |
| `black`        | `#1d1d21`  | `ShapeColor::BLACK`         |

---

## Managing Shapes
### ➤ Clear Shapes
You can clear all shapes of a specific type for a viewer or world using the `clearAll` method:
```php
use ninjaknights\drawerAPI\DrawerAPI;
DrawerAPI::clearAll(ShapeType::TEXT, $world); // Clears all text shapes for the specified world
DrawerAPI::clearAll(ShapeType::ARROW, $player); // Clears all arrow shapes for the specified player
```

### ➤ Remove Specific Shape by ID
```php
use ninjaknights\drawerAPI\shape\Text;
Text::removeById($world, 1); // Removes the text shape with ID 1 for the player/world players
```

---

## ID Management
### ➤ Get ID List
```php
use ninjaknights\drawerAPI\DrawerAPI;
use ninjaknights\drawerAPI\ShapeType;

$activeIds = DrawerAPI::getIdList(ShapeType::LINE);
foreach ($activeIds as $id) {
	echo "Line shape active with ID: $id\n";
}
```

### ➤ Check if an ID is Active
```php
DrawerAPI::isActiveId(ShapeType::CIRCLE, 5); // Returns true/false
```

### ➤ Get Last Generated ID
```php
$lastId = DrawerAPI::getId(ShapeType::BOX);
```

---

## API Reference
### ➤ DrawerAPI Methods
| Method | Description |
|--------|-------------|
| `register(PluginBase $plugin)` | Initializes and registers the DrawerAPI. Required before use. |
| `isRegistered(): bool` | Returns `true` if the API is initialized. |
| `getColor(?string $color): Color` | Converts a color name or hex or enum (e.g., `"red"`, `"#ff0000"`, `"ShapeColor::WHITE"`) to a `Color` object. |
| `getId(ShapeType $type): int` | Gets the last generated ID for a shape type. |
| `isActiveId(ShapeType $type, int $id): bool` | Checks if an ID is currently active. |
| `getIdList(ShapeType $type): array<int>` | Lists all currently active IDs for a shape type. |
| `removeId(ShapeType $type, int $id): void` | Manually unregisters an ID (usually done automatically). |
| `clearAll(World\|Player $viewer, ?ShapeType $type): void` | Despawns and removes all shapes of a specific type or all types. |
| `despawnPacketByID(World\|Player $viewer, int $id): void` | Removes a single shape from view by its ID. |
| `sendPacket(World\|Player $viewer, ServerScriptDebugDrawerPacket $packet): void` | Sends a custom packet to a player or world. |

### ➤ ShapeColor Methods
| Method | Description |
|--------|-------------|
| `ShapeColor::fromString(string $name): ?ShapeColor` | Converts a color name into a `ShapeColor` enum. Returns `null` if invalid. |
| `ShapeColor::("COLOR")->toColor(): Color` | Converts the enum value to a `pocketmine\color\Color` instance. |
| `ShapeColor::("COLOR")->name` | Returns the constant name (e.g., `WHITE`, `RED`, etc.). |
| `ShapeColor::("COLOR")->value` | Returns the constant value (e.g., `white`, `red`, etc.). |

```php
$pmColor = ShapeColor::WHITE->toColor(); // pocketmine\color\Color
// or
$color = ShapeColor::fromString("cyan");
if ($color !== null) {
	$pmColor = $color->toColor(); // pocketmine\color\Color
}
```

---

## Notes

- Color names are case-insensitive and use the `ShapeColor` enum internally.
- Hex values like `"#ffffff"` or `"ff0000"` are also accepted.
- Either store the id when using `create()` as it returns the id or use `getId`

---

## 📄 License

This project is licensed under the [GPL-3.0 License](https://github.com/ninjaknights/DrawerAPI/blob/main/LICENSE).

---

## 📬 Contact

- Have questions or need help? Join out [Discord](https://discord.gg/ZKfh5ycJrU) Server
- Found a bug or wish to suggest some changes? [Open an issue](https://github.com/ninjaknights/DrawerAPI/issues)
