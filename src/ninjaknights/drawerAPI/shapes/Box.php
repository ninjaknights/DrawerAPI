<?php

declare(strict_types=1);

namespace ninjaknights\drawerAPI\shapes;

use ninjaknights\drawerAPI\DrawerAPI;
use ninjaknights\drawerAPI\ShapeType;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\World;
use pocketmine\network\mcpe\protocol\ServerScriptDebugDrawerPacket;
use pocketmine\network\mcpe\protocol\types\PacketShapeData;
use pocketmine\network\mcpe\protocol\types\ScriptDebugShapeType;

class Box {

	/**
	 * Creates a box shape in the world or for a player.
	 * 
	 * @param World|Player $viewer The world or player where the box will be displayed.
	 * @param Vector3|null $position The position where the box will be displayed. Defaults to the viewer's position.
	 * @param Vector3|null $boxBound The bounding box of the box. Defaults to null.
	 * @param float|null $size The size of the box. Defaults to 1.0.
	 * @param string|null $color The color of the box. Defaults to "white". (Accepts HexCode eg: #f0f0f0)
	 * @throws \LogicException if DrawerAPI is not registered.
	 */
	public static function create(
		World|Player $viewer,
		Vector3|null $position = null,
		Vector3|null $boxBound = null,
		float|null $size = null,
		string|null $color = null
	): ?int {
		if(!DrawerAPI::isRegistered()) {
			throw new \LogicException("Cannot call Box::create before calling register");
		}
		$pos = ($position === null) ? ($viewer instanceof Player ? $viewer->getPosition() : null) : $position;
		$id = DrawerAPI::generateId(ShapeType::BOX);
		DrawerAPI::sendPacket($viewer, ServerScriptDebugDrawerPacket::create([
			new PacketShapeData(
				networkId: $id,
				type: ScriptDebugShapeType::BOX,
				location: $pos,
				scale: $size ?? 1.0,
				rotation: null,
				totalTimeLeft: null,
				color: DrawerAPI::getColor($color),
				text: null,
				boxBound: $boxBound,
				lineEndLocation: null,
				arrowHeadLength: null,
				arrowHeadRadius: null,
				segments: null
			)])
		);
		return $id;
	}

	/**
	 * Removes a specific box shape by its ID.
	 *
	 * @param World|Player $viewer The world or player from which to remove the box shape.
	 * @param int $id The ID of the box shape to remove.
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