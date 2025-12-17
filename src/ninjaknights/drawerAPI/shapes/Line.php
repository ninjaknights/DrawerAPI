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

class Line {

	/**
	 * Creates a Line shape in the world or for a player.
	 * 
	 * @param World|Player $viewer The world or player that will see the Line Shape.
	 * @param Vector3|null $position The position where the Line will start. Defaults to the viewer's position.
	 * @param Vector3|null $endLinePosition The position where the Line will end. Defaults to `null`.
	 * @param string|null $color The color of the Line. Defaults to "white". (Accepts HexCode eg: `#f0f0f0`)
	 * @param float|null $lifeSpan The total initial time-span (in seconds) until this shape is automatically removed. Defaults to `null`.
	 * @throws \LogicException if DrawerAPI is not registered.
	*/
	public static function create(
		World|Player $viewer,
		Vector3|null $position = null,
		Vector3|null $endLinePosition = null,
		string|null $color = null,
		?float $lifeSpan = null
	): ?int {
		if(!DrawerAPI::isRegistered()) {
			throw new \LogicException("Cannot call Line::create before calling register");
		}
		$pos = ($position === null) ? ($viewer instanceof Player ? $viewer->getPosition() : null) : $position;
		$id = DrawerAPI::generateId(ShapeType::LINE);
		DrawerAPI::sendPacket($viewer, DebugDrawerPacket::create([
			new PacketShapeData(
				networkId: $id,
				type: ScriptDebugShapeType::LINE,
				location: $pos,
				scale: 1.0,
				rotation: null,
				totalTimeLeft: $lifeSpan,
				color: DrawerAPI::getColor($color),
				text: null,
				boxBound: null,
				lineEndLocation: $endLinePosition ?? new Vector3(0, 0, 0),
				arrowHeadLength: null,
				arrowHeadRadius: null,
				segments: null
			)])
		);
		return $id;
	}

	/**
	 * Removes a specific Line shape by its ID.
	 *
	 * @param World|Player $viewer The world or player from which to remove the Line shape.
	 * @param int $id The ID of the Line shape to remove.
	 * @throws \LogicException if DrawerAPI is not registered.
	 */
	public static function removeById(World|Player $viewer, int $id): void {
		if(!DrawerAPI::isRegistered()) {
			throw new \LogicException("Cannot call Line::removeById before DrawerAPI is registered");
		}
		DrawerAPI::despawnPacketByID($viewer, $id);
		DrawerAPI::removeId(ShapeType::LINE, $id);
	}
}