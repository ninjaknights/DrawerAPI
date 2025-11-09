<?php
declare(strict_types=1);
namespace ninjaknights\drawerAPI;

use ninjaknights\drawerAPI\ShapeType;
use ninjaknights\drawerAPI\ShapeColor;
use pocketmine\color\Color;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\protocol\DebugDrawerPacket;
use pocketmine\network\mcpe\protocol\types\PacketShapeData;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\world\World;

final class DrawerAPI {

	/** @var bool */
	protected static bool $registered = false;
	/** @var PluginBase|null */
	public static PluginBase|null $plugin = null;
	/** @var array<string, int> */
	private static array $idsCount = [];
	/** @var array<string, array<int, bool>> */
	private static array $activeIds = [];

	/**
	 * Checks if the DrawerAPI is registered.
	 * @return bool True if registered, false otherwise.
	 */
	public static function isRegistered(): bool {
		return self::$registered;
	}

	/**
	 * Checks if the DrawerAPI is registered and throws an exception if not.
	 * @throws \LogicException if DrawerAPI is not registered.
	 */
	public static function checkRegistered(): void {
		if(!self::$registered) throw new \LogicException("DrawerAPI must be registered before use.");
	}

	/**
	 * Gets the registered plugin instance.
	 * @return PluginBase|null The registered plugin instance, or null if not registered.
	 */
	public static function getPlugin(): PluginBase|null {
		self::checkRegistered();
		return self::$plugin;
	}

	/**
	 * Registers the DrawerAPI with the given plugin.
	 * This method should be called once during plugin initialization.
	 * @param PluginBase $plugin The plugin instance to register with.
	 * @throws \LogicException if DrawerAPI is already registered.
	 */
	public static function register(PluginBase $plugin): void {
		if(self::$registered) throw new \LogicException("DrawerAPI is already registered");
		foreach(ShapeType::cases() as $type){
			self::$idsCount[$type->value] = 0;
			self::$activeIds[$type->value] = [];
		}
		self::$plugin = $plugin;
		self::$registered = true;
	}

	/**
	 * Unregisters the DrawerAPI.
	 */
	public static function unregister(): void {
		self::$plugin = null;
		self::$registered = false;
	}

	/**
	 * converts a string into a Color object.
	 * Accepts:
	 * - Named colors (e.g., "red", "blue", "green") (**case-insensitive**).
	 * - Hex color codes in formats like `#ffffff` or `ffffff`.
	 * - `ShapeColor` enum (e.g., `ShapeColor::RED`)
	 * If the color is null or invalid, it defaults to white.
	 * @param ?string $color Name / HexCode / ShapeColor of the color.
	 * @return Color The corresponding Color object.
	*/
	public static function getColor(?string $color = null): Color {
		self::checkRegistered();
		if($color === null) return ShapeColor::WHITE->toColor();
		$color = strtolower(trim($color));
		$data = ShapeColor::fromString($color);
		if($data !== null) return $data->toColor();
		return self::fromHex($color) ?? ShapeColor::WHITE->toColor();
	}

	/**
	 * **INTERNAL USE ONLY**
	 * 
	 * Parses a 6-character hexadecimal color string into a Color object.
	 * Accepts formats like "#ff0000" or "ff0000" (case-insensitive).
	 * Returns null if the input is not a valid 6-digit hex color.
	 * @param string $hex Hexadecimal color string.
	 * @return Color|null
	 */
	private static function fromHex(string $hex): Color|null {
		self::checkRegistered();
		$hex = ltrim($hex, "#");
		if(!preg_match('/^[0-9a-f]{6}$/i', $hex)) return null;
		return new Color(
			(int) hexdec(substr($hex, 0, 2)),
			(int) hexdec(substr($hex, 2, 2)),
			(int) hexdec(substr($hex, 4, 2))
		);
	}

	/**
	 * Sends a DebugDrawerPacket to the specified viewer or world.
	 * If the viewer is a Player, it sends the packet to that player.
	 * If the viewer is a World, it broadcasts the packet to all players in that world.
	 * @param World|Player $viewer The viewer or world to send the packet to.
	 * @param DebugDrawerPacket $packet The packet to send.
	 */
	public static function sendPacket(
		World|Player $viewer,
		DebugDrawerPacket $packet
	): void {
		self::checkRegistered();
		$targets = $viewer instanceof Player ? [$viewer] : $viewer->getPlayers();
		NetworkBroadcastUtils::broadcastPackets($targets, [$packet]);
		return;
	}

	/**
	 * Despawns a shape packet by its ID for the specified viewer or world.
	 * If the viewer is a Player, it despawns the shape for that player.
	 * If the viewer is a World, it despawns the shape for all players in that world.
	 * @param World|Player $viewer The viewer or world from which to despawn the shape.
	 * @param int $id The ID of the shape to despawn.
	 */
	public static function despawnPacketByID(
		World|Player $viewer,
		int $id
	): void {
		self::checkRegistered();
		$targets = $viewer instanceof Player ? [$viewer] : $viewer->getPlayers();
		NetworkBroadcastUtils::broadcastPackets($targets, [DebugDrawerPacket::create([PacketShapeData::remove($id)])]);
		return;
	}

