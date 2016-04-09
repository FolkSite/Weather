<?php
/** @var array $scriptProperties */
if (!class_exists('Weather')) {
	require MODX_CORE_PATH . 'components/weather/model/weather.class.php';
}

/** @var Weather $Weather */
$Weather = new Weather($modx, $scriptProperties);

return $Weather->run();