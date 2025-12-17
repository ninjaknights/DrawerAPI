<?php
declare(strict_types=1);
namespace ninjaknights\drawerAPI\shapes;

use ninjaknights\drawerAPI\DrawerAPI;
use ninjaknights\drawerAPI\ShapeColor;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\protocol\ClientboundDebugRendererPacket;
use pocketmine\network\mcpe\protocol\types\DebugMarkerData;
use pocketmine\player\Player;
use pocketmine\world\World;

class Cube {

	/**
	 * Creates a Cube shape in the world or for a player.
	 * 
	 * @param World|Player $viewer The world or player that will see the Cube Shape.
	 * @param Vector3|null $position The position where the Cube will spawn at. Defaults to the viewer's position.
	 * @param string|null $text the text that is displayed above the debug.
	 * @param string|null $color The color of the Cube. Defaults to "white". (Accepts HexCode eg: `#f0f0f0`)
	 * @param int|null $lifeSpan Lifetime of the Cube **in seconds**. Defaults to `10`.
	 * 
	 * @throws \LogicException if DrawerAPI is not registered.
	*/
	public static function create(
		World|Player $viewer,
		Vector3|null $position = null,
		string $text = "",
		string|null $color = ShapeColor::WHITE,
		int $lifeSpan = 10
	): void {
		if(!DrawerAPI::isRegistered()) {
			throw new \LogicException("Cannot call Cube::create before calling register");
		}
		$pos = ($position === null) ? ($viewer instanceof Player ? $viewer->getPosition() : null) : $position;
		$marker = new DebugMarkerData(
			text: $text,
			position: $pos,
			color: DrawerAPI::getColor($color),
			durationMillis: $lifeSpan * 1000
		);
		$targets = $viewer instanceof Player ? [$viewer] : $viewer->getPlayers();
		NetworkBroadcastUtils::broadcastPackets($targets, [ClientboundDebugRendererPacket::addCube($marker)]);
	}

	/**
	 * Clears **all debug cubes** currently visible to the viewer(s).
	 *
	 * @param World|Player $viewer The world or player from which to remove the Cube shape.
	 * @throws \LogicException if DrawerAPI is not registered.
	 */
	public static function clear(World|Player $viewer): void {
		if(!DrawerAPI::isRegistered()) {
			throw new \LogicException("Cannot call Cube::removeById before DrawerAPI is registered");
		}
		$targets = $viewer instanceof Player ? [$viewer] : $viewer->getPlayers();
		NetworkBroadcastUtils::broadcastPackets($targets, [ClientboundDebugRendererPacket::clear()]);
	}
}