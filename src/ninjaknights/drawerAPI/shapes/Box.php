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

class Box {

	/**
	 * Creates a Box shape in the world or for a player.
	 * 
	 * @param World|Player $viewer The world or player that will see the Box Shape.
	 * @param Vector3|null $position The position where the Box will be displayed. Defaults to the viewer's position.
	 * @param Vector3|null $boxBound The bounding Box of the Box. Defaults to `null`.
	 * @param float|null $size The size of the Box. Defaults to `1.0`.
	 * @param string|null $color The color of the Box. Defaults to "white". (Accepts HexCode eg: `#f0f0f0`)
	 * @param float|null $lifeSpan The total initial time-span (in seconds) until this shape is automatically removed. Defaults to `null`.
	 * @throws \LogicException if DrawerAPI is not registered.
	 */
	public static function create(
		World|Player $viewer,
		Vector3|null $position = null,
		Vector3|null $boxBound = null,
		float|null $size = null,
		string|null $color = null,
		?float $lifeSpan = null
	): ?int {
		if(!DrawerAPI::isRegistered()) {
			throw new \LogicException("Cannot call Box::create before calling register");
		}
		$pos = ($position === null) ? ($viewer instanceof Player ? $viewer->getPosition() : null) : $position;
		$id = DrawerAPI::generateId(ShapeType::BOX);
		DrawerAPI::sendPacket($viewer, DebugDrawerPacket::create([
			new PacketShapeData(
				networkId: $id,
				type: ScriptDebugShapeType::BOX,
				location: $pos,
				scale: $size ?? 1.0,
				rotation: null,
				totalTimeLeft: $lifeSpan,
				color: DrawerAPI::getColor($color),
				text: null,
				boxBound: $boxBound ?? new Vector3(1, 1, 1),
				lineEndLocation: null,
				arrowHeadLength: null,
				arrowHeadRadius: null,
				segments: null
			)])
		);
		return $id;
	}

	/**
	 * Removes a specific Box shape by its ID.
	 *
	 * @param World|Player $viewer The world or player from which to remove the Box shape.
	 * @param int $id The ID of the Box shape to remove.
	 * @throws \LogicException if DrawerAPI is not registered.
	 */
	public static function removeById(World|Player $viewer, int $id): void {
		if(!DrawerAPI::isRegistered()) {
			throw new \LogicException("Cannot call Box::removeById before DrawerAPI is registered");
		}
		DrawerAPI::despawnPacketByID($viewer, $id);
		DrawerAPI::removeId(ShapeType::BOX, $id);
	}
}