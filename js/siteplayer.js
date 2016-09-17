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

	if (type == "3") { // SoundCloud
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

	} else {
		player = Popcorn.smart(player_id, url);
	}
  
	player_type = type;

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

		player

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
		if (player) player.volume(vol);
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
