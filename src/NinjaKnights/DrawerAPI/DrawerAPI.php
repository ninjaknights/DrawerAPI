<?php

declare(strict_types=1);

namespace ninjaknights\drawerAPI;

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
	 *
	 * @param PluginBase $plugin The plugin instance to register with.
	 * @throws \RuntimeException if DrawerAPI is already registered.
	 */
	public static function register(PluginBase $plugin): void {
		if(self::$registered){
			throw new \RuntimeException("DrawerAPI is already registered");
		}
		foreach(["arrow", "text", "circle", "sphere", "box", "line"] as $type){
			self::$idsCount[$type] = 0;
			self::$activeIds[$type] = [];
		}
		self::$plugin = $plugin;
		self::$registered = true;
	}

	/**
	 * Gets the Color object for a given color name.
	 * If the color name is not recognized, it defaults to white.
	 * @param string|null $color The name of the color to retrieve.
	 * @return Color The corresponding Color object.
	*/
	public static function getColor(?string $color = null): Color {
		self::checkRegistered();
		// todo: Add more colors
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
			default => new Color(0xf0, 0xf0, 0xf0)
		};
	}

	/**
	 * Sends a ServerScriptDebugDrawerPacket to the specified viewer or world.
	 * If the viewer is a Player, it sends the packet to that player.
	 * If the viewer is a World, it broadcasts the packet to all players in that world.
	 *
	 * @param World|Player $viewer The viewer or world to send the packet to.
	 * @param ServerScriptDebugDrawerPacket|null $packet The packet to send.
	 */
	public static function sendPacket(
		World|Player $viewer,
		?ServerScriptDebugDrawerPacket $packet
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
	 *
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
	 *
	 * @param string $type The type of shape for which to generate an ID.
	 * @return int The generated ID.
	 * @throws \RuntimeException if the ID overflow occurs for the specified type.
	 */
	public static function generateId(string $type): int {
		self::checkRegistered();
		if(isset(self::$activeIds[$type][PHP_INT_MAX])){
			throw new \RuntimeException("ID overflow for type: $type");
		}
		$id = ++self::$idsCount[$type];
		self::$activeIds[$type][$id] = true;
		self::$plugin?->getLogger()->debug("Generated ID {$id} for type {$type}");
		return $id;
	}

	/**
	 * Removes an ID for a specific type of shape.
	 * This method should be called when the shape is no longer needed.
	 *
	 * @param string $type The type of shape for which to remove the ID.
	 * @param int $id The ID to remove.
	 * @throws \RuntimeException if the ID does not exist for the specified type or if the ID count goes negative.
	 */
	public static function removeId(string $type, int $id): void {
		self::checkRegistered();
		if(!isset(self::$activeIds[$type][$id])){
			throw new \RuntimeException("Cannot remove non-existent ID {$id} for type {$type}");
		}
		unset(self::$activeIds[$type][$id]);
		if(self::$idsCount[$type] === $id){
			self::$idsCount[$type]--;
		}
		if(self::$idsCount[$type] < 0){
			throw new \RuntimeException("ID count for type {$type} went negative");
		}
		self::$plugin?->getLogger()->debug("Removed ID {$id} for type {$type}");
	}

	/**
	 * Gets the list of active IDs for a specific type of shape.
	 * This method returns an array of IDs that are currently active for the specified type.
	 *
	 * @param string $type The type of shape for which to get the active IDs.
	 * @return array<int> An array of active IDs for the specified type.
	 * @throws \RuntimeException if there are no active IDs for the specified type.
	 */
	public static function getIdList(string $type): array {
		self::checkRegistered();
		if(!isset(self::$activeIds[$type])){
			throw new \RuntimeException("No active IDs for type {$type}");
		}
		return array_keys(self::$activeIds[$type]);
	}

	/**
	 * Clears all active shapes of a specific type for a viewer or world.
	 * This method despawns all shapes of the specified type and removes their IDs.
	 *
	 * @param World|Player $viewer The viewer or world from which to clear the shapes.
	 * @param string $type The type of shapes to clear.
	 */
	public static function clearAll(World|Player $viewer, string $type): void {
		self::checkRegistered();
		foreach(self::$activeIds[$type] ?? [] as $id => $_){
			self::despawnPacketByID($viewer, $id);
			self::removeId($type, $id);
		}
	}
}