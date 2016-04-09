<?php

class Weather {
	/** @var modX $modx */
	public $modx;
	/** @var array $config */
	public $config = array();


	/**
	 * @param modX $modx
	 * @param array $config
	 */
	function __construct(modX &$modx, array $config = array()) {
		$this->modx = $modx;
		$this->config = array_merge(
			array(
				'city' => 27612,
				'limit' => 0,
				'attempts' => 10,
				'mode' => 'basic',
				'url' => 'http://export.yandex.ru/weather-ng/forecasts/',
				'tplDetailed' => 'tpl.Weather.detailed',
				'tplDetailedPart' => 'tpl.Weather.detailed_part',
				'tplShort' => 'tpl.Weather.short',
				'tplBasic' => 'tpl.Weather.basic',

				'cacheTime' => 600,
				'imgDir' => MODX_ASSETS_PATH . 'components/weather/img/',
				'imgUrl' => MODX_ASSETS_URL . 'components/weather/img/',
				'registerCss' => '[[+assetsUrl]]components/weather/css/weather.css',
			),
			$config
		);
	}


	/**
	 *
	 */
	public function run() {
		if (!empty($this->config['registerCss'])) {
			$file = str_replace('[[+assetsUrl]]', MODX_ASSETS_URL, $this->config['registerCss']);
			$this->modx->regClientCSS(str_replace('//', '/', $file));
		}

		if (!$data = $this->getDataCache()) {
			return '';
		}

		$output = '';
		switch ($this->config['mode']) {
			case 'detailed':
				foreach ($data['day'] as $idx => $day) {
					if ($this->config['limit'] && $idx >= $this->config['limit']) {
						break;
					}
					$output .= $this->getDay($day);
				}
				break;
			case 'short':
				foreach ($data['day'] as $idx => $day) {
					if ($this->config['limit'] && $idx >= $this->config['limit']) {
						break;
					}
					$output .= $this->getDayShort($day);
				}
				break;
			default:
				$output = $this->getBasic($data);
		}

		return $output;
	}


	/**
	 * @param array $array
	 *
	 * @return bool|string
	 */
	public function getDay(array $array = array())                               // выдает массив
	{
		$parts = '';
		$sunrise = $array['sunrise'];                                               // вывод восхода
		$sunset = $array['sunset'];                                                 // вывод заката
		$day_week = $this::getDayRus(date('w', strtotime($array['@attributes']['date']))); // вывод дня недели
		$day_number = date('j', strtotime($array['@attributes']['date']));          // вывод числа
		$month = $this::getMonthRus(date('n', strtotime($array['@attributes']['date'])));  // вывод месяца


		// последовательный перебор массива с данными по части дня
		foreach ($array['day_part'] as $idx => $part) {
			// выкидываем из результатов массива середину дня и ночи
			if ($idx > 3) {
				break;
			}
			// вывод части дня в температуре
			if ($idx == 0) {
				$part['part'] = 'Утром';
			}
			if ($idx == 1) {
				$part['part'] = 'Днем';
			}
			if ($idx == 2) {
				$part['part'] = 'Вечером';
			}
			if ($idx == 3) {
				$part['part'] = 'Ночью';
			}

			// вывод поворота стрелочки направления ветра
			$part['wind_icon'] = $this->wind_arr($part['wind_direction']);
			$part['wind'] = $this->getImage('wind_icon');
			$part['wind_direction'] = $this::wind_dir($part['wind_direction']);

			// вывод изменения цвета фона температуры в зависимости от значений оной
			$temp_today = isset($part['temperature_from'])
				? (($part['temperature_from'] + $part['temperature_to']) / 2)
				: $part['temperature'];
			$part['css_class'] = $this::getTempClass($temp_today);

			// вывод картинки сильного ветра в зависимости от скорости ветра
			if ($part['wind_speed'] > 7) {
				$part['wind_strength'] = $this->getImage('wind_strength');
			}
			else {
				$part['wind_strength'] = '';
			}

			// вывод знака "+" перед температурой
			foreach (array('temperature_from', 'temperature_to', 'temperature') as $v) {
				if (isset($part[$v])) {
					$part[$v] = $this::plus($part[$v]);
				}
				else {
					$part[$v] = '';
				}
			}
			// получение картинок
			$part['image'] = $this->getImage($part['image-v3']);
			// постановка условия: какой шаблон выводить в зависимости от температуры
			$parts .= $this->getChunk('tplDetailedPart', $part);
		}
		// разные значения внутри массива
		$placeholders = array(
			'parts' => $parts,
			'date' => $array['@attributes']['date'],
			'sunrise' => $sunrise,
			'sunset' => $sunset,
			'day_week' => $day_week,
			'day_number' => $day_number,
			'month' => $month,
		);

		// вывод выходных дней в году другим стилем
		$placeholders['css_class'] = $this::isHoliday($array['@attributes']['date'])
			? 'holiday'
			: 'workday';

		return $this->getChunk('tplDetailed', $placeholders);
	}