	/**
	 * **INTERNAL USE ONLY**
	 * 
	 * Generates a new ID for a specific type of shape.
	 * The ID is incremented from the last used ID for that type.
	 * @param ShapeType|null $type The type of shape for which to generate an ID.
	 * @return int The generated ID.
	 * @throws \InvalidArgumentException  if the ShapeType is not mentioned via ShapeType Enum.
	 * @throws \RuntimeException if the ID overflow occurs for the specified type.
	 */
	public static function generateId(ShapeType|null $type = null): int {
		self::checkRegistered();
		if(is_null($type)) throw new \InvalidArgumentException ("Specify a ShapeType Enum");
		if(isset(self::$activeIds[$type->value][PHP_INT_MAX])) throw new \RuntimeException("ID overflow for type: $type->value");
		$id = ++self::$idsCount[$type->value];
		self::$activeIds[$type->value][$id] = true;
		self::$plugin?->getLogger()->debug("Generated ID {$id} for type {$type->value}");
		return $id;
	}

	/**
	 * Gets the current highest allocated ID for the given shape type.
	 * This method returns the last issued ID for the specified `ShapeType`,
	 * even if it has been removed.
	 * @param ShapeType|null $type The type of shape to get the last ID for.
	 * @return int The last issued ID for the shape type, or 0 if none exist.
	 * @throws \InvalidArgumentException If no ShapeType is provided.
	 */
	public static function getId(ShapeType|null $type = null): int {
		self::checkRegistered();
		if($type === null) throw new \InvalidArgumentException("Specify a ShapeType Enum.");
		return self::$idsCount[$type->value] ?? 0;
	}

	/**
	 * Checks if a specific ID is currently active for the given shape type.
	 * @param ShapeType|null $type The type of shape.
	 * @param int $id The ID to check.
	 * @return bool True if the ID is active, false otherwise.
	 * @throws \InvalidArgumentException If the ShapeType is not provided.
	 */
	public static function isActiveId(ShapeType|null $type = null, int $id): bool {
		self::checkRegistered();
		if($type === null) throw new \InvalidArgumentException("Specify a ShapeType Enum.");
		return isset(self::$activeIds[$type->value][$id]);
	}

	/**
	 * Gets the list of active IDs for a specific type of shape.
	 * This method returns an array of IDs that are currently active for the specified type.
	 * @param ShapeType|null $type The type of shape for which to get the active IDs.
	 * @return array<int> An array of active IDs for the specified type.
	 * @throws \InvalidArgumentException  if the ShapeType is not mentioned via ShapeType Enum.
	 * @throws \RuntimeException if there are no active IDs for the specified type.
	 */
	public static function getIdList(ShapeType|null $type = null): array {
		self::checkRegistered();
		if(is_null($type)) throw new \InvalidArgumentException ("Specify a ShapeType Enum");
		if(!isset(self::$activeIds[$type->value])){
			throw new \RuntimeException("No active IDs for type {$type->value}");
		}
		return array_keys(self::$activeIds[$type->value]);
	}

	/**
	 * Removes an ID for a specific type of shape.
	 * This method should be called when the shape is no longer needed.
	 * @param ShapeType|null $type The type of shape for which to remove the ID.
	 * @param int $id The ID to remove.
	 * @throws \InvalidArgumentException  if the ShapeType is not mentioned via ShapeType Enum.
	 * @throws \RuntimeException if the ID does not exist for the specified type or if the ID count goes negative.
	 */
	public static function removeId(ShapeType|null $type = null, int $id): void {
		self::checkRegistered();
		if(is_null($type)) throw new \InvalidArgumentException ("Specify a ShapeType Enum");
		if(!isset(self::$activeIds[$type->value][$id])){
			throw new \RuntimeException("Cannot remove non-existent ID {$id} for type {$type->value}");
		}
		unset(self::$activeIds[$type->value][$id]);
		if(self::$idsCount[$type->value] === $id){
			self::$idsCount[$type->value]--;
		}
		if(self::$idsCount[$type->value] < 0){
			throw new \RuntimeException("ID count for type {$type->value} went negative");
		}
		self::$plugin?->getLogger()->debug("Removed Shape ID {$id} for type {$type->value}");
	}

	/**
	 * Clears all active shapes of a specific type for a viewer or world.
	 * This method despawns all shapes of the specified type and removes their IDs.
	 * @param World|Player $viewer The viewer or world from which to clear the shapes.
	 * @param ShapeType|null $type The type of shapes to clear, by default `null` means all shapes.
	 */
	public static function clearAll(World|Player $viewer, ShapeType|null $type = null): void {
		self::checkRegistered();
		if($type !== null){
			foreach(self::$activeIds[$type->value] ?? [] as $id => $_){
				self::despawnPacketByID($viewer, $id);
				self::removeId($type, $id);
			}
		}else{
			foreach(ShapeType::cases() as $shapeType){
				foreach (self::$activeIds[$shapeType->value] ?? [] as $id => $_) {
					self::despawnPacketByID($viewer, $id);
					self::removeId($shapeType, $id);
				}
			}
		}
	}
}