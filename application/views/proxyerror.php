	<div class="dialog_window">
		<div id="dialog_header">Внимание! Ошибка в настройках</div>
		<div id="dialog_buttons">
			<span class="dialog_other_buttons"></span>
			<a href="#" class="toolbtn dialog_close">&nbsp; Закрыть &nbsp;</a>
		</div>
		<div id="dialog_content">
			У Вашего браузера обнаружены неверные настройки "прокси сервера".<br>
			Ваш адрес определён как адрес прокси-сервера:<br>
			<ul class="proxyRemoteAddr">
				<li>REMOTE_ADDR = <?=$ipAddress?><li>
			</ul>
			Пожалуйста обратитесь к специалисту, или, если знаете как, сами добавьте<br> 
			в список исключений для прокси сервера адрес "*.arhcity.ru".<br>
			Убедитесь, что в списке исключений также присутствуют адреса:<br>
			<ul class="proxyExceptions">
				<li>*.arhcity.ru;</li>
				<li>arhcity.ru;</li>
				<li>192.168.*;</li>
			</ul>
			Данная ошибка может помешать Вам работать с системой,<br>
			и некоторые её возможности будут Вам недоступны.
		</div>
	</div>
