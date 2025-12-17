<?php
declare(strict_types=1);
namespace ninjaknights\drawerAPI;

enum ShapeType: string {
	case ARROW = "arrow";
	case BOX = "box";
	case CIRCLE = "circle";
	case LINE = "line";
	case SPHERE = "sphere";
	case TEXT = "text";
}