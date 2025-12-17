<?php
declare(strict_types=1);
namespace ninjaknights\drawerAPI\shapes;

use ninjaknights\drawerAPI\DrawerAPI;
use ninjaknights\drawerAPI\ShapeType;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\World;
use pocketmine\network\mcpe\protocol\DebugDrawerPacket;
use pocketmine\network\mcpe\protocol\types\PacketShapeData;
use pocketmine\network\mcpe\protocol\types\ScriptDebugShapeType;

class Circle {

	/**
	 * Creates a Circle shape in the world or for a player.
	 * 
	 * @param World|Player $viewer The world or player that will see the Circle Shape.
	 * @param Vector3|null $position The position where the Circle will be displayed. Defaults to the viewer's position.
	 * @param float|null $size The size of the Circle. Defaults to `1.0`.
	 * @param string|null $color The color of the Circle. Defaults to "white". (Accepts HexCode eg: `#f0f0f0`)
	 * @param int|null $segments The number of segments for the Circle. Defaults to `20` (not sure).
	 * @param float|null $lifeSpan The total initial time-span (in seconds) until this shape is automatically removed. Defaults to `null`.
	 * @throws \LogicException if DrawerAPI is not registered.
	 */
	public static function create(
		World|Player $viewer,
		Vector3|null $position = null,
		float|null $size = null,
		string|null $color = null,
		int|null $segments = null,
		?float $lifeSpan = null
	): ?int {
		if(!DrawerAPI::isRegistered()) {
			throw new \LogicException("Cannot call Circle::create before calling register");
		}
		$pos = ($position === null) ? ($viewer instanceof Player ? $viewer->getPosition() : null) : $position;
		$id = DrawerAPI::generateId(ShapeType::CIRCLE);
		DrawerAPI::sendPacket($viewer, DebugDrawerPacket::create([
			new PacketShapeData(
				networkId: $id,
				type: ScriptDebugShapeType::CIRCLE,
				location: $pos,
				scale: $size ?? 1.0,
				rotation: null,
				totalTimeLeft: $lifeSpan,
				color: DrawerAPI::getColor($color),
				text: null,
				boxBound: null,
				lineEndLocation: null,
				arrowHeadLength: null,
				arrowHeadRadius: null,
				segments: $segments ?? 20
			)])
		);
		return $id;
	}

	/**
	 * Removes a specific Circle shape by its ID.
	 *
	 * @param World|Player $viewer The world or player from which to remove the Circle shape.
	 * @param int $id The ID of the Circle shape to remove.
	 * @throws \LogicException if DrawerAPI is not registered.
	 */
	public static function removeById(World|Player $viewer, int $id): void {
		if(!DrawerAPI::isRegistered()) {
			throw new \LogicException("Cannot call Circle::removeById before DrawerAPI is registered");
		}
		DrawerAPI::despawnPacketByID($viewer, $id);
		DrawerAPI::removeId(ShapeType::CIRCLE, $id);
	}
}