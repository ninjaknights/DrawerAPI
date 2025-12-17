<?php
declare(strict_types=1);
namespace ninjaknights\drawerAPI;

use pocketmine\color\Color;
use ninjaknights\drawerAPI\DrawerAPI;

enum ShapeColor: string {
	case WHITE = 'white';
	case ORANGE = 'orange';
	case MAGENTA = 'magenta';
	case LIGHT_BLUE = 'light_blue';
	case YELLOW = 'yellow';
	case LIME = 'lime';
	case PINK = 'pink';
	case GRAY = 'gray';
	case LIGHT_GRAY = 'light_gray';
	case CYAN = 'cyan';
	case PURPLE = 'purple';
	case BLUE = 'blue';
	case BROWN = 'brown';
	case GREEN = 'green';
	case RED = 'red';
	case BLACK = 'black';

	/**
	 * @return Color 
	 * @throws \LogicException if DrawerAPI is not registered.
	 */
	public function toColor(): Color {
		if(!DrawerAPI::isRegistered()) {
			throw new \LogicException("Cannot call ShapeColor::toColor before DrawerAPI is registered");
		}
		return match($this) {
			self::WHITE => new Color(0xf0, 0xf0, 0xf0),
			self::ORANGE => new Color(0xf9, 0x80, 0x1d),
			self::MAGENTA => new Color(0xc7, 0x4e, 0xbd),
			self::LIGHT_BLUE => new Color(0x3a, 0xb3, 0xda),
			self::YELLOW => new Color(0xfe, 0xd8, 0x3d),
			self::LIME => new Color(0x80, 0xc7, 0x1f),
			self::PINK => new Color(0xf3, 0x8b, 0xaa),
			self::GRAY => new Color(0x47, 0x4f, 0x52),
			self::LIGHT_GRAY => new Color(0x9d, 0x9d, 0x97),
			self::CYAN => new Color(0x16, 0x9c, 0x9c),
			self::PURPLE => new Color(0x89, 0x32, 0xb8),
			self::BLUE => new Color(0x3c, 0x44, 0xaa),
			self::BROWN => new Color(0x83, 0x54, 0x32),
			self::GREEN => new Color(0x5e, 0x7c, 0x16),
			self::RED => new Color(0xb0, 0x2e, 0x26),
			self::BLACK => new Color(0x1d, 0x1d, 0x21),
		};
	}

	/**  
	 * @return ShapeColor|null
	 * @throws \LogicException if DrawerAPI is not registered.
	*/
	public static function fromString(string $input): ShapeColor|null {
		if(!DrawerAPI::isRegistered()) {
			throw new \LogicException("Cannot call ShapeColor::fromString before DrawerAPI is registered");
		}
		$input = strtolower(trim($input));
		foreach(self::cases() as $case){
			if($case->value === $input){
				return $case;
			}
		}
		return null;
	}
}