	/**
	 * @param array $array
	 *
	 * @return bool|string
	 */
	public function getDayShort(array $array = array()) {
		$day_week = $this::getDayRus(date('w', strtotime($array['@attributes']['date'])));  // вывод дня недели
		$day_number = date('j', strtotime($array['@attributes']['date']));           // вывод числа
		$month = $this::getMonthRus(date('n', strtotime($array['@attributes']['date'])));   // вывод месяца

		$placeholders = array(
			'date' => $array['@attributes']['date'],
			'day_week' => $day_week,
			'day_number' => $day_number,
			'month' => $month,
		);
		// День
		$day = $array['day_part'][1];
		$placeholders['image'] = $this->getImage($day['image-v3']);
		$placeholders['weather_type'] = $day['weather_type'];
		$temperature_day = isset($day['temperature_to'])
			? $day['temperature_to']
			: $day['temperature'];
		$placeholders['css_class_day'] = $this::getTempClass($temperature_day);
		$placeholders['temperature_day'] = $this::plus($temperature_day);
		// Ночь
		$night = $array['day_part'][3];
		$temperature_night = isset($night['temperature_from'])
			? $night['temperature_from']
			: $night['temperature'];
		$placeholders['css_class_night'] = $this::getTempClass($temperature_night);
		$placeholders['temperature_night'] = $this::plus($temperature_night);

		// вывод выходных дней в году другим стилем
		$placeholders['css_class_date'] = $this::isHoliday($array['@attributes']['date'])
			? 'holiday'
			: 'workday';

		return $this->getChunk('tplShort', $placeholders);
	}


	/**
	 * @param $array
	 *
	 * @return bool|string
	 */
	public function getBasic($array) {
		$placeholders = array(                                           // прописываем плейсхолдеры из шаблона tpl outer
			'id' => $array['@attributes']['id'],
			'city' => $array['@attributes']['city'],                    // город
			//'date' => date('H:i:s', strtotime($array['fact']['uptime'])), // настоящее время

			'temp' => $this::plus($array['fact']['temperature']),              // температура со знаком "+" настоящего времени
			'hour1' => $this::get_sting_from_hour(date('G', strtotime($array['fact']['uptime']))),                  // вывод времени суток №1
			'hour2' => $this::get_sting_from_hour1(date('G', strtotime($array['fact']['uptime']))),                 // вывод времени суток №2
			'temp1' => $this::temp1($array),                                   // вывод температуры для времени суток №1
			'temp2' => $this::temp2($array),                                   // вывод температуры для времени суток №2
			'weather_type' => $array['fact']['weather_type'],           // вывод типа погоды настоящего времени

			'sunrise' => $array['day'][0]['sunrise'],                   // вывод времени восхода текущего дня
			'sunset' => $array['day'][0]['sunset'],                     // вывод времени заката текущего дня
			'wind_speed' => $array['fact']['wind_speed'],               // вывод скорости ветра настоящего времени
			'wind_dir' => $this::wind_dir($array['fact']['wind_direction']),   // вывод направления ветра на русском настоящего времени
			'wind_icon' => $this::wind_arr($array['fact']['wind_direction']),
			'humidity' => $array['fact']['humidity'],                   // вывод влажности настоящего времени
			'pressure' => $array['fact']['pressure'],                   // вывод давления настоящего времени
			'temp_y' => $this::plus($array['yesterday']['temperature']),       // вывод температуры вчерашнего дня
			'image_1' => $this->getImage($array['fact']['image-v3']),                   // вывод картинки погоды настоящего времени
			'image_2' => $this->getImage($this::get_image_from_hour($array)),                   // вывод картинки погоды для времени суток №1
			'image_3' => $this->getImage($this::get_image_from_hour1($array)),                  // вывод картинки погоды для времени суток №2
			'observation_time' => date('H:i', strtotime($array['fact']['observation_time'])),  // вывод времени обновления данных
			'water_temperature' => $this::tempWater($array),
			'water' => $this::tempWaterClass($array),

			'css_class_1' => $this::getTempClass($array['fact']['temperature']),
			'css_class_2' => $this::getTempClass($this::temp1($array)),
			'css_class_3' => $this::getTempClass($this::temp2($array)),
		);

		return $this->getChunk('tplBasic', $placeholders);
	}


