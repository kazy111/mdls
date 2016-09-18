var player_id = '';
var player;
var player_type;
var players;
var player_height = 400;
var header_height = 20;
var player_volume = 1;
var player_autoplay = true;
var player_loop = true;

function init(id, height)
{
	player_id = id;
	player_height = height;

	var n = $(player_id);
	n[0].height = player_height;

	players = {
		// 1: Popcorn.youtube(player_id),
		// 2: Popcorn.vimeo(player_id),
		// 3: Popcorn.soundcloud(player_id, '', {"controls": 1, "autoplay": true})
		3: Popcorn( Popcorn.HTMLSoundCloudAudioElement(player_id) )
	};
}

function load(type, url)
{
	var n = $(player_id);
	if (n.size() === 0) {
		return;
	}

	if (player) {
		player.pause();
		if( player_type != "3" )
			player.destroy();
	}

	n.children().each(function(x,y){
		if( y.id.indexOf('soundcloud-') !== 0 ) {
			y.parentNode.removeChild(y);
		} else {
			y.style.visibility = 'hidden';
			y.style.zIndex = -1;
			y.style.width = 0;
		}
	});

	n[0].height = player_height;
	n.css('height', player_height);
	n.slideDown();
	$('.main').css('padding-top', player_height+header_height);

	var needInitialize = true;
	// create player
	player_type = type;
	switch(type){
		case "1":
			player = setupPlyr(player_id, 'youtube', url.substring(url.indexOf("v=")+2));			
			return;
		case "2":
			player = setupPlyr(player_id, 'vimeo', url.substring(url.indexOf("/", 8)+1));
			return;
			//player = Popcorn.smart(player_id, url);
			//break;
		case "3": // SoundCloud
			player = players[3];
			if (player.media.src != "") {
				//player.currentTime(0);
				needInitialize = false;
			}
			player.media.src = url;

			n.children().each(function(x,y){
				if( y.id.indexOf('soundcloud-') === 0 ) {
					y.style.visibility = 'visible';
					y.style.zIndex = 0;
					y.style.width = '100%';
				}
			});
			break;
	}

	// add a footnote at 2 seconds, and remove it at 6 seconds
	player.controls(true);

	player.autoplay(player_autoplay);
	if (needInitialize) {
		// fallback autoplay
		player.on('canplay', function(){
			player.volume(player_volume);
			if(player_autoplay){
				setTimeout(function(){
					if( player && player.paused() ) player.play();
				}, 500);
			}
		});

		loop(player_loop);

		player.on('error', function(){
			// move to next?
		});
	} else {
		if(player_autoplay){
			setTimeout(function(){
				if( player && player.paused() ) {
					 player.play();
				}
			}, 500);
		}
	}

}

function play(n)
{
	if (player) {
		if (n) {
			player.play(n);
		} else {
			player.play();
		}
     }
}

function pause()
{
     if (player) player.pause();
}

function video_box_close()
{
	$(player_id).slideUp();
	$('.main').css('padding-top', header_height);
}

function video_box_open()
{
	if(player){
		$(player_id).slideDown();
		$('.main').css('padding-top', player_height+header_height);
	}
}

function destroy()
{
	if(player) player.destroy();
}

function volume(vol)
{
	if (vol !== undefined) {
		player_volume = vol;
		if (player) {
			switch (player_type) {
				case '1':
					player.setVolume(vol * 10);
					break;
				default:
					player.volume(vol);
			}
		}
	} else {
		return player ? player.volume() : 0;
	}
}

function loop(val)
{
	if(val !== undefined) {
		player_loop = val;
		if(!player) return;
		if(val){
			player.on('ended', function(){
				switch(player_type){
					case '1': setTimeout(function(){
								//player.play(0);
								//player.pause();
								player.play();
							}, 500); break;
					case '2': setTimeout(function(){
								//player.play(0);
								//player.pause();
								player.play();
							}, 1000); break;
					case '3': setTimeout(function(){
								player.play();
							}, 1000); break;
				}
			});
		} else {
			player.off('ended');
		}
	} else {
		return player_loop;
	}
}

function setupPlyr(boxid, type, videoId)
{
	var box = $(boxid);
	box.html('<div data-type="'+type+'" data-video-id="'+videoId+'"></div>');
	
	// Setup the player
	var instances = plyr.setup({
		autoplay:			player_autoplay,
		loop:				player_loop,
		volume:				player_volume * 10,
		//debug:              true,
		title:				'Youtube',
		iconUrl:			'./css/plyr.svg',
		tooltips: {
			controls:		false
		},
		captions: {
			defaultActive:	true
		}
	});
	plyr.loadSprite('./css/plyr.svg');

	// Plyr returns an array regardless
	var player = instances[0];
	try {
		player.source({
			type:       'video',
			title:      '',
			sources: [{
				src:    videoId,
				type:   type
			}]
		});
	} catch (ex) {
		if(ex){

		}
	}

	return player;
}
