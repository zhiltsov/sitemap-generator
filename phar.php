<?php

$phar = new Phar('sitemap-generator.phar');
$phar->addFile('index.php');
$phar->addFile('autoload.php');
$phar->buildFromDirectory('lib/SiteMap');
$phar->compressFiles(PHAR::GZ);