	/**
	 * @param string $name Имя чанка
	 * @param array $properties
	 *
	 * @return bool|string
	 */
	public function getChunk($name, array $properties = array()) {

		$properties['img_url'] = $this->config['imgUrl'];
		$chunk = $this->config[$name];

		if (class_exists('pdoTools') && $pdo = $this->modx->getService('pdoTools')) {
			return $pdo->getChunk($chunk, $properties);
		}
		else {
			return $this->modx->getChunk($chunk, $properties);
		}
	}


	/**
	 * @return array|bool
	 */
	public function getDataCache() {
		/** @var xPDOCacheManager $cacheManager */
		$cacheManager = $this->modx->getCacheManager();
		$cache_key = 'weather/' . $this->config['city'];

		$json = $cacheManager->get($cache_key);
		if (empty($json)) {
			$url = $this->config['url'] . $this->config['city'] . '.xml';
			$attempts = (int)$this->config['attempts']
				? (int)$this->config['attempts']
				: 10;
			for ($i = 1; $i <= $attempts; $i++) {
				if ($data = $this->download($url)) {
					break;
				}
			}
			if (!empty($data)) {
				$xml = simplexml_load_string($data);
				$json = json_encode($xml);
				$cacheManager->set($cache_key, $json, $this->config['cacheTime']);
			}
			else {
				$this->modx->log(modX::LOG_LEVEL_ERROR,
					"[Weather] Could not download data from: {$url} after {$attempts} attempts"
				);
			}
		}

		return json_decode($json, true);
	}


	/**
	 * @param $temp
	 *
	 * @return string
	 */
	public static function plus($temp) {
		if ($temp > 0) {
			$temp = '+' . $temp;
		}

		return $temp;
	}


	/**
	 * функция вывода на русском названия времени суток №1
	 *
	 * @param $date
	 *
	 * @return string
	 */
	public static function get_sting_from_hour($date) {
		if ($date <= 12 and $date > 6) {
			$dir = 'Днем';
		}
		elseif ($date <= 18 and $date > 12) {
			$dir = "Вечером";
		}
		elseif ($date <= 24 and $date > 18) {
			$dir = "Ночью";
		}
		else {
			$dir = "Утром";
		}
		return $dir;
	}


	/**
	 * функция вывода на русском названия времени суток №2
	 *
	 * @param $date
	 *
	 * @return string
	 */
	public static function get_sting_from_hour1($date) {
		if ($date <= 12 and $date > 6) {
			$dir = 'Вечером';
		}
		elseif ($date <= 18 and $date > 12) {
			$dir = "Ночью";
		}
		elseif ($date <= 24 and $date > 18) {
			$dir = "Утром";
		}
		else {
			$dir = "Днем";
		}
		return $dir;
	}


	/**
	 * функция вывода температуры в зависимости от времени суток №1
	 *
	 * @param $array
	 *
	 * @return float|string
	 */
	protected static function temp1($array) {
		$date = date('G', strtotime($array['fact']['uptime']));

		if ($date <= 12 and $date > 6) {
			$temp = isset($array['day'][0]['day_part'][1]['temperature_from'])
				? ceil(($array['day'][0]['day_part'][1]['temperature_from'] + $array['day'][0]['day_part'][1]['temperature_to']) / 2)
				: $array['day'][0]['day_part'][1]['temperature'];
			if ($temp > 0) {
				$temp = '+' . $temp;
			}
		}
		elseif ($date <= 18 and $date > 12) {
			$temp = isset($array['day'][0]['day_part'][2]['temperature_from'])
				? ceil(($array['day'][0]['day_part'][2]['temperature_from'] + $array['day'][0]['day_part'][2]['temperature_to']) / 2)
				: $array['day'][0]['day_part'][2]['temperature'];
			if ($temp > 0) {
				$temp = '+' . $temp;
			}
		}
		elseif ($date <= 24 and $date > 18) {
			$temp = isset($array['day'][0]['day_part'][3]['temperature_from'])
				? ceil(($array['day'][0]['day_part'][3]['temperature_from'] + $array['day'][0]['day_part'][3]['temperature_to']) / 2)
				: $array['day'][0]['day_part'][3]['temperature'];
			if ($temp > 0) {
				$temp = '+' . $temp;
			}
		}
		else {
			$temp = isset($array['day'][0]['day_part'][0]['temperature_from'])
				? ceil(($array['day'][0]['day_part'][0]['temperature_from'] + $array['day'][0]['day_part'][0]['temperature_to']) / 2)
				: $array['day'][0]['day_part'][0]['temperature'];
			if ($temp > 0) {
				$temp = '+' . $temp;
			}
		}

		return $temp;
	}


