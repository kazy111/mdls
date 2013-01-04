var player_id = '';
var player;
var player_type;
var players;
var player_height = 400;
var header_height = 20;
var player_volume = 1;
var player_autoplay = true;

function init(id, height)
{
	player_id = id;
	player_height = height;

	var n = $(player_id);
	n[0].height = player_height;

	players = {
		// 1: Popcorn.youtube(player_id),
		// 2: Popcorn.vimeo(player_id),
		3: Popcorn.soundcloud(player_id, '', {"controls": 1, "autoplay": true})
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
		player.destroy();
	}

	n.children().each(function(x,y){
		if( ! y.id.startsWith('soundcloud-') ) {
			y.parentNode.removeChild(y);
		} else {
			y.style.visibility = 'hidden';
		}
	});

	n[0].height = player_height;
	n.css('height', player_height);
	n.slideDown();
	$('.main').css('padding-top', player_height+header_height);

	var options = {"controls": 1, "autoplay": player_autoplay};
	switch(type){
		case "1": player = Popcorn.youtube(player_id, url+'&showinfo=1', options); break;
		case "2": player = Popcorn.vimeo(player_id, url, options); break;
		// case "3": player = Popcorn.soundcloud(player_id, url, options); break;
		case "3": player = players[3];
				player.currentTime(0);
				player.media.src = url;
				n.children().each(function(x,y){
					if( y.id.startsWith('soundcloud-') ) {
						y.style.visibility = 'visible';
					}
				});
				break;
		default: player = false;
	}
    player_type = type;
     // add a footnote at 2 seconds, and remove it at 6 seconds
	player.controls(true);
	player.autoplay(player_autoplay);
	player.volume(player_volume);
	player.on('ended', function(){
		console.log('ended');
		switch(player_type){
			case '1': setTimeout(function(){
						player.play(0);
						player.pause();
						player.play();
					}, 500); break;
			case '2': setTimeout(function(){
						//player.play(0);
						//player.pause();
						player.play();
					}, 1000); break;
			case '3': player.play(0);
					break;
		}
	});
	player.on('error', function(){

	});
	// fallback autoplay
	player.on('canplay', function(){
		if(player_autoplay){
			setTimeout(function(){
				if( player && player.paused() ) player.play();
			}, 500);
		}
	});

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

function set_volume(vol)
{
	player_volume = vol;
    if (player) player.volume(vol);
}

function get_volume()
{
	return player ? player.volume() : 0;
}
