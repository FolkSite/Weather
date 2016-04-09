<?php

$chunks = array();

$tmp = array(
	'tpl.Weather.detailed' => array(
		'file' => 'detailed',
		'description' => 'шаблон обрамления дня в полном формате',
	),
	'tpl.Weather.detailed_part' => array(
		'file' => 'detailed_part',
		'description' => 'шаблон обрамления части дня в полном формате',
	),

	'tpl.Weather.short' => array(
		'file' => 'short',
		'description' => 'шаблон обрамления дня в сокращённом формате',
	),

	'tpl.Weather.basic' => array(
		'file' => 'basic',
		'description' => 'шаблон для вывода виджета погоды',
	),
);

// Save chunks for setup options
$BUILD_CHUNKS = array();

foreach ($tmp as $k => $v) {
	/* @avr modChunk $chunk */
	$chunk = $modx->newObject('modChunk');
	$chunk->fromArray(array(
		'id' => 0,
		'name' => $k,
		'description' => @$v['description'],
		'snippet' => file_get_contents($sources['source_core'] . '/elements/chunks/chunk.' . $v['file'] . '.tpl'),
		'static' => BUILD_CHUNK_STATIC,
		'source' => 1,
		'static_file' => 'core/components/' . PKG_NAME_LOWER . '/elements/chunks/chunk.' . $v['file'] . '.tpl',
	), '', true, true);

	$chunks[] = $chunk;

	$BUILD_CHUNKS[$k] = file_get_contents($sources['source_core'] . '/elements/chunks/chunk.' . $v['file'] . '.tpl');
}

unset($tmp);
return $chunks;