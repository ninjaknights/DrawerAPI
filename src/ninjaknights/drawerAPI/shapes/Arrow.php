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

class Arrow {

	/**
	 * Creates an Arrow shape in the world or for a player.
	 * 
	 * @param World|Player $viewer The world or player that will see the Arrow Shape.
	 * @param Vector3|null $position The position where the Arrow will start. Defaults to the viewer's position.
	 * @param float|null $size The size of the Arrow. Defaults to `1.0`.
	 * @param string|null $color The color of the Arrow. Defaults to "white". (Accepts HexCode eg: `#f0f0f0`)
	 * @param Vector3|null $endLinePosition The position where the Arrow will end. Defaults to `null`.
	 * @param float|null $arrowHeadLength The length of the Arrow head. Defaults to `0.5`.
	 * @param float|null $arrowHeadRadius The radius of the Arrow head. Defaults to `0.1`.
	 * @param int|null $segments The number of segments for the Arrow. Defaults to `4`.
	 * @param float|null $lifeSpan The total initial time-span (in seconds) until this shape is automatically removed. Defaults to `null`.
	 * @throws \LogicException if DrawerAPI is not registered.
	 */
	public static function create(
		World|Player $viewer,
		Vector3|null $position = null,
		float|null $size = null,
		string|null $color = null,
		Vector3|null $endLinePosition = null,
		float|null $arrowHeadLength = null,
		float|null $arrowHeadRadius = null,
		int|null $segments = null,
		?float $lifeSpan = null
	): ?int {
		if(!DrawerAPI::isRegistered()) {
			throw new \LogicException("Cannot call Arrow::create before calling register");
		}
		$pos = ($position === null) ? ($viewer instanceof Player ? $viewer->getPosition() : null) : $position;
		$id = DrawerAPI::generateId(ShapeType::ARROW);
		DrawerAPI::sendPacket($viewer, DebugDrawerPacket::create([
			new PacketShapeData(
				networkId: $id,
				type: ScriptDebugShapeType::ARROW,
				location: $pos,
				scale: $size ?? 1.0,
				rotation: null,
				totalTimeLeft: $lifeSpan,
				color: DrawerAPI::getColor($color),
				text: null,
				boxBound: null,
				lineEndLocation: $endLinePosition ?? new Vector3(0, 0, 0),
				arrowHeadLength: $arrowHeadLength ?? 1,
				arrowHeadRadius: $arrowHeadRadius ?? 0.5,
				segments: $segments ?? 4
			)])
		);
		return $id;
	}

	/**
	 * Removes a specific Arrow shape by its ID.
	 *
	 * @param World|Player $viewer The world or player from which to remove the Arrow shape.
	 * @param int $id The ID of the Arrow shape to remove.
	 * @throws \LogicException if DrawerAPI is not registered.
	 */
	public static function removeById(World|Player $viewer, int $id): void {
		if(!DrawerAPI::isRegistered()) {
			throw new \LogicException("Cannot call Arrow::removeById before DrawerAPI is registered");
		}
		DrawerAPI::despawnPacketByID($viewer, $id);
		DrawerAPI::removeId(ShapeType::ARROW, $id);
	}
}