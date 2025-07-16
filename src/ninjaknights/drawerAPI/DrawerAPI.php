<?php

declare(strict_types=1);

namespace ninjaknights\drawerAPI;

use ninjaknights\drawerAPI\ShapeType;
use pocketmine\color\Color;
use pocketmine\player\Player;
use pocketmine\world\World;
use pocketmine\plugin\PluginBase;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\protocol\ServerScriptDebugDrawerPacket;
use pocketmine\network\mcpe\protocol\types\PacketShapeData;

class DrawerAPI {

	/** @var bool */
	protected static bool $registered = false;
	/** @var PluginBase|null */
	private static ?PluginBase $plugin = null;
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
		if(!self::$registered){
			throw new \LogicException("DrawerAPI must be registered before use.");
		}
	}

	/**
	 * Registers the DrawerAPI with the given plugin.
	 * This method should be called once during plugin initialization.
	 * @param PluginBase $plugin The plugin instance to register with.
	 * @throws \LogicException if DrawerAPI is already registered.
	 */
	public static function register(PluginBase $plugin): void {
		if(self::$registered){
			throw new \LogicException("DrawerAPI is already registered");
		}
		foreach(ShapeType::cases() as $type){
			self::$idsCount[$type->value] = 0;
			self::$activeIds[$type->value] = [];
		}
		self::$plugin = $plugin;
		self::$registered = true;
	}

	/**
	 * Gets the Color object for a given color name.
	 * If a named color is provided (e.g., "red", "blue"), it returns the matching Color object.
	 * If a hex color is provided (e.g., "#ffffff" or "ffffff"), it parses and returns the corresponding Color.
	 * If no color is provided or it's invalid, it defaults to white.
	 * @param string|null $color The name or hex code of the color.
	 * @return Color The corresponding Color object.
	*/
	public static function getColor(?string $color = null): Color {
		self::checkRegistered();
		if($color === null) return new Color(0xf0, 0xf0, 0xf0);
		$color = strtolower(trim($color));
		return match($color){
			"white" => new Color(0xf0, 0xf0, 0xf0),
			"orange" => new Color(0xf9, 0x80, 0x1d),
			"magenta" => new Color(0xc7, 0x4e, 0xbd),
			"light_blue" =>new Color(0x3a, 0xb3, 0xda),
			"yellow" => new Color(0xfe, 0xd8, 0x3d),
			"lime" => new Color(0x80, 0xc7, 0x1f),
			"pink" => new Color(0xf3, 0x8b, 0xaa),
			"gray" => new Color(0x47, 0x4f, 0x52),
			"light_gray" => new Color(0x9d, 0x9d, 0x97),
			"cyan" => new Color(0x16, 0x9c, 0x9c),
			"purple" => new Color(0x89, 0x32, 0xb8),
			"blue" => new Color(0x3c, 0x44, 0xaa),
			"brown" => new Color(0x83, 0x54, 0x32),
			"green" => new Color(0x5e, 0x7c, 0x16),
			"red" => new Color(0xb0, 0x2e, 0x26),
			"black" => new Color(0x1d, 0x1d, 0x21),
			default => self::fromHex($color) ?? new Color(0xf0, 0xf0, 0xf0)
		};
	}

	/**
	 * Parses a hex color code and returns a Color object.
	 * @param string $hex Hex string (e.g. "#ff0000" or "ff0000").
	 * @return Color|null
	 */
	private static function fromHex(string $hex): ?Color{
		self::checkRegistered();
		$hex = ltrim($hex, "#");
		if(!preg_match('/^[0-9a-f]{6}$/i', $hex)){
			return null;
		}
		return new Color(
			(int) hexdec(substr($hex, 0, 2)),
			(int) hexdec(substr($hex, 2, 2)),
			(int) hexdec(substr($hex, 4, 2))
		);
	}

	/**
	 * Sends a ServerScriptDebugDrawerPacket to the specified viewer or world.
	 * If the viewer is a Player, it sends the packet to that player.
	 * If the viewer is a World, it broadcasts the packet to all players in that world.
	 * @param World|Player $viewer The viewer or world to send the packet to.
	 * @param ServerScriptDebugDrawerPacket $packet The packet to send.
	 */
	public static function sendPacket(
		World|Player $viewer,
		ServerScriptDebugDrawerPacket $packet
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
		NetworkBroadcastUtils::broadcastPackets($targets, [ServerScriptDebugDrawerPacket::create([PacketShapeData::remove($id)])]);
	}

	/**
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
		if(isset(self::$activeIds[$type->value][PHP_INT_MAX])){
			throw new \RuntimeException("ID overflow for type: $type->value");
		}
		$id = ++self::$idsCount[$type->value];
		self::$activeIds[$type->value][$id] = true;
		self::$plugin?->getLogger()->debug("Generated ID {$id} for type {$type->value}");
		return $id;
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
		self::$plugin?->getLogger()->debug("Removed ID {$id} for type {$type->value}");
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