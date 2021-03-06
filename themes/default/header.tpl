<!DOCTYPE html>
<html lang="ja" xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>{$site_title}</title>
<link rel="shortcut icon" href="{$relative_dir_to_top}/favicon.ico" />
<link rel="stylesheet" type="text/css" href="{$relative_dir_to_top}/css/main.css" />
<link rel="stylesheet" type="text/css" href="{$relative_dir_to_top}/css/plyr.css" />
<link rel="stylesheet" type="text/css" href="{$relative_dir_to_top}/css/jquery-ui-1.9.2.custom.min.css" />
<link rel="alternate" type="application/rss+xml" href="{$relative_dir_to_top}/rss.php" title="RSS 2.0" />
<meta name="keywords" content="{$site_title}" />
<meta name="description" content="{$site_title}" />
<script type="text/javascript" src="{$relative_dir_to_top}/js/popcorn-complete.js"></script>
<script src="https://cdn.shr.one/0.1.9/shr.js"></script>
<script type="text/javascript" src="{$relative_dir_to_top}/js/plyr.js"></script>
<script type="text/javascript" src="{$relative_dir_to_top}/js/jquery-1.8.3.min.js"></script>
<script type="text/javascript" src="{$relative_dir_to_top}/js/jquery-ui-1.9.2.custom.min.js"></script>
<script type="text/javascript" src="{$relative_dir_to_top}/js/cookie.js"></script>
<script type="text/javascript" src="{$relative_dir_to_top}/js/siteplayer.js"></script>
{$additional_header}
<script type="text/javascript">
$(document).ready(function(){
	// volume setup
	var vol = ReadCookie('player-volume', 80);
	$('.volume-slider').slider({
		change: function(event, ui) {
			var vol = $('.volume-slider').slider('value');
			volume(vol / 100.0);
			WriteCookie('player-volume', vol, 90);
		}
	}).slider('value', vol);

	var val = ReadCookie('player-loop');
	loop(val=='true'?true:false);
	if(val=='true'){
		$('.loop-check').attr('checked', 'checked');
	}
	$('.loop-check').on('click', function(event){
		loop(this.checked);
		WriteCookie('player-loop', (this.checked ? 'true' : 'false'), 90);
	});

	// video setup
	init('.video', 400);
	$('a.play-link').on('click', function(event){
		event.preventDefault();
		pause();
		var type = this.getAttribute("type");
		var href = this.getAttribute("href")
		setTimeout(function(){
			load(type, href);
			play();
		}, 200);
	});
});
</script>
</head>
<body>
<div class="header">
	<div class="control-box">
		<a onclick="video_box_open()">↓</a>
		<a onclick="video_box_close()">↑</a>
	</div>
	<div class="volume-slider-box">
		<a onclick="play()">|&gt;</a> &nbsp;&nbsp;
		<a onclick="pause()">||</a> &nbsp;&nbsp;
		loop: <input type="checkbox" class="loop-check"></input> &nbsp;&nbsp;
		Vol: <div class="volume-slider"></div>
	</div>
</div>