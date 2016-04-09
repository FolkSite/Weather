<tr>
	<td width="30">
	</td>
	<td class="[[+css_class]]" height="63" width="163" align="center">
		<font size="-1">[[+part]]</font><br>
		<font size="+1"><b>
			[[+temperature_from:isnot=``:then=`
				[[+temperature_from]]...[[+temperature_to]]
			`:else=`
				[[+temperature]]
			`]]
		</b></font>
	</td>
	<td class="[[+css_class]]" width="82" align="center">
		<img src="[[+image]]" width="30" height="30">
	</td>
	<td class="[[+css_class]]" width="187" align="center">
		[[+weather_type]]
	</td>
	<td class="[[+css_class]]" width="121" align="center">
		[[+pressure]]
	</td>
	<td class="[[+css_class]]" width="97" align="center">
		[[+humidity]] %
	</td>
	<td class="[[+css_class]]" width="97" align="center">
		[[+wind_speed]] м/с, <i><img class="[[+wind_icon]]" src="[[+wind]]" width="11" height="11"></i>
		[[+wind_direction]]
	</td>
	<td class="[[+css_class]]" width="30" height="30" align="center">
		[[+wind_strength:isnot=``:then=`
		<img src="[[+wind_strength]]" title="Сильный ветер">
		`]]
	</td>
</tr>