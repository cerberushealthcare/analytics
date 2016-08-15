<?php
$v = file_get_contents('version', FILE_USE_INCLUDE_PATH);
header('Content-Type: text/cache-manifest');
echo <<<END
CACHE MANIFEST
# Version $v

CACHE:
# stylesheets
css/jquery.mobile-1.3.2.min.css
css/my-jquery-overrides.css
css/my.css
# javascript
js/jquery-1.10.2.min.js
js/jquery.mobile-1.3.2.min.js
js/lcd.js
js/my-plugins.js
js/data/Flights.js
js/ui/Pages.js
# images
img/skywatch.png
img/skywatch-splash.png
css/images/ajax-loader.gif
css/images/ios7-back.png
css/images/ios7-plus.png
css/images/ios7-edit.png
css/images/ios7-fwd.png
css/images/ios7-unlock-32.png
css/images/ios7-lock-32.png
css/images/ios7-config-32.png

NETWORK:
*
END;
