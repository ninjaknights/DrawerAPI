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

class Text {

	/**
	 * Creates a text shape in the world or for a player.
	 *
	 * @param World|Player $viewer The world or player where the text will be displayed.
	 * @param Vector3|null $position The position where the text will be displayed. Defaults to the viewer's position.
	 * @param string|null $text The text to display.
	 * @param float|null $size The size of the text. Defaults to 1.0.
	 * @param string|null $color The color of the text. Defaults to "white". (Accepts HexCode eg: #f0f0f0)
	 *
	 * @throws \LogicException if DrawerAPI is not registered.
	 */
	public static function create(
		World|Player $viewer,
		?Vector3 $position = null,
		?string $text = null,
		?float $size = null,
		?string $color = null
	): ?int {
		if(!DrawerAPI::isRegistered()) {
			throw new \LogicException("Cannot call Text::create before DrawerAPI is registered");
		}
		$pos = ($position === null) ? ($viewer instanceof Player ? $viewer->getPosition() : null) : $position;
		// https://learn.microsoft.com/en-us/minecraft/creator/scriptapi/minecraft/debug-utilities/debugshape?view=minecraft-bedrock-experimental#totaltimeleft
		// apparently totalTimeLeft is a real only method
		$id = DrawerAPI::generateId(ShapeType::TEXT);
		DrawerAPI::sendPacket($viewer, ServerScriptDebugDrawerPacket::create([
			new PacketShapeData(
				networkId: $id,
				type: ScriptDebugShapeType::TEXT,
				location: $pos,
				scale: $size ?? 1.0,
				rotation: null,
				totalTimeLeft: null,
				color: DrawerAPI::getColor($color),
				text: $text,
				boxBound: null,
				lineEndLocation: null,
				arrowHeadLength: null,
				arrowHeadRadius: null,
				segments: null
			)
		]));
		return $id;
	}

	/**
	 * Removes a specific text shape by its ID.
	 *
	 * @param World|Player $viewer The world or player from which to remove the text shape.
	 * @param int $id The ID of the text shape to remove.
	 * @throws \LogicException if DrawerAPI is not registered.
	 */
	public static function removeById(World|Player $viewer, int $id): void {
		if(!DrawerAPI::isRegistered()) {
			throw new \LogicException("Cannot call Text::removeById before DrawerAPI is registered");
		}
		DrawerAPI::despawnPacketByID($viewer, $id);
		DrawerAPI::removeId(ShapeType::TEXT, $id);
	}
}