	/**
	 * функция вывода температуры в зависимости от времени суток №2
	 *
	 * @param $array
	 *
	 * @return float|string
	 */
	protected static function temp2($array) {
		$date = date('G', strtotime($array['fact']['uptime']));
		if ($date <= 12 and $date > 6) {
			$temp = isset($array['day'][0]['day_part'][2]['temperature_from'])
				? ceil(($array['day'][0]['day_part'][2]['temperature_from'] + $array['day'][0]['day_part'][2]['temperature_to']) / 2)
				: $array['day'][0]['day_part'][2]['temperature'];
			if ($temp > 0) {
				$temp = '+' . $temp;
			}
		}
		elseif ($date <= 18 and $date > 12) {
			$temp = isset($array['day'][0]['day_part'][3]['temperature_from'])
				? ceil(($array['day'][0]['day_part'][3]['temperature_from'] + $array['day'][0]['day_part'][3]['temperature_to']) / 2)
				: $array['day'][0]['day_part'][3]['temperature'];
			if ($temp > 0) {
				$temp = '+' . $temp;
			}
		}
		elseif ($date <= 24 and $date > 18) {
			$temp = isset($array['day'][1]['day_part'][0]['temperature_from'])
				? ceil(($array['day'][1]['day_part'][0]['temperature_from'] + $array['day'][1]['day_part'][0]['temperature_to']) / 2)
				: $array['day'][1]['day_part'][0]['temperature'];
			if ($temp > 0) {
				$temp = '+' . $temp;
			}
		}
		else {
			$temp = isset($array['day'][0]['day_part'][1]['temperature_from'])
				? ceil(($array['day'][0]['day_part'][1]['temperature_from'] + $array['day'][0]['day_part'][1]['temperature_to']) / 2)
				: $array['day'][0]['day_part'][1]['temperature'];
			if ($temp > 0) {
				$temp = '+' . $temp;
			}
		}

		return $temp;
	}


	/**
	 * функция вывода направления ветра на русском
	 *
	 * @param $wind
	 *
	 * @return string
	 */
	protected static function wind_dir($wind) {
		switch ($wind) {
			case 'n':
				$dir = 'с';
				break;
			case 'w':
				$dir = 'з';
				break;
			case 'e':
				$dir = 'в';
				break;
			case 's':
				$dir = 'ю';
				break;
			case 'ne':
				$dir = 'св';
				break;
			case 'se':
				$dir = 'юв';
				break;
			case 'nw':
				$dir = 'сз';
				break;
			case 'sw':
				$dir = 'юз';
				break;
			case 'calm':
				$dir = 'Штиль';
				break;
			default:
				$dir = 'непонятное';
		}

		return $dir;
	}


	/**
	 * Вывод поворота стрелочки направления ветра
	 *
	 * @param $wind
	 *
	 * @return string
	 */
	protected static function wind_arr($wind) {
		switch ($wind) {
			case 'n':
				$arr = 'wind_180';
				break;
			case 'w':
				$arr = 'wind_90';
				break;
			case 'e':
				$arr = 'wind_270';
				break;
			case 's':
				$arr = 'wind_0';
				break;
			case 'ne':
				$arr = 'wind_225';
				break;
			case 'se':
				$arr = 'wind_315';
				break;
			case 'nw':
				$arr = 'wind_135';
				break;
			case 'sw':
				$arr = 'wind_45';
				break;
			default:
				$arr = 'wind_calm';
		}

		return $arr;
	}


	/**
	 * функция вывода картинки погоды для времени суток №1
	 *
	 * @param $array
	 *
	 * @return mixed
	 */
	protected static function get_image_from_hour($array) {
		$date = date('G', strtotime($array['fact']['uptime']));
		if ($date <= 12 and $date > 6) {
			$dir = $array['day'][0]['day_part'][1]['image-v3'];
		}
		elseif ($date <= 18 and $date > 12) {
			$dir = $array['day'][0]['day_part'][2]['image-v3'];
		}
		elseif ($date <= 24 and $date > 18) {
			$dir = $array['day'][0]['day_part'][3]['image-v3'];
		}
		else {
			$dir = $array['day'][0]['day_part'][0]['image-v3'];
		}

		return $dir;
	}


	/**
	 * функция вывода картинки погоды для времени суток №2
	 *
	 * @param $array
	 *
	 * @return mixed
	 */
	protected static function get_image_from_hour1($array) {
		$date = date('G', strtotime($array['fact']['uptime']));
		if ($date <= 12 and $date > 6) {
			$dir = $array['day'][0]['day_part'][2]['image-v3'];
		}
		elseif ($date <= 18 and $date > 12) {
			$dir = $array['day'][0]['day_part'][3]['image-v3'];
		}
		elseif ($date <= 24 and $date > 18) {
			$dir = $array['day'][1]['day_part'][0]['image-v3'];
		}
		else {
			$dir = $array['day'][0]['day_part'][1]['image-v3'];
		}

		return $dir;
	}


	/**
	 * Функция вывода названия дня недели на русском
	 *
	 * @param bool $num_day
	 *
	 * @return mixed
	 */
	protected static function getDayRus($num_day = false) {
		if ($num_day >= 7) {
			$num_day = date('w');
		}
		$days = array(
			'вс', 'пн',
			'вт', 'ср',
			'чт', 'пт', 'сб'
		);
		$name_day = $days[$num_day];

		return $name_day;
	}


	/**
	 * Функция вывода названия месяца на русском
	 *
	 * @param bool $num_month
	 *
	 * @return mixed
	 */
	protected static function getMonthRus($num_month = false) {
		if (!$num_month) {
			$num_month = date('n');
		}
		$monthes = array(
			1 => 'января', 2 => 'февраля', 3 => 'марта',
			4 => 'апреля', 5 => 'мая', 6 => 'июня',
			7 => 'июля', 8 => 'августа', 9 => 'сентября',
			10 => 'октября', 11 => 'ноября',
			12 => 'декабря'
		);
		$name_month = $monthes[$num_month];

		return $name_month;
	}


	/**
	 * @param $image
	 * @param string $ext
	 *
	 * @return bool|string
	 */
	public function getImage($image, $ext = 'svg') {
		$image .= '.' . $ext;
		$cache = $this->config['imgDir'] . $image;

		if (!file_exists($cache)) {
			if ($tmp = $this->download("https://yastatic.net/weather/i/icons/svg/{$image}")) {
				file_put_contents($cache, $tmp);
			}
			else {
				return false;
			}
		}

		return $this->config['imgUrl'] . $image;
	}


	/**
	 * @param $temp
	 *
	 * @return string
	 */
	public static function getTempClass($temp) {
		if ($temp > 60) {
			$class = 'temphot';
		}
		elseif ($temp < -60) {
			$class = 'tempcold';
		}
		else {
			$class = 'temp' . (int)$temp;
		}

		return $class;
	}


	/**
	 * @param $array
	 *
	 * @return bool|string
	 */
	protected function tempWater($array) {

		$tempWater = isset($array['fact']['water_temperature']);

		if ($tempWater) {
			$Water = 'Температура воды: +' . $array['fact']['water_temperature'] . '°С';
		}
		else $Water = False;
		return $Water;
	}


	/**
	 * @param $array
	 *
	 * @return string
	 */
	protected function tempWaterClass($array) {
		$tempWater = isset($array['fact']['water_temperature']);

		if ($tempWater) {
			$Water = 'water';
		}
		else $Water = 'water_no';
		return $Water;
	}


	/**
	 * @param $date
	 *
	 * @return bool
	 */
	protected function isHoliday($date) {
		if (!is_numeric($date)) {
			$date = strtotime($date);
		}

		$dow = date('w', $date);
		if ($dow == 0 || $dow == 6) {
			return true;
		}

		$month = date('m', $date);
		$day = date('d', $date);
		if ($month == 1 && $day >= 1 && $day <= 10) {
			return true;
		}

		$holidays = array(
			'23.02', '08.03', '01.05', '02.05', '03.05', '04.05',
			'09.05', '10.05', '11.05', '12.06', '04.11'
		);

		return in_array($day . '.' . $month, $holidays);
	}


	/**
	 * @param $src
	 * @param int $timeout
	 *
	 * @return bool|mixed|string
	 */
	protected function download($src, $timeout = 3) {
		if (function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $src);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
			$safeMode = @ini_get('safe_mode');
			$openBasedir = @ini_get('open_basedir');
			if (empty($safeMode) && empty($openBasedir)) {
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			}
			$file = curl_exec($ch);
			$info = curl_getinfo($ch);
			if ($info['http_code'] != 200) {
				//$this->modx->log(modX::LOG_LEVEL_ERROR, '[Weather] Could not download data from: ' . $src);
				$file = '';
			}
			curl_close($ch);
		}
		else {
			$file = @file_get_contents($src);
		}

		return $file;
	}

}