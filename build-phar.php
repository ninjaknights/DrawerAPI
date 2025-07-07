<?php

function getVersion(): string {
	$composer = json_decode(file_get_contents(__DIR__ . '/composer.json'), true);
	return $composer['version'] ?? 'dev';
}

function getName(): string {
	$composer = json_decode(file_get_contents(__DIR__ . '/composer.json'), true);
	return isset($composer['name']) ? basename($composer['name']) : 'DrawerAPI';
}

$version = getVersion();
$name = getName();
$pharFile = "build/{$name}-{$version}.phar";
if (!is_dir("build")) {
	mkdir("build");
}
if (file_exists($pharFile)) {
	unlink($pharFile);
}
echo "Building Phar: $pharFile\n";
$phar = new Phar($pharFile);
$phar->startBuffering();
$phar->setStub('<?php Phar::mapPhar(); require "phar://'. $name . '-' . $version . '.phar/src/NinjaKnights/DrawerAPI.php"; __HALT_COMPILER();');
$phar->addFile('composer.json');
$phar->addFile('virion.yml');
$phar->addFile('LICENSE');
$phar->addFile('README.md');
$phar->buildFromDirectory(__DIR__ . '/src', '/\\.php$/');
$phar->stopBuffering();
echo "Done! Built the phar: $pharFile\n";
