<?php

declare(strict_types=1);

namespace NinjaKnights\DrawerAPI\Shapes;

use NinjaKnights\DrawerAPI\DrawerAPI;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\World;
use pocketmine\network\mcpe\protocol\ServerScriptDebugDrawerPacket;
use pocketmine\network\mcpe\protocol\types\PacketShapeData;
use pocketmine\network\mcpe\protocol\types\ScriptDebugShapeType;

class Line {

	/**
	 * Creates a line shape in the world or for a player.
	 * 
	 * @param World|Player $viewer The world or player where the line will be displayed.
	 * @param Vector3|null $position The position where the line will start. Defaults to the viewer's position.
	 * @param Vector3|null $endLinePosition The position where the line will end. Defaults to null.
	 * @param float|null $size The size of the line. Defaults to 1.0.
	 * @param string|null $color The color of the line. Defaults to "white".
	 * 
	 * @throws \LogicException if DrawerAPI is not registered.
	*/
    public static function create(
        World|Player $viewer,
		Vector3|null $position = null,
		Vector3|null $endLinePosition = null,
		float|null $size = null,
		string|null $color = null
	): ?int {
		if(!DrawerAPI::isRegistered()) {
			throw new \LogicException("Cannot call Line::create before calling register");
		}
		$id = DrawerAPI::getInstance()->generateId("line");
		DrawerAPI::sendPacket($viewer, ServerScriptDebugDrawerPacket::create([
			new PacketShapeData(
				networkId: $id,
				type: ScriptDebugShapeType::LINE,
				location: $position ?? $viewer->getPosition(),
				scale: $size ?? 1.0,
				rotation: null,
				totalTimeLeft: null,
				color: DrawerAPI::getColor($color),
				text: null,
				boxBound: null,
				lineEndLocation: $endLinePosition,
				arrowHeadLength: null,
				arrowHeadRadius: null,
				segments: null
			)])
		);
		return $id;
    }

	/**
	 * Removes a specific line shape by its ID.
	 *
	 * @param World|Player $viewer The world or player from which to remove the line shape.
	 * @param int $id The ID of the line shape to remove.
	 * 
	 * @throws \LogicException if DrawerAPI is not registered.
	 */
	public static function removeById(World|Player $viewer, int $id): void {
		if(!DrawerAPI::isRegistered()) {
			throw new \LogicException("Cannot call Line::removeById before DrawerAPI is registered");
		}
		DrawerAPI::despawnPacketByID($viewer, $id);
		DrawerAPI::removeId("line", $id);
	}
}