<?php

$properties = array();

$tmp = array(
	'city' => array(
		'type' => 'numberfield',
		'value' => 27612,
		'desc' => 'Id города для вывода. Список городов можно <a href="https://pogoda.yandex.ru/static/cities.xml" target="_blank">посмотреть на Яндекс</a>.'
	),
	'limit' => array(
		'type' => 'numberfield',
		'value' => 0,
		'desc' => 'Ограничение на количество дней для вывода в режимах short и detailed.'
	),
	'attempts' => array(
		'type' => 'numberfield',
		'value' => 10,
		'desc' => 'Количество попыток загрузить данные с удалённого сервиса'
	),
	'mode' => array(
		'type' => 'list',
		'value' => 'basic',
		'options' => array(
			array('text' => 'Базовый','value' => 'basic'),
			array('text' => 'Подробный','value' => 'detailed'),
			array('text' => 'Краткий','value' => 'short'),
		),
		'desc' => 'Режим работы сниппета',
	),

	'tplDetailed' => array(
		'type' => 'textfield',
		'value' => 'tpl.Weather.detailed',
		'desc' => 'Шаблон одного дня для вывода погоды в подробном режиме',
	),
	'tplDetailedPart' => array(
		'type' => 'textfield',
		'value' => 'tpl.Weather.detailed_part',
		'desc' => 'Шаблон одной части дня для вывода погоды в подробном режиме',
	),
	'tplShort' => array(
		'type' => 'textfield',
		'value' => 'tpl.Weather.short',
		'desc' => 'Шаблон одного дня для вывода погоды в сокращённом режиме',
	),
	'tplBasic' => array(
		'type' => 'textfield',
		'value' => 'tpl.Weather.basic',
		'desc' => 'Шаблон вывода текущей погоды в городе',
	),

	'cacheTime' => array(
		'type' => 'numberfield',
		'value' => 600,
		'desc' => 'Время кэширования данных одного города, в секундах',
	),
	'registerCss' => array(
		'type' => 'textfield',
		'value' => '[[+assetsUrl]]components/weather/css/weather.css',
		'desc' => 'Регистрация css файла с оформлением на сайте',
	),


);

foreach ($tmp as $k => $v) {
	$properties[] = array_merge(
		array(
			'name' => $k,
			//'desc' => PKG_NAME_LOWER . '_prop_' . $k,
			//'lexicon' => PKG_NAME_LOWER . ':properties',
		), $v
	);
}

return $properties;