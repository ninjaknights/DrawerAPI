<?php

declare(strict_types=1);

namespace ninjaknights\drawerAPI\shapes;

use ninjaknights\drawerAPI\DrawerAPI;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\World;
use pocketmine\network\mcpe\protocol\ServerScriptDebugDrawerPacket;
use pocketmine\network\mcpe\protocol\types\PacketShapeData;
use pocketmine\network\mcpe\protocol\types\ScriptDebugShapeType;

class Arrow {

	/**
	 * Creates an arrow shape in the world or for a player.
	 * 
	 * @param World|Player $viewer The world or player where the arrow will be displayed.
	 * @param Vector3|null $position The position where the arrow will start. Defaults to the viewer's position.
	 * @param Vector3|null $endLinePosition The position where the arrow will end. Defaults to null.
	 * @param float|null $size The size of the arrow. Defaults to 1.0.
	 * @param float|null $arrowHeadLength The length of the arrow head. Defaults to 0.5.
	 * @param float|null $arrowHeadRadius The radius of the arrow head. Defaults to 0.1.
	 * @param string|null $color The color of the arrow. Defaults to "white". (Accepts HexCode eg: #f0f0f0)
	 * @param int|null $segments The number of segments for the arrow. Defaults to 4.
	 * 
	 * @throws \LogicException if DrawerAPI is not registered.
	 */
	public static function create(
		World|Player $viewer,
		Vector3|null $position = null,
		Vector3|null $endLinePosition = null,
		float|null $size = null,
		float|null $arrowHeadLength = null,
		float|null $arrowHeadRadius = null,
		string|null $color = null,
		int|null $segments = null
	): ?int {
		if(!DrawerAPI::isRegistered()) {
			throw new \LogicException("Cannot call Arrow::create before calling register");
		}
		$pos = ($position === null) ? ($viewer instanceof Player ? $viewer->getPosition() : null) : $position;
		$id = DrawerAPI::generateId("arrow");
		DrawerAPI::sendPacket($viewer, ServerScriptDebugDrawerPacket::create([
			new PacketShapeData(
				networkId: $id,
				type: ScriptDebugShapeType::ARROW,
				location: $pos,
				scale: $size ?? 1.0,
				rotation: null,
				totalTimeLeft: null,
				color: DrawerAPI::getColor($color),
				text: null,
				boxBound: null,
				lineEndLocation: $endLinePosition,
				arrowHeadLength: $arrowHeadLength,
				arrowHeadRadius: $arrowHeadRadius,
				segments: $segments ?? 4
			)])
		);
		return $id;
	}

	/**
	 * Removes a specific arrow shape by its ID.
	 *
	 * @param World|Player $viewer The world or player from which to remove the arrow shape.
	 * @param int $id The ID of the arrow shape to remove.
	 * @throws \LogicException if DrawerAPI is not registered.
	 */
	public static function removeById(World|Player $viewer, int $id): void {
		if(!DrawerAPI::isRegistered()) {
			throw new \LogicException("Cannot call Arrow::removeById before DrawerAPI is registered");
		}
		DrawerAPI::despawnPacketByID($viewer, $id);
		DrawerAPI::removeId("arrow", $id);
	}
}