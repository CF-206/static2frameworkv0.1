<?php
// filepath: c:\wamp64\www\static2frameworkv0.1\app\systeme\Autoload.php

define('PATH_APP', dirname(__DIR__));
define('PATH_CLASS', PATH_APP . '/systeme/class');
define('PATH_ROOT_JSON', PATH_APP . '/root.json');

foreach (glob(PATH_CLASS . '/*.php') as $file) {
    require_once $file;
}