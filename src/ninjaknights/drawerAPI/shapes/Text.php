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

class Text {

	/**
	 * Creates a Text shape in the world or for a player.
	 * The text label automatically faces the screen of the player.
	 *
	 * @param World|Player $viewer The world or player that will see the Text Display.
	 * @param Vector3|null $position The position where the Text will be displayed. Defaults to the viewer's position.
	 * @param string|null $text The text to display.
	 * @param string|null $color The color of the Text. Defaults to "white". (Accepts HexCode eg: `#f0f0f0`)
	 * @param float|null $size The size of the Text. Defaults to `null`.
	 * @param float|null $lifeSpan The total initial time-span (in seconds) until this shape is automatically removed. Defaults to `null`.
	 * @throws \LogicException if DrawerAPI is not registered.
	 */
	public static function create(
		World|Player $viewer,
		?Vector3 $position = null,
		?string $text = null,
		?string $color = null,
		?float $size = null,
		?float $lifeSpan = null
	): ?int {
		if(!DrawerAPI::isRegistered()) {
			throw new \LogicException("Cannot call Text::create before DrawerAPI is registered");
		}
		$pos = ($position === null) ? ($viewer instanceof Player ? $viewer->getPosition() : null) : $position;
		$id = DrawerAPI::generateId(ShapeType::TEXT);
		DrawerAPI::sendPacket($viewer, DebugDrawerPacket::create([
			new PacketShapeData(
				networkId: $id,
				type: ScriptDebugShapeType::TEXT,
				location: $pos,
				scale: $size,
				rotation: null,
				totalTimeLeft: $lifeSpan,
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
	 * Removes a specific Text shape by its ID.
	 *
	 * @param World|Player $viewer The world or player from which to remove the Text shape.
	 * @param int $id The ID of the Text shape to remove.
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