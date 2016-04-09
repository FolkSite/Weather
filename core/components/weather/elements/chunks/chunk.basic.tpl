<div class="weather">
	<h3>Погода в городе [[+city]]</h3>

	<table cellspacing="0" class="table_basic">
		<tr>
			<td width="20"></td>
			<td></td>
			<td></td>
			<td>
				<div align="center"><small>[[+hour1]]</small></div>
			</td>
			<td>
				<div align="center"><small>[[+hour2]]</small></div>
			<td></td>
		</tr>
		<tr>
			<td class="[[+css_class_1]]"></td>
			<td class="[[+css_class_1]]"></td>
			<td class="[[+css_class_1]]"></td>
			<td class="col2 [[+css_class_2]]"></td>
			<td class="col3 [[+css_class_3]]"></td>
			<td> Восход: [[+sunrise]] Закат: [[+sunset]]</td>
		</tr>
		<tr>
			<td class="[[+css_class_1]]"></td>
			<td align="center" class="[[+css_class_1]]" rowspan="2">
				<img src="[[+image_1]]" width="50" height="50">
			</td>
			<td align="center" width="150" class="[[+css_class_1]]" rowspan="2">[[+weather_type]]</td>
			<td align="center" width="75" class="col2 [[+css_class_2]]" valign="top">
				<img src="[[+image_2]]" width="30" height="30">
			</td>
			<td align="center" width="75" class="col3 [[+css_class_3]]" valign="top">
				<img src="[[+image_3]]" width="30" height="30">
			</td>
			<td>Ветер: [[+wind_speed]] м/с. [[+wind_dir]]
				<img class="[[+wind_icon]]" src="[[+img_url]]wind_icon.svg" width="11" height="11">
			</td>
		</tr>
		<tr>
			<td class="[[+css_class_1]]"></td>
			<td class="col2 [[+css_class_2]]"></td>
			<td class="col3 [[+css_class_3]]"></td>
			<td>Влажность: [[+humidity]]%</td>
		</tr>

		<tr>
			<td class="[[+css_class_1]]"></td>
			<td class="[[+css_class_1]]" colspan="2">
				<div align="left"><font size="+2"><b>[[+temp]]°С</b></font></div>
			</td>
			<td class="col2 [[+css_class_2]]">
				<div align="center"><b>[[+temp1]]°С</b></div>
			</td>
			<td class="col3 [[+css_class_3]]">
				<div align="center"><b>[[+temp2]]°С</b></div>
			</td>
			<td>Давление: [[+pressure]] мм.рт.ст.</td>
		</tr>
		<tr>
			<td class="[[+css_class_1]]"></td>
			<td class="[[+css_class_1]]"></td>
			<td class="[[+css_class_1]]"></td>
			<td class="col2 [[+css_class_2]]"></td>
			<td class="col3 [[+css_class_3]]"></td>
			<td>
				<small>Данные на [[+observation_time]]</small>
			</td>
		</tr>
	</table>
	<p>Вчера в это время: [[+temp_y]]°С</p>
	<p>[[+water_temperature]] <img class="[[+water]]" src="[[+img_url]]water.svg" width="15" height="15"></p>
</div>