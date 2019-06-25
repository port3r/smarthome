<?php 
  define('ADMIN_LOGIN','port3r'); 
  define('ADMIN_PASSWORD','e7c0c70a1f14baf3c9f11e12bc81fa63');
  
  if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) 
      || ($_SERVER['PHP_AUTH_USER'] != ADMIN_LOGIN) 
      || (md5($_SERVER['PHP_AUTH_PW']) != ADMIN_PASSWORD)) { 
    header('HTTP/1.1 401 Unauthorized'); 
    header('WWW-Authenticate: Basic realm="Password For YUM4 SmartHome"'); 
    exit("Access Denied: Username and password required."); 
  } 
?>
<!DOCTYPE HTML>
<html>
<head>
	<meta charset='utf-8'>
	<meta http-equiv='X-UA-Compatible' content='IE=edge'>
	<meta name='viewport' content='width=device-width, initial-scale=1'>

	<!-- Favicon -->
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
	<link rel="apple-touch-icon" href="apple-touch-icon.png" />
	<link rel="apple-touch-icon" sizes="57x57" href="apple-touch-icon-57x57.png" />
	<link rel="apple-touch-icon" sizes="72x72" href="apple-touch-icon-72x72.png" />
	<link rel="apple-touch-icon" sizes="76x76" href="apple-touch-icon-76x76.png" />
	<link rel="apple-touch-icon" sizes="114x114" href="apple-touch-icon-114x114.png" />
	<link rel="apple-touch-icon" sizes="120x120" href="apple-touch-icon-120x120.png" />
	<link rel="apple-touch-icon" sizes="144x144" href="apple-touch-icon-144x144.png" />
	<link rel="apple-touch-icon" sizes="152x152" href="apple-touch-icon-152x152.png" />
	<link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon-180x180.png" />
	
	<link rel='stylesheet' href='assets/bootstrap.min.css'>
	<link rel='stylesheet' href='assets/font-awesome.min.css'>
	<link rel='stylesheet' href='assets/simple-switch.min.css'>

	<script src='assets/jquery.min.js'></script>
	<script src='assets/bootstrap.min.js'></script>
	<script src='assets/jquery.simpleswitch.min.js'></script>

	<script>
		jQuery(function($)
		{
			var months = ['Jan','Feb','Mar','Apr','Máj','Jún','Júl','Aug','Sep','Okt','Nov','Dec'];
			
			//-- INIT
			fancontrol();
			wunderground();
			gatecontrol();
			//rtc();

			//-- EVERY 1min.
			setInterval(function()
			{ 
			    fancontrol();
			    gatecontrol();
			}, 60000);
			
			//-- EVERY 5min.
			setInterval(function()
			{ 
			    wunderground();
			}, 300000);

			$('.ios').simpleSwitch();

			$(document).on('click','.ios,.aos', function()
			{
				var 
					$this = $(this),
					esp = $(this).data('esp'),
					cmd = $(this).data('cmd'),
				    	wait = $(this).data('wait'),
					url = 'esp/' + esp +'.php?redirect=' + cmd; 
				
				if (wait && wait > 0)
				{
					if (isDoubleClicked($(this), wait)) return;
				}
				
				$.ajax({method:'GET',url:url}).done(function(result) 
				{
					if (esp == 'fancontrol') fancontrol();
				});
			});
			
			$(document).on('click','.rm-active',function()
			{
				$(this).parent().removeClass('active');
			});			

			function fancontrol()
			{
				$.getScript("esp/fancontrol.php", function()
				{
					//-- SET SYSTEM TEMPERATURE
					//$('.fancontrol-systemp').html((Math.round(fancontroldata.celsius * 10) / 10) + ' &#8451;');
					if (fancontroldata.outsideTemperature != 'nan')
					{
						$('.fancontrol-airtemperature').html((Math.round(fancontroldata.outsideTemperature * 10) / 10) + '°');
						$('.fancontrol-airhumidity').html(Math.round(fancontroldata.outsideHumidity) + '%');
					}
					
					if (fancontroldata.maintenance == 'on')
					{
						$('.manualormaintenance').text('Údržba');	
					}
					else
					{
						$('.manualormaintenance').text('Manuálne spustenie');
					}
					
					//-- SET BATHROOM STATES
					var shower = (fancontroldata.brHumidity >= 70) ? ' &nbsp;<i class="fa fa-tint" aria-hidden="true"></i>' : '';
					$('.fancontrol-brtemperature').html((Math.round(fancontroldata.brTemperature * 10) / 10) + '°');
					$('.fancontrol-brhumidity').html(Math.round(fancontroldata.brHumidity) + '%' + shower);
					
					var brUpdateOn = new Date(fancontroldata.brUpdateOn * 1000);
					if (isDST(brUpdateOn))
					{
						brUpdateOn = new Date((fancontroldata.brUpdateOn - 3600) * 1000);
					}
					
					var brHours = ("0" + brUpdateOn.getUTCHours()).slice(-2);
					var brMinutes = ("0" + brUpdateOn.getUTCMinutes()).slice(-2);
					$('.fancontrol-brUpdateOn').html(brUpdateOn.getUTCDate() + ' ' + months[brUpdateOn.getUTCMonth()] + '. ' + brHours + ':' + brMinutes);
					
					if (fancontroldata.brControl == 'on') 
					{
						$('.fancontrol-brControl').prop('checked', true).parent().removeClass('unchecked').addClass('checked');
						$('.fancontrol-brControl-sm-status').text('');
						$('li.brControl').show();
					}
					else 
					{
						$('.fancontrol-brControl').prop('checked', false).parent().removeClass('checked').addClass('unchecked');
						$('.sm-status.fancontrol-brhumidity').text('');
						$('.sm-status.fancontrol-brtemperature').text('');
						$('.fancontrol-brControl-sm-status').text('VYP.');
						$('small.fancontrol-brUpdateOn').text('');
						$('li.brControl').hide();
					}
					
					//-- WEATHER
					$('.fancontrol-wutemperature').html((Math.round(fancontroldata.wuTemperature * 10) / 10) + '°');
					$('.fancontrol-wuhumidity').html(Math.round(fancontroldata.wuHumidity) + '%');
					
					var wuUpdateOn = new Date(fancontroldata.wuUpdateOn * 1000);
					if (isDST(wuUpdateOn))
					{
						wuUpdateOn = new Date((fancontroldata.wuUpdateOn - 3600) * 1000);
					}
					var wuHours = ("0" + wuUpdateOn.getUTCHours()).slice(-2);
					var wuMinutes = ("0" + wuUpdateOn.getUTCMinutes()).slice(-2);
					$('.fancontrol-wuUpdateOn').html(wuUpdateOn.getUTCDate() + ' ' + months[wuUpdateOn.getUTCMonth()] + '. ' + wuHours + ':' + wuMinutes);
						
					if (fancontroldata.wuControl == 'on') 
					{
						$('.fancontrol-wuControl').prop('checked', true).parent().removeClass('unchecked').addClass('checked');
						$('.fancontrol-wuControl-sm-status').text('');
						$('li.wuControl').show();
					}
					else 
					{
						$('.fancontrol-wuControl').prop('checked', false).parent().removeClass('checked').addClass('unchecked');
						//$('.sm-status.fancontrol-wuhumidity').text('');
						//$('.sm-status.fancontrol-wutemperature').text('');
						$('.fancontrol-wuControl-sm-status').text(' VYP.');
						//$('small.fancontrol-wuUpdateOn').text('');
						//$('li.wuControl').hide();
						$('li.wuControl').show();
					}					
					
					//-- SET FAN STATE
					if (fancontroldata.fanstate == 'on') $('.fancontrol-fanstate').addClass('fa-spin');
					else $('.fancontrol-fanstate').removeClass('fa-spin');

					//-- LOGGER
					if (fancontroldata.logger == 'on') $('.fancontrol-logger').prop('checked', true).parent().removeClass('unchecked').addClass('checked');
					else $('.fancontrol-logger').prop('checked', false).parent().removeClass('checked').addClass('unchecked');					

					//-- SUMMER
					if (fancontroldata.summer == 'on') 
					{
						$('.fancontrol-summer').prop('checked', true).parent().removeClass('unchecked').addClass('checked');
						$('.fancontrol-summer-sm-status').text('ZAP.');
					}
					else 
					{
						$('.fancontrol-summer').prop('checked', false).parent().removeClass('checked').addClass('unchecked');
						$('.fancontrol-summer-sm-status').text('VYP.');
					}

					//-- OFF
					if (fancontroldata.fanoff == 'on') $('.fancontrol-off').prop('checked', true).parent().removeClass('unchecked').addClass('checked');
					else $('.fancontrol-off').prop('checked', false).parent().removeClass('checked').addClass('unchecked');
					
					//-- MANUAL
					if (fancontroldata.fanCurrentState == 'on') 
					{
						if (fancontroldata.fanstate == 'on') $('.fancontrol-manual').data('cmd', 'fanOFF');
						else $('.fancontrol-manual').data('cmd', 'fanON');
						
						if (fancontroldata.manualFinish)
						{
							var manualFinishDate = new Date(fancontroldata.manualFinish * 1000);
							if (isDST(manualFinishDate))
							{
								manualFinishDate = new Date((fancontroldata.manualFinish - 3600) * 1000);
							}
							var hours = ("0" + manualFinishDate.getUTCHours()).slice(-2);
							var minutes = ("0" + manualFinishDate.getUTCMinutes()).slice(-2);
							$('.fancontrol-manual-end').html('<br />do ' + manualFinishDate.getUTCDate() + ' ' + months[manualFinishDate.getUTCMonth()] + '. ' + hours + ':' + minutes);
						}
						
						$('.fancontrol-manual').prop('checked', true).parent().removeClass('unchecked').addClass('checked');
					}
					else 
					{
						$('.fancontrol-manual').data('cmd', 'fanON');
						$('.fancontrol-manual').prop('checked', false).parent().removeClass('checked').addClass('unchecked');
						$('.fancontrol-manual-end').html('');
					}

					//-- VACATION
					if (fancontroldata.vacation == 'on') $('.fancontrol-vacation').prop('checked', true).parent().removeClass('unchecked').addClass('checked');
					else $('.fancontrol-vacation').prop('checked', false).parent().removeClass('checked').addClass('unchecked');
				});
				
			}
			
			function wunderground()
			{
				$.getScript("services/wunderground.php", function()
				{
					$('.wunderground-location').text(dataWU.location);
					$('.wunderground-date').text(dataWU.date);
					$('.wunderground-temperature').html(dataWU.temperature + ' &#8451;');
					$('.wunderground-humidity').text(dataWU.humidity);
					$('.wunderground-pressure').html(dataWU.pressure + ' hPa');
					$('.wunderground-feelslike').html(dataWU.feelslike + ' &#8451');
					$('.wunderground-dewpoint').html(dataWU.dewpoint + ' &#8451');
					$('.wunderground-icon').html("<img src='"+dataWU.icon+"' width='60' />");
				});
			}

			function rtc()
			{
				$.getScript("services/rtc.php", function()
				{
					//console.log(dataRTC);
				});				
			}
			
			function isDST(d)
			{
				var jan = new Date(d.getFullYear(), 0, 1);
				var jul = new Date(d.getFullYear(), 6, 1);
 				var dst = Math.max(jan.getTimezoneOffset(), jul.getTimezoneOffset());
				if (d.getTimezoneOffset() == dst)
				{
					return true;
				}
				return false;		
			}
			
			function gatecontrol()
			{
				url = 'esp/gate.php?redirect=index';
				$.ajax({method:'GET',url:url}).done(function(result) 
				{
					//console.log();
				});				
			}			
			
			function isDoubleClicked(element, delaytime) 
			{
				if (element.data("isclicked")) return true;
				element.data("isclicked", true);
				setTimeout(function(){element.removeData("isclicked");}, delaytime);
				return false;
			}
		});
	</script>
	
	<style>
		a:hover{text-decoration:none;}
		.iost{position:relative;top:-6px;}
		.sm-status{position:relative;top:-4px;}
		.label{font-size: 100% !important;}
	</style>

	<title>My Home Assistant</title>
</head>
<body>
	<div class='container-fluid'>   
		<div class='row'>     
			<div class='col-md-12'>  
				<br />
				<!--<h2>MyHome <a href='/smarthome'><button class='btn btn-default btn-lg'><span class='fa fa-refresh' aria-hidden='true'></span></button></a></h2> -->
				<!-- Nav tabs -->
				<ul class="nav nav-pills" role="tablist">
					<li role="presentation"><a href="#dashboard" aria-controls="dashboard" role="tab" data-toggle="tab"><i class="fa fa-home" aria-hidden="true"></i></a></li>
					<li role="presentation"><a href="#vetranie" aria-controls="vetranie" role="tab" data-toggle="tab">Vetranie</a></li>
					<li role="presentation"><a href="#zahrada" aria-controls="zahrada" role="tab" data-toggle="tab">Záhrada</a></li>
					<li role="presentation"><a href="#camera" aria-controls="camera" role="tab" data-toggle="tab"><i class="fa fa-camera" aria-hidden="true"></i></a></li>
					<li role="presentation"><a href="#settings" aria-controls="settings" role="tab" data-toggle="tab"><i class="fa fa-sliders" aria-hidden="true"></i></a></li>
				</ul>

				<br />

				<!-- Tab panes -->
				<div class="tab-content">
					<div role="tabpanel" class="tab-pane active" id="dashboard">
						<ul class='list-group'>
							<li class='list-group-item'>Stav vetrania <span class='pull-right iost'><span class='fancontrol-fanstate fa fa-cog fa-2x text-inverse'></span></li>
							<li class='list-group-item'>Počasie&nbsp;<small class='fancontrol-wuUpdateOn text-muted'></small> <span class='pull-right'><small class='fancontrol-wutemperature'></small> &nbsp;<small class='fancontrol-wuhumidity'>!</small><small class="fancontrol-wuControl-sm-status"></small></span></li>
							<li class='list-group-item'>Počasie 
									<span class='pull-right'>
										<span class="label label-default wunderground-humidity"><i class="fa fa-refresh fa-spin" aria-hidden="true"></i> Načítavam&hellip;</span> 
										<span class="label label-success wunderground-temperature"></span>
									</span>							
							</li>
						</ul>					
					</div>
					
					<div class="tab-pane" id="vetranie">
						<ul class='list-group'>
							<li class='list-group-item'>Stav vetrania <span class='pull-right iost'><span class='fancontrol-fanstate fa fa-cog fa-2x text-inverse'></span></li>
							<li class='list-group-item'>Prich. vzduch &nbsp;<small class='fancontrol-airUpdateOn text-muted'></small> <span class='pull-right'><span class='fancontrol-airtemperature'></span> &nbsp;<span class='fancontrol-airhumidity'><i class="fa fa-refresh fa-spin" aria-hidden="true"></i></span></span></li>
							<li class='list-group-item'>Kúpeľňa &nbsp;<small class='fancontrol-brUpdateOn text-muted'></small> <span class='pull-right rm-active sm-status'><a href="#vetranie-bathroom" class="rm-active" aria-controls="vetranie-bathroom" data-toggle="tab"><small class='sm-status fancontrol-brtemperature'></small> &nbsp;<small class='sm-status fancontrol-brhumidity'>!</small><small class="sm-status fancontrol-brControl-sm-status"></small> &nbsp;<i class="fa fa-angle-right fa-2x" aria-hidden="true"></i></a></span></li>
							<li class='list-group-item'>Počasie&nbsp;<small class='fancontrol-wuUpdateOn text-muted'></small> <span class='pull-right rm-active sm-status'><a href="#vetranie-wunderground" class="rm-active" aria-controls="vetranie-wunderground" data-toggle="tab"><small class='sm-status fancontrol-wutemperature'></small> &nbsp;<small class='sm-status fancontrol-wuhumidity'>!</small><small class="sm-status fancontrol-wuControl-sm-status"></small> &nbsp;<i class="fa fa-angle-right fa-2x" aria-hidden="true"></i></a></span></li>
							<!--<li class='list-group-item'>Systémová teplota <span class='pull-right fancontrol-systemp'><i class="fa fa-refresh fa-spin" aria-hidden="true"></i> Načítavam&hellip;</span></li>-->
							<li class='list-group-item'>Vypnúť <span class='pull-right iost'><input type='checkbox' class='ios fancontrol-off' data-esp='fancontrol' data-cmd='off' unchecked /></span></li>
							<li class='list-group-item'>Logovanie <span class='pull-right iost'><input type='checkbox' class='ios fancontrol-logger' data-esp='fancontrol' data-cmd='logger' unchecked /></span></li>
							<li class='list-group-item'><span class='manualormaintenance'>Manuálne spustenie</span> <small class='fancontrol-manual-end text-muted'></small> <span class='pull-right iost'><input type='checkbox' class='ios fancontrol-manual' data-esp='fancontrol' unchecked /></span></li>
							<li class='list-group-item'>Dovolenkový režim <span class='pull-right iost'><input type='checkbox' class='ios fancontrol-vacation' data-esp='fancontrol' data-cmd='vacation' unchecked /></span></li>
							<li class='list-group-item'>Letný režim <span class='pull-right iost rm-active'> <a href="#vetranie-summer" class="rm-active" aria-controls="vetranie-summer" data-toggle="tab"><small class="sm-status fancontrol-summer-sm-status"></small> &nbsp; <i class="fa fa-angle-right fa-2x" aria-hidden="true"></i></a></span></li> 
							<li class='list-group-item'>Reštart <span class='pull-right'><a href='javascript:void(0)' class='aos' data-esp='fancontrol' data-cmd='reboot'><i class="fa fa-refresh" aria-hidden="true"></i></a></span></li>
						</ul>
					</div>
					
					<!-- SUBPAGES FOR VETRANIE START -->
					<div class="tab-pane" id="vetranie-bathroom">
						<a href="#vetranie" aria-controls="vetranie" role="tab" data-toggle="tab"><i class="fa fa-angle-left" aria-hidden="true"></i> &nbsp; Vetranie</a> &nbsp; <i class="fa fa-caret-right" aria-hidden="true"></i> &nbsp; <b>Kúpeľňa</b><br /><br />
						<ul class='list-group'>
							<li class='list-group-item'>Použiť dáta z kúpelne <span class='pull-right iost'><input type='checkbox' class='ios fancontrol-brControl' data-esp='fancontrol' data-cmd='useBathroomData' unchecked /></span></li>
							<li class='list-group-item brControlx'>Aktualizované <span class='pull-right fancontrol-brUpdateOn text-muted'></span></li>
							<li class='list-group-item brControlx'>Teplota <span class='pull-right fancontrol-brtemperature'><i class="fa fa-refresh fa-spin" aria-hidden="true"></i> Načítavam&hellip;</span></li>
							<li class='list-group-item brControlx'>Vlhkosť <span class='pull-right fancontrol-brhumidity'><i class="fa fa-refresh fa-spin" aria-hidden="true"></i> Načítavam&hellip;</span></li>
						</ul>	
						<small>
							Ak je povolené spracovanie dát z kúpelne, bude sa vetranie riadiť vlhkosťou v kúpelni. Pri vhlkosti vyššej ako 70% sa vetranie
							zapne a zostane zapnuté pokiaľ vlhkosť neklesne pod 68%.
						</small>
					</div>
					
					<div class="tab-pane" id="vetranie-summer">
						<a href="#vetranie" aria-controls="vetranie" role="tab" data-toggle="tab"><i class="fa fa-angle-left" aria-hidden="true"></i> &nbsp; Vetranie</a> &nbsp; <i class="fa fa-caret-right" aria-hidden="true"></i> &nbsp; <b>Letný režim</b><br /><br />
						<ul class='list-group'>
							<li class='list-group-item'>Letný režim <span class='pull-right iost'><input type='checkbox' class='ios fancontrol-summer' data-esp='fancontrol' data-cmd='summer' unchecked /></span></li> 
						</ul>	
						<small>
							Letný režim sa používa na vypnutie vetrania počas letných nocí, kedy sa dom vychladzuje otvorenými oknami.
							Vetranie je vypnuté od 22. hodiny do 9. hodiny ráno. Po tomto čase prejde vetranie do normáneho režimu.
						</small>
					</div>
					
					<div class="tab-pane" id="vetranie-wunderground">
						<a href="#vetranie" aria-controls="vetranie" role="tab" data-toggle="tab"><i class="fa fa-angle-left" aria-hidden="true"></i> &nbsp; Vetranie</a> &nbsp; <i class="fa fa-caret-right" aria-hidden="true"></i> &nbsp; <b>Počasie podľa OWM API</b><br /><br />
						<ul class='list-group'>
							<li class='list-group-item'>Použiť dáta z OWM API <span class='pull-right iost'><input type='checkbox' class='ios fancontrol-wuControl' data-esp='fancontrol' data-cmd='useWundergroundData' unchecked /></span></li>
							<li class='list-group-item wuControlx'>Aktualizované <span class='pull-right fancontrol-wuUpdateOn text-muted'></span></li>
							<li class='list-group-item wuControlx'>Teplota <span class='pull-right fancontrol-wutemperature'><i class="fa fa-refresh fa-spin" aria-hidden="true"></i> Načítavam&hellip;</span></li>
							<li class='list-group-item wuControlx'>Vlhkosť <span class='pull-right fancontrol-wuhumidity'><i class="fa fa-refresh fa-spin" aria-hidden="true"></i> Načítavam&hellip;</span></li>
						</ul>	
						<small>
							Ak je povolené použiť dáta zo servera na počasie, bude sa vetranie riadiť podľa týchto údajov. 
							Pri vhlkosti vyššej ako 90% sa vetranie vypne a zostane vypnuté pokiaľ vlhkosť neklesne. 
							Pokiaľ je povolený letný režim a vonkajšia teplota klesne alebo je rovná 23 stupňov, vetranie sa vypne. 
							Predpokladá sa, že už budú otvorené okná.
						</small>
					</div>					
					<!-- SUBPAGES FOR VETRANIE END -->
					
					<div role="tabpanel" class="tab-pane" id="zahrada">
						<ul class='list-group'>
							<li class='list-group-item'>Ovládanie brány <span class='pull-right iost'><a href='javascript:void(0)' class='aos' data-wait='3000' data-esp='gate' data-cmd='toggle'><i class="fa fa-exchange fa-2x" aria-hidden="true"></i></a></span></li>
							<li class='list-group-item'>Filtrácia bazéna <span class='pull-right iost'><input type='checkbox' class='ios zahrada-bazen' data-esp='' data-cmd='' unchecked /></span></li>							
							<li class='list-group-item'>Kvalita vody <span class='pull-right iost rm-active'> <a href="#zahrada-bazen" class="rm-active" aria-controls="zahrada-bazen" data-toggle="tab"><small class="sm-status zahrada-bazen-sm-status">PH 6.3, 26.4 &#8451;</small> &nbsp; <i class="fa fa-angle-right fa-2x" aria-hidden="true"></i> &nbsp; </a></span></li> 
						</ul>
					</div>
					
					<div role="tabpanel" class="tab-pane" id="camera">
						<p>
							<a href='javascript:void(0)' class='aos' data-esp='cam' data-cmd='move_left9.9'><i class="fa fa-arrow-left fa-2x" aria-hidden="true"></i></a>
							<a href='javascript:void(0)' class='aos' data-esp='cam' data-cmd='move_right9.9'><i class="fa fa-arrow-right fa-2x" aria-hidden="true"></i></a>
							<a href='javascript:void(0)' class='aos' data-esp='cam' data-cmd='fb_stop'><i class="fa fa-pause fa-2x" aria-hidden="true"></i></a>
							<a href='javascript:void(0)' class='aos' data-esp='cam' data-cmd='move_backward9.9'><i class="fa fa-arrow-up fa-2x" aria-hidden="true"></i></a>
							<a href='javascript:void(0)' class='aos' data-esp='cam' data-cmd='move_forward9.9'><i class="fa fa-arrow-down fa-2x" aria-hidden="true"></i></a>
						</p>
						<img src='http://yum4net.asuscomm.com:7373/cgi/video/video.cgi'>
					</div>						
					
					<div role="tabpanel" class="tab-pane" id="settings">
						<ul class='list-group'>
							<li class='list-group-item'>Letný čas <span class='pull-right iost'><input type='checkbox' class='ios settings-dst' data-esp='' data-cmd='' unchecked /></span></li> 
						</ul>
					</div>					
				</div>   

			</div>
		</div>
	</div>
</body>
</html>
