<?php
/**
 * Support for the MediaElement.js video and audio player by John Dyer (http://mediaelementjs.com - license: MIT).
 * It will play natively via HTML5 in capable browsers and is responsive.
 *
 * Audio: <var>.mp3</var>, <var>.m4a</var> - Counterpart formats <var>.oga</var> and <var>.webma</var> supported (see note below!)<br>
 * Video: <var>.m4v</var>/<var>.mp4</var>, <var>.flv</var> - Counterpart formats <var>.ogv</var> and <var>.webmv</var> supported (see note below!)
 *
 * IMPORTANT NOTE ON OGG AND WEBM COUNTERPART FORMATS:
 * The counterpart formats are not valid formats for Zenphoto itself and not recognized as items as that would confuse the management.
 * Therefore these formats can be uploaded via FTP only.
 * The files need to have the same file name (beware of the character case!). In single player usage, the player
 * will check via the file system if a counterpart file exists and if counterpart support is enabled.
 * Firefox seems to prefer the <var>.oga</var> and <var>.ogv</var> while Chrome <var>.webma</var> and <var>.webmv</var>
 *
 * Since the flash fallback covers all essential formats this is not much of an issue for visitors though.
 *
 * If you have problems with any format being recognized, you might need to tell your server about the mime types first:
 * See examples on {@link http://mediaelementjs.com} under "installation".
 *
 * Subtitle and chapter support for videos (NOTE: NOT IMPLEMENTED YET!):
 * It supports .srt files. Like the counterpart formats MUST be uploaded via FTP!
 *
 * For each language a separate file must be supplied and each filename must end with with a 2-letter language code.
 * They must follow this naming convention:
 * subtitles file: <nameofyourvideo>_subtitles-en.srt
 * chapters file: <name of your video>_chapters-en.srt
 *
 * Example: yourvideo.mp4 with yourvideo_subtitles-en.srt and yourvideo_chapters-en.srt
 *
 * CONTENT MACRO:<br>
 * Mediaelementjs attaches to the content_macro MEDIAPLAYER you can use within normal text of Zenpage pages or articles for example.
 * You have to supply an albumname and a filename.
 * The <width> parameter is optional. If omitted, the audio/video will be 100% wide (responsive)
 *
 * Usage:
 * [MEDIAPLAYER <albumname> <imagefilename> <width>]
 *
 * Example:
 * [MEDIAPLAYER album1 video.mp4 400] (400px wide) or [MEDIAPLAYER album1 video.mp4] (responsive)
 *
 * <b>NOTE:</b> This player does not support external albums!
 *
 * Playlist (beta):
 * Basic playlist support (adapted from Andrew Berezovsky – https://github.com/duozersk/mep-feature-playlist):
 * Enable the option to load the playlist script support. Then call on your theme's album.php the method $_zp_multimedia_extension->playlistPlayer();
 * echo $_zp_multimedia_extension->playlistPlayer('video','',''); //video playlist using all available .mp4, .m4v, .flv files only
 * echo $_zp_multimedia_extension->playlistPlayer('audio','',''); //audio playlist using all available .mp3, .m4a files only
 * Additionally you can set a specific albumname on the 2nd parameter to call a playlist outside of album.php
 *
 * Notes: Mixed audio and video playlists are not possible.
 *
 * @author Malte Müller (acrylian) <info@maltem.de>
 * @copyright 2014 Malte Müller
 * @license GPL v3 or later
 * @package plugins
 * @subpackage media
 */
$plugin_is_filter = 5 | CLASS_PLUGIN;
$plugin_description = gettext("Enable <strong>mediaelement.js</strong> to handle multimedia files.");
$plugin_notice = gettext("<strong>IMPORTANT</strong>: Only one multimedia player plugin can be enabled at the time and the class-video plugin must be enabled, too.").'<br /><br />'.gettext("Please see <a href='http://http://mediaelementjs.com'>mediaelementjs.com</a> for more info about the player and its license.");
$plugin_author = "Malte Müller (acrylian)";
$plugin_disable = (getOption('album_folder_class') === 'external')?gettext('This player does not support <em>External Albums</em>.'):false;
$plugin_version = '1.1.1';
$option_interface = 'mediaelementjs_options';

if (!empty($_zp_multimedia_extension->name) || $plugin_disable) {
	enableExtension('mediaelementjs_player', 0);

//NOTE: the following text really should be included in the $plugin_disable statement above so that it is visible
//on the plugin tab

	if (isset($_zp_multimedia_extension)) {
		trigger_error(sprintf(gettext('Mediaelementjs_player not enabled, %s is already instantiated.'), get_class($_zp_multimedia_extension)), E_USER_NOTICE);
	}
} else {
	Gallery::addImageHandler('flv', 'Video');
	Gallery::addImageHandler('mp3', 'Video');
	Gallery::addImageHandler('mp4', 'Video');
	Gallery::addImageHandler('m4v', 'Video');
	Gallery::addImageHandler('m4a', 'Video');

	zp_register_filter('content_macro', 'mediaelementjs_player::macro');
}

class mediaelementjs_options {

	function __construct() {
		setOptionDefault('mediaelementjs_skin', 'mediaelementplayer');
		setOptionDefault('mediaelementjs_showcontrols', 1);
		setOptionDefault('mediaelementjs_playpause', 1);
		//setOptionDefault('mediaelementjs_loop', 0);
		setOptionDefault('mediaelementjs_progress', 1);
		setOptionDefault('mediaelementjs_current', 1);
		setOptionDefault('mediaelementjs_duration', 1);
		setOptionDefault('mediaelementjs_tracks', 0);
		setOptionDefault('mediaelementjs_startlanguage', 'none');
		setOptionDefault('mediaelementjs_volume', 1);
		setOptionDefault('mediaelementjs_fullscreen', 1);
		setOptionDefault('mediaelementjs_videowidth', '100%');
		//setOptionDefault('mediaelementjs_videoheight', 270);
		setOptionDefault('mediaelementjs_audiowidth', '100%');
		//setOptionDefault('mediaelementjs_audioheight', 30);
		setOptionDefault('mediaelementjs_preload', 0);
		setOptionDefault('mediaelementjs_poster', 1);
		//setOptionDefault('mediaelementjs_videoposterwidth', 640);
		//setOptionDefault('mediaelementjs_videoposterheight', 360);
    	setOptionDefault('mediaelementjs_audioposter', 1);
    	//setOptionDefault('mediaelementjs_audiopostercrop', 0);
    	setOptionDefault('mediaelementjs_audioposterwidth', 640);
		setOptionDefault('mediaelementjs_audioposterheight', 360);
		setOptionDefault('mediaelementjs_preload', 0);
		// Playlist
		setOptionDefault('mediaelementjs_playlist', 0);
		setOptionDefault('mediaelementjsplaylist_showcontrols', 1);
		setOptionDefault('mediaelementjsplaylist_prevtrack', 1);
		setOptionDefault('mediaelementjsplaylist_playpause', 1);
		setOptionDefault('mediaelementjsplaylist_nexttrack', 1);
		setOptionDefault('mediaelementjsplaylist_loop', 0); // When enabled in the controls by the user it also functions as a sort of auto next track...
		setOptionDefault('mediaelementjsplaylist_shuffle', 1);
		setOptionDefault('mediaelementjsplaylist_progress', 1);
		setOptionDefault('mediaelementjsplaylist_current', 1);
		setOptionDefault('mediaelementjsplaylist_duration', 1);
		setOptionDefault('mediaelementjsplaylist_volume', 1);
		setOptionDefault('mediaelementjsplaylist_fullscreen', 1);
		setOptionDefault('mediaelementjs_videoplaylistbackground', '');
		setOptionDefault('mediaelementjsplaylist_preload', 1); // Otherwise Safari does not play well
	}

	function getOptionsSupported() {
		$skins = getMediaelementjsSkins();
 		return array(
 			gettext('Player skin') => array('key'	=> 'mediaelementjs_skin', 'type' => OPTION_TYPE_SELECTOR,
 				'order' => 0,
				'selections' => $skins,
				'desc'	=> gettext("Select the skin (theme) to use. <br />NOTE: Since the skin is pure HTML/CSS only there may be display issues with certain themes that require manual adjustments. The two Zenphoto custom skins are responsive regarding the player width. Place custom skin within the root plugins folder. See plugin documentation for more info.")),

 			array('type' => OPTION_TYPE_NOTE,
				'order' => 0.1,
				'desc' => gettext("<h2>Single audio and video options</h2><hr />")),

 			gettext('Control bar') => array(
				'key' => 'mediaelementjs_controlbar',
				'type' => OPTION_TYPE_CHECKBOX_UL,
				'order' => 1,
				'checkboxes' => array( // The definition of the checkboxes
					gettext('Play/Pause')=>'mediaelementjs_playpause',
					//gettext('Loop')=>'mediaelementjs_loop',
					gettext('Progress')=>'mediaelementjs_progress',
					gettext('Current')=>'mediaelementjs_current',
					gettext('Duration')=>'mediaelementjs_duration',
					gettext('Tracks (Video only)')=>'mediaelementjs_tracks',
					gettext('Volume')=>'mediaelementjs_volume',
					gettext('Fullscreen')=>'mediaelementjs_fullscreen',
					gettext('Always show controls')=>'mediaelementjs_showcontrols'
				),
				'desc' => gettext('Enable what should be shown in the player control bar.')),

// Below field should only visible when "Tracks" are enabled.
// Best would be a dropdown with available languages...if any. Should default to "none" is no files are present.
// No idea how to do this.
// Right now I have the field default to "none" if nothing is entered.
			gettext('Start language') => array(
				'key' => 'mediaelementjs_startlanguage', 'type' => OPTION_TYPE_TEXTBOX,
				'order'=>1.1,
				'desc' => gettext('The language of subtitles to start with.')),
			gettext('Video width') => array(
				'key' => 'mediaelementjs_videowidth', 'type' => OPTION_TYPE_TEXTBOX,
				'order'=>2,
				'desc' => gettext('Pixel value (i.e. without <code>px</code>). Leave empty for responsive layouts')),
// Not needed
			//gettext('Video height') => array(
			//	'key' => 'mediaelementjs_videoheight', 'type' => OPTION_TYPE_TEXTBOX,
			//	'order'=>3,
			//	'desc' => gettext('Pixel value (i.e. without <code>px</code>) or <code>100%</code> for responsive layouts')),
			gettext('Video Poster') => array(
				'key' => 'mediaelementjs_poster', 'type' => OPTION_TYPE_CHECKBOX,
				'order'=>4,
				'desc' => gettext('If a poster of the videothumb should be shown. This is cropped to fit the player size as the player would distort image not fitting the player dimensions otherwise.')),
// Not needed
			//gettext('Video poster width') => array(
			//	'key' => 'mediaelementjs_videoposterwidth', 'type' => OPTION_TYPE_TEXTBOX,
			//	'order'=>5,
			//	'desc' => gettext('Max width of the video poster (pixel value without <code>px</code>). Image will be sized automatially in responsive layouts. Might require theme CSS changes to work correctly.')),
// Not needed
			//gettext('Video poster height') => array(
			//	'key' => 'mediaelementjs_videoposterheight', 'type' => OPTION_TYPE_TEXTBOX,
			//	'order'=>6,
			//	'desc' => gettext('Height of the video poster (pixel value without <code>px</code>). Image will be sized automatially in responsive layouts. Might require theme CSS changes to work correctly.')),
			gettext('Audio width') => array(
				'key' => 'mediaelementjs_audiowidth', 'type' => OPTION_TYPE_TEXTBOX,
				'order'=>7,
				'desc' => gettext('Pixel value (i.e. without <code>px</code>). Leave empty for responsive layouts (default)')),
// Not needed
			//gettext('Audio height') => array(
			//	'key' => 'mediaelementjs_audioheight', 'type' => OPTION_TYPE_TEXTBOX,
			//	'order'=>8,
			//	'desc' => gettext('Pixel value (i.e. without <code>px</code>) or <code>100%</code> for responsive layouts (default)')),
      		gettext('Audio poster') => array(
				'key' => 'mediaelementjs_audioposter', 'type' => OPTION_TYPE_CHECKBOX,
				'order'=>9,
				'desc' => gettext('If an image of the videothumb should be shown with audio files. You need to set the width/height. This is cropped to fit the size.')),
      		gettext('Audio poster width') => array(
				'key' => 'mediaelementjs_audioposterwidth', 'type' => OPTION_TYPE_TEXTBOX,
				'order'=>10,
				'desc' => gettext('Max width of the audio poster (pixel value without <code>px</code>)). Image will be sized automatially in responsive layouts. Might require theme CSS changes to work correctly.')),
			gettext('Audio poster height') => array(
				'key' => 'mediaelementjs_audioposterheight', 'type' => OPTION_TYPE_TEXTBOX,
				'order'=>11,
				'desc' => gettext('Height of the audio poster (pixel value without <code>px</code>). Image will be sized automatially in responsive layouts. Might require theme CSS changes to work correctly.')),
			gettext('Preload') => array(
				'key' => 'mediaelementjs_preload', 'type' => OPTION_TYPE_CHECKBOX,
				'order'=>12,
				'desc' => gettext('If the files should be preloaded (Note: if this works is browser dependent and might not work in all!).')),

			// Playlist
			array('type' => OPTION_TYPE_NOTE,
				'order' => 50,
				'desc' => gettext("<h2>Playlist options</h2><hr />")),

			gettext('Playlist support') => array(
				'key' => 'mediaelementjs_playlist', 'type' => OPTION_TYPE_CHECKBOX,
				'order'=>51,
				'desc' => gettext('If enabled the script for playlist support is loaded. For playlists either use the macro or modify your theme.')),
			gettext('Playlist control bar') => array(
				'key' => 'mediaelementjsplaylist_controlbar',
				'type' => OPTION_TYPE_CHECKBOX_UL,
				'order' =>52,
				'checkboxes' => array( // The definition of the checkboxes
					gettext('Previous track')=>'mediaelementjsplaylist_prevtrack',
					gettext('Play/Pause')=>'mediaelementjsplaylist_playpause',
					gettext('Next track')=>'mediaelementjsplaylist_nexttrack',
					gettext('Loop')=>'mediaelementjsplaylist_loop',
					gettext('Shuffle')=>'mediaelementjsplaylist_shuffle',
					gettext('Progress')=>'mediaelementjsplaylist_progress',
					gettext('Current')=>'mediaelementjsplaylist_current',
					gettext('Duration')=>'mediaelementjsplaylist_duration',
					gettext('Volume')=>'mediaelementjsplaylist_volume',
					gettext('Fullscreen')=>'mediaelementjsplaylist_fullscreen',
					gettext('Always show controls')=>'mediaelementjsplaylist_showcontrols'
				),
				'desc' => gettext('Enable what should be shown in the playlist control bar.')),
			gettext('Playlist audio width') => array(
				'key' => 'mediaelementjs_audioplaylistwidth', 'type' => OPTION_TYPE_TEXTBOX,
				'order' => 53,
				'desc' => gettext('Pixel value (i.e. without <code>px</code>).<br />If empty defaults to <code>100%</code>(responsive).')),
			gettext('Playlist audio height') => array(
				'key' => 'mediaelementjs_audioplaylistheight', 'type' => OPTION_TYPE_TEXTBOX,
				'order' => 54,
				'desc' => gettext('Pixel value (i.e. without <code>px</code>).<br />If empty defaults to <code>300</code>.')),
			gettext('Playlist video width') => array(
				'key' => 'mediaelementjs_videoplaylistwidth', 'type' => OPTION_TYPE_TEXTBOX,
				'order' => 55,
				'desc' => gettext('Pixel value (i.e. without <code>px</code>).<br />If empty defaults to <code>100%</code>(responsive).')),
			gettext('Playlist video height') => array(
				'key' => 'mediaelementjs_videoplaylistheight', 'type' => OPTION_TYPE_TEXTBOX,
				'order' => 56,
				'desc' => gettext('Pixel value (i.e. without <code>px</code>).<br />If empty defaults to <code>360</code>.')),
			gettext('Playlist video background') => array(
				'key' => 'mediaelementjs_videoplaylistbackground', 'type' => OPTION_TYPE_TEXTBOX,
				'order' => 56.1,
				'desc' => gettext('Background image for the video playlist. Only shows at startup.<br />File should be present in your theme\'s <code>images</code> folder.')),
			gettext('Playlist preload') => array(
				'key' => 'mediaelementjsplaylist_preload', 'type' => OPTION_TYPE_CHECKBOX,
				'order'=>57,
				'desc' => gettext('If the files should be preloaded (Note: if this works is browser dependent and might not work in all!).')),

		);
	}
}

if ( !getOption('mediaelementjs_startlanguage') ) {
	setOption('mediaelementjs_startlanguage', 'none', true, '"'.FULLWEBPATH.'/'.USER_PLUGIN_FOLDER.'/mediaelementjs_player"');
}

/** NOT USED YET
 * Gets the skin names and css files
 *
 */
function getMediaelementjsSkins() {
	$all_skins = array();
	//$default_skins_dir = FULLWEBPATH.'/'.USER_PLUGIN_FOLDER.'/mediaelementjs_player/skin';
	$user_skins_dir = SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/mediaelementjs_player/skin/';
	$filestoignore = array( '.', '..','.DS_Store','Thumbs.db','.htaccess','.svn');
	//$skins = array_diff(scandir($default_skins_dir),array_merge($filestoignore));
	$skins = scandir($user_skins_dir);
	//$default_skins = getMediaelementjsSkinCSS($skins,$default_skins_dir);
	$user_skins = getMediaelementjsSkinCSS($skins,$user_skins_dir);
	//echo "<pre>";print_r($default_skins);echo "</pre>";
	$skins2 = @array_diff(scandir($user_skins_dir),array_merge($filestoignore));
	if(is_array($skins2)) {
		$user_skins = getMediaelementjsSkinCSS($skins2,$user_skins_dir);
		//echo "<pre>";print_r($user_skins);echo "</pre>";
		//$default_skins = array_merge($default_skins,$user_skins);
	}
	return $user_skins;
}
/** NOT USED YET
 * Gets the css files for a skin. Helper function for getMediaelementjsSkins().
 *
 */
function getMediaelementjsSkinCSS($skins, $dir) {
	$skin_css = array();
	foreach ($skins as $skin) {
		$css = safe_glob($dir.'/'.$skin.'/*.css');
		if ($css) {
			$skin_css = array_merge($skin_css, array($skin => $css[0]));	// a skin should only have one css file so we just use the first found
		}
	}
	return $skin_css;
}



class mediaelementjs_player {
	public $mode = '';

	function __construct() {
	}

	static function getFeatureOptionsPlayer() {
		$array = array();
		if(getOption('mediaelementjs_playpause')) $array[] = 'playpause';
		//if(getOption('mediaelementjs_loop')) $array[] = 'loop';
		if(getOption('mediaelementjs_progress')) $array[] = 'progress';
		if(getOption('mediaelementjs_current')) $array[] = 'current';
		if(getOption('mediaelementjs_duration')) $array[] = 'duration';
		if(getOption('mediaelementjs_tracks')) $array[] = 'tracks';
		if(getOption('mediaelementjs_volume')) $array[] = 'volume';
		if(getOption('mediaelementjs_fullscreen')) $array[] = 'fullscreen';
		$count = '';
		$featurecount = count($array);
		$features = '';
		foreach($array as $f) {
			$count++;
			$features .= "'".$f."'";
			if($count != $featurecount) {
				$features .= ',';
			}
		}
		return $features;
	}

	static function getFeatureOptionsPlaylist() {
		$array = array();
		$array[] = 'playlistfeature'; //required
		if(getOption('mediaelementjsplaylist_prevtrack')) $array[] = 'prevtrack';
		if(getOption('mediaelementjsplaylist_playpause')) $array[] = 'playpause';
		if(getOption('mediaelementjsplaylist_nexttrack')) $array[] = 'nexttrack';
		if(getOption('mediaelementjsplaylist_loop')) $array[] = 'loop';
		if(getOption('mediaelementjsplaylist_shuffle')) $array[] = 'shuffle';
		$array[] = 'playlist'; // playlist icon right before the progress bar
		if(getOption('mediaelementjsplaylist_progress')) $array[] = 'progress';
		if(getOption('mediaelementjsplaylist_current')) $array[] = 'current';
		if(getOption('mediaelementjsplaylist_duration')) $array[] = 'duration';
		if(getOption('mediaelementjsplaylist_volume')) $array[] = 'volume';
		if(getOption('mediaelementjsplaylist_fullscreen')) $array[] = 'fullscreen';
		$count = '';
		$featurecount = count($array);
		$features = '';
		foreach($array as $f) {
			$count++;
			$features .= "'".$f."'";
			if($count != $featurecount) {
				$features .= ',';
			}
		}
		return $features;
	}

	static function mediaelementjs_js() {

		$skin = getOption('mediaelementjs_skin');

		if(file_exists($skin)) {
			$skin = str_replace(SERVERPATH,FULLWEBPATH,$skin); //replace SERVERPATH as that does not work as a CSS link
		} else {
			//$skin = FULLWEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/mediaelementjs_player/mediaelementplayer.css';
			$skin = FULLWEBPATH . '/' . USER_PLUGIN_FOLDER . '/mediaelementjs_player/skin/mediaelementplayer.css';
		}

		//$skin = FULLWEBPATH.'/'.USER_PLUGIN_FOLDER.'/mediaelementjs_player/mediaelementplayer.css';
		$features = mediaelementjs_player::getFeatureOptionsPlayer();
		if(getOption('mediaelementjs_showcontrols')) {
			$showcontrols = 'true';
		} else {
			$showcontrols = 'false';
		}
		?>

		<link href="<?php echo $skin; ?>" rel="stylesheet" type="text/css" />
		<script type="text/javascript" src="<?php echo FULLWEBPATH .'/'.USER_PLUGIN_FOLDER; ?>/mediaelementjs_player/mediaelement-and-player.min.js"></script>

		<script>
			$(document).ready(function(){
				$('audio.mep_player,video.mep_player').mediaelementplayer({
					alwaysShowControls: <?php echo $showcontrols; ?>,
					features: [<?php echo $features; ?>],
					// subtitles
					startLanguage:'<?php echo getOption("mediaelementjs_startlanguage"); ?>'
					//mode: 'shim' // force flash fallback
				});
				// Show poster image when ended
				$('video.mep_player').mediaelementplayer().bind('ended',function () {
					$(this).parents('.mejs-inner').find('.mejs-poster').show();
				});
			});
		</script>
		<?php
	}

	static function mediaelementjs_playlist_js() {
		$playlistfeatures = mediaelementjs_player::getFeatureOptionsPlaylist();
		if(getOption('mediaelementjs_showcontrols')) {
			$showcontrols = 'true';
		} else {
			$showcontrols = 'false';
		}
		if ( getOption('mediaelementjsplaylist_loop') ) {
			$loopplaylist = 'true';
		} else {
			$loopplaylist = 'false';
		}
		?>
		<link href="<?php echo FULLWEBPATH.'/'.USER_PLUGIN_FOLDER; ?>/mediaelementjs_player/mediaelement-playlist-plugin.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript" src="<?php echo FULLWEBPATH .'/'.USER_PLUGIN_FOLDER; ?>/mediaelementjs_player/mediaelement-playlist-plugin.js"></script>
		<script>
		$(document).ready(function(){
			$('audio.mep_playlist,video.mep_playlist').mediaelementplayer({
				alwaysShowControls: <?php echo $showcontrols; ?>,
				loopplaylist: <?php echo $loopplaylist; ?>,	// plays next track automatically and continues with the first track after the last has finished...and so on
				continuous: true, 		// this (undocumented) option is specifically for auto-next-track. Player stops after last track !!
										// not sure if both "loop" and "continuous" options should be used
				features: [<?php echo $playlistfeatures; ?>],
				audioVolume: 'vertical' // need to specify, otherwise won't show.
			});

			// Show playlist(overlay) on pause. Also prevents a continuous loading gif at the end.
			// May come in handy on small devices.
			$('video.mep_playlist').mediaelementplayer().bind('pause',function () {
				$(this).parents('.mejs-inner').find('.mejs-playlist').show();
			});
		});
		</script>
		<?php
	}

	/**
	 * Get the HTML configuration of Mediaelementjs_player
	 *
	 * @param mixed $movie the image object
	 * @param string $movietitle the title of the movie
	 * @param string $width the width of the movie.
	 *
	 */
	function getPlayerConfig($movie, $movietitle='', $width) {
    global $_zp_current_image;
    	$count = $movie->getID();
		$moviepath = $movie->getFullImage(FULLWEBPATH);
		if (is_null($movietitle)) {
			$movietitle = $movie->getTitle();
		}
		$ext = getSuffix($moviepath);
		if(!in_array($ext,array('m4a','m4v','mp3','mp4','flv'))) {
			echo '<p>'.gettext('This multimedia format is not supported by mediaelement.js.').'</p>';
			return NULL;
		}
		switch($ext) {
			case 'm4a':
			case 'mp3':
				$this->mode = 'audio';
			  break;
			case 'mp4':
			case 'm4v':
			case 'flv':
				$this->mode = 'video';
			  break;
		}

		if(getOption('mediaelementjs_preload')) {
			$preload = ' preload="metadata"';
		} else {
			$preload = ' preload="none"';
		}

		$playerconfig = '';
			$counterparts = $this->getCounterpartFiles($moviepath,$ext);
			switch($this->mode) {
				case 'audio':
					$style = '';
					$width = getOption('mediaelementjs_audiowidth');
					if ( empty($width) || !ctype_digit($width) ) {
						$width = '100%';
						$style = ' style="width:100%;"';
					}
					$height = 30; // Fixed height. Do not see the need for a "height" option
					if (getOption('mediaelementjs_audioposter')) {
						$posterwidth = getOption('mediaelementjs_audioposterwidth');
						$posterheight = getOption('mediaelementjs_audioposterheight');
						if ( empty($posterwidth) || empty($posterheight) ) {
          					$playerconfig .= '<img class="mediaelementjs_audioposter" src="' . $movie->getThumb() . '" alt="" width="" height="">' . "\n";
          				} else {
          					$playerconfig .= '<img class="mediaelementjs_audioposter" src="' . $movie->getCustomImage(NULL, $posterwidth, $posterheight, $posterwidth, $posterheight, NULL, NULL, true, NULL) . '" alt="" width="'.$posterwidth.'" height="'.$posterheight.'"style="max-width:'.$posterwidth.';height:auto;">' . "\n";
          				}
       			}
				$playerconfig .= '
					<audio id="mediaelementjsplayer_'.$count.'" class="mep_player" width="'.$width.'" height="'.$height.'" controls="controls"'.$preload.$style.'>
    				<source type="audio/mp3" src="'.pathurlencode($moviepath).'" />'."\n";
    			if(!empty($counterparts)) {
    				$playerconfig .= $counterparts;
    			}
    	  		$playerconfig  .= '
    				<object width="'.$width.'" height="'.$height.'" type="application/x-shockwave-flash" data="'.FULLWEBPATH.'/'.USER_PLUGIN_FOLDER.'/mediaelementjs_player/flashmediaelement.swf">
        			<param name="movie" value="'.FULLWEBPATH.'/'.USER_PLUGIN_FOLDER.'/mediaelementjs_player/flashmediaelement.swf" />
        			<param name="flashvars" value="controls=true&amp;file='.pathurlencode($moviepath).'" />
        			<p>'.gettext('Sorry, no playback capabilities.').'</p>
    				</object>
					</audio>
				';
				break;
			case 'video':
				// Gets the width, height and aspect ratio of the video using the getID3 library
				// NOT SURE AT ALL IF THIS APPROACH IS CORRECT !
				//$getID3 = new getID3;
				//$file = $getID3->analyze($movie->getFullImage(SERVERPATH));
// Apparently "VideoResolution_x" and "VideoResolution_y" (and "VideoAspect_ratio") are already stored in the DB
// No need to call the getID3 library again
				$vwidth = $movie->get('VideoResolution_x');
				$vheight = $movie->get('VideoResolution_y');
// For a number of entries the "VideoAspect_ratio" in the DB is either "1" or not calculated at all ("NULL")
// so we use "VideoResolution_x" and "VideoResolution_y" to calculate it here
				$ratio = round( ($vwidth / $vheight), 3 );
					if ( !empty($width) && ctype_digit($width) ) { // http://php.net/manual/en/function.ctype-digit.php
						$width = $width;
					} else {
						$width = getOption('mediaelementjs_videowidth');
					}
				$style = '';
					if ( empty($width) || !ctype_digit($width) ) {
						$width = $vwidth;
						$style = ' style="width:100%;height:100%;"';
					}
				$height = (int) ceil($width / $ratio);

				$poster = '';
				if(getOption('mediaelementjs_poster')) {
					//$posterwidth = getOption('mediaelementjs_videoposterwidth');
					//$posterheight = getOption('mediaelementjs_videoposterheight');
					//	if(empty($posterwidth)) {
					//		$posterwidth = $width;
					//	}
					//	if(empty($posterheight)) {
					//		$posterheight = $height;
					//	}

// User should take care of correct poster size to prevent too much distortion
					$poster = ' poster="' . $movie->getCustomImage(null, $width, $height, $width, $height, null, null, true) . '"';
				}
				if ( $ext == 'flv' ) {
					$type = 'video/x-flv';
				} else {
					$type = 'video/mp4';
				}
				$playerconfig  .= '
					<video id="mediaelementjsplayer_' . $count . '" class="mep_player" width="' . $width . '" height="' . $height . '" controls="controls"' . $preload . $poster . $style .'>
    				<source type="'.$type.'" src="'.pathurlencode($moviepath).'" />'."\n";
				if ( !empty($counterparts) ) {
					$playerconfig .= $counterparts;
				}
				if ( getOption('mediaelementjs_tracks') ) {
    				$tracks = $this->getTrackFiles($moviepath, $movie);
    				if ( !empty($tracks) ) {
						$playerconfig .= $tracks;
					}
    			}

				$playerconfig  .= '
    				<object width="'.$width.'" height="'.$height.'" type="application/x-shockwave-flash" data="'.FULLWEBPATH.'/'.USER_PLUGIN_FOLDER.'/mediaelementjs_player/flashmediaelement.swf">
        			<param name="movie" value="'.FULLWEBPATH.'/'.USER_PLUGIN_FOLDER.'/mediaelementjs_player/flashmediaelement.swf" />
        			<param name="flashvars" value="controls=true&amp;file='.pathurlencode($moviepath).'" />
        			<p>'.gettext('Sorry, no playback capabilities.').'</p>
    				</object>
					</video>
				';
				break;
		}
		return $playerconfig;
	}

	/**
	 * outputs the player configuration HTML
	 *
 	 * @param mixed $movie the image object if empty (within albums) the current image is used
	 * @param string $movietitle the title of the movie. if empty the Image Title is used
	 */
	function printPlayerConfig($movie = NULL, $movietitle = NULL) {
		global $_zp_current_image;
		if (empty($movie)) {
			$movie = $_zp_current_image;
		}
		echo $this->getPlayerConfig($movie,$movietitle);
	}

	/**
	 * Gets the counterpart formats (webm,ogg) for html5 browser compatibilty
	 * NOTE: These formats need to be uploaded via FTP as they are not valid file types for Zenphoto, to avoid confusion
	 *
	 * @param string $moviepath full link to the multimedia file to get counterpart formats from.
	 * @param string $ext the file format extention to search the counterpart for (as we already have fetched that)
	 */
	function getCounterpartFiles($moviepath,$ext) {
		$counterparts = '';
		switch($this->mode) {
			case 'audio':
				$suffixes = array('oga','webma');
				break;
			case 'video':
				$suffixes = array('ogv','webmv');
				break;
		}

		foreach($suffixes as $suffix) {
			$counterpart = str_replace(".".$ext, ".".$suffix, $moviepath); // in case the letters of the extension are also part of the filename (e.g. ogv_video.ogv)
			if(file_exists(str_replace(FULLWEBPATH,SERVERPATH,$counterpart))) {
				switch($suffix) {
					case 'oga':
						$type = 'audio/ogg';
						break;
					case 'ogv':
						$type = 'video/ogg';
						break;
					case 'webma':
						$type = 'audio/webm';
						break;
					case 'webmv':
						$type = 'video/webm';
						break;
				}
				$counterparts .= '<source type="'.$type.'" src="'.pathurlencode($counterpart).'" />'."\n";
			}
		}
		return $counterparts;
	}


// There must be a much better way to get the tracks !!!
	/**
	 * Gets the track files
	 * NOTE: These files need to be uploaded via FTP as they are not valid file types for Zenphoto.
	 *
	 * @param string $moviepath full path to the multimedia file to get the track files from.
	 * @param string $movie the multimedia file.
	 */
	function getTrackFiles($moviepath, $movie) {
		$tracks = '';
		$suffixes = array('srt','vtt');
		$mediafolder = str_replace(FULLWEBPATH,SERVERPATH,dirname($moviepath));
		$pattern = stripSuffix($movie->filename);

		foreach($suffixes as $suffix) {
			switch($suffix) {
				case 'srt':
					$files = safe_glob($mediafolder.'/'.$pattern.'*.srt');
					break;
				case 'vtt':
// Not sure if "vtt" should be implemented at all
					//$files = glob($mediafolder.'/*.vtt');
					break;
			}
		}
		foreach($files as $track) {
			if ( file_exists($track) ) {
				$track = str_replace(SERVERPATH, FULLWEBPATH, $track);
				if ( strpos($track,'subtitles') ) {
					$kind = 'subtitles';
					$trackname = stripSuffix($track);
					$language = substr($trackname, -2);
				}
				if ( strpos($track,'chapters') ) {
					$kind = 'chapters';
					$trackname = stripSuffix($track);
					$language = substr($trackname, -2);
				}

				$tracks .= '<track kind="'.$kind.'" src="'.pathurlencode($track).'" srclang="'.$language.'" />'."\n";
			}
		}
		return $tracks;
	}


// Don't understand why getWidth() and getHeight() functions can't be removed without a fatal error....
// Call to undefined method mediaelementjs_player::getWidth() in /Applications/MAMP/htdocs/zenphototest/zp-core/zp-extensions/class-video.php on line 139

	/**
	 * Returns the width of the player
	 * @param object $image the image for which the width is requested (not used)
	 *
	 * @return mixed
	 */
	function getWidth($image=NULL) {
		switch($this->mode) {
			case 'audio':
			//	$width = getOption('mediaelementjs_audiowidth');
			//	if(empty($width)) {
			//		return '100%';
			//	} else {
			//		return $width;
			//	}
				break;
			case 'video':
			//	$width = getOption('mediaelementjs_videowidth');
			//	if(empty($width)) {
			//		return '100%';
			//	} else {
			//		return $width;
			//	}
				break;
		}
	}

	/**
	 * Returns the height of the player
	 * @param object $image the image for which the height is requested (not used!)
	 *
	 * @return mixed
	 */
	function getHeight($image=NULL) {
		switch($this->mode) {
			case 'audio':
			//	$height = getOption('mediaelementjs_audioheight');
			//	if(empty($height)) {
			//		return '30';
			//	} else {
			//		return $height;
			//	}
			//	break;
			case 'video':
			//	$height = getOption('mediaelementjs_videoheight');
			//	if(empty($height)) {
			//		return 'auto';
			//	} else {
			//		return $height;
			//	}
			//	break;
		}
	}


// This function appears to be called twice for each macro when the html_meta_tags plugin is enabled.
	static function getMacroPlayer($albumname, $imagename, $width = NULL) {
		global $_zp_multimedia_extension;
		$movie = newImage(NULL, array('folder' => $albumname, 'filename' => $imagename), true);
		if ($movie->exists) {
			return $_zp_multimedia_extension->getPlayerConfig($movie, NULL, $width);
		} else {
			return '<span class="error">' . sprintf(gettext('%1$s::%2$s not found.'), $albumname, $imagename) . '</span>';
		}
	}

// Works on pages/news when called with "echo getPageContent()".
// Does NOT work on pages/news when called with "printPageContent()".
// Apparently "html_encodeTagged()" causes problems.
// TODO: A macro for playlists...no idea how...
	static function macro($macros) {
		$macros['MEDIAPLAYER'] = array(
						'class'	 => 'function',
						'params' => array('string','string','int*'),
						'value'	 => 'mediaelementjs_player::getMacroPlayer',
						'owner'	 => 'mediaelementjs_player',
						'desc'	 => gettext('provide the album name (%1), media file name (%2) and optional width (%3)')
		);
		return $macros;
	}

	/**
	 * Get the HTML configuration of Mediaelementjs playlist
	 * @param string $mode what kind of playlist. Set to either 'audio' or 'video'
	 * @param string $albumfolder the name of a specific album to get the mediafiles from
	 * @param string $count number of the item to append to the id for multiple playlists on one page
	 */
	function playlistPlayer($mode, $albumfolder='', $count='') {
		global $_zp_current_album;

		if(empty($albumfolder)) {
			$albumobj = $_zp_current_album;
		} else {
			$albumobj = newAlbum($albumfolder);
		}
		if(empty($count)) {
			$multiplayer = false;
			$count = '1';
		}	else {
			$multiplayer = true; // since we need extra JS if multiple players on one page
			$count = $count;
		}

		$playerconfig  = '';
		if(getOption('mediaelementjsplaylist_preload')) {
			$preload = ' preload="preload"';
		} else {
			$preload = ' preload="none"';
		}
		$counteradd = '';
		switch($mode) {
			case 'audio':
				$this->mode = 'audio'; // Must be set. Otherwise does not find counterparts.
				$width = getOption('mediaelementjs_audioplaylistwidth');
				$height = getOption('mediaelementjs_audioplaylistheight');
				if ( empty($width) || !ctype_digit($width) ) { // http://php.net/manual/en/function.ctype-digit.php
					$width = '100%';
				}
				if ( empty($height) || !ctype_digit($height) ) { // http://php.net/manual/en/function.ctype-digit.php
					$height = 300;
				}
				$playerconfig  = '
					<audio id="mediaelementjsplaylist_'.$count.'" class="mep_playlist" data-showplaylist="true" width="'.$width.'" height="'.$height.'" controls="controls"' . $preload .'>'."\n";
						$files = $albumobj->getImages(0);
						$counter = '';
						foreach($files as $file) {
							$ext = getSuffix($file);
							if(in_array($ext,array('m4a','mp3'))) {
								$counteradd = '';
								$counter++;
								if($counter < 10) $counteradd = '0';
								$obj = newImage($albumobj,$file);
// Any poster size will do for now. Gets scaled as a background image.
// Actually would like to get the provided full poster-image...don't know how...
								$poster = $obj->getCustomImage(null, 640, 360, 640, 360, null, null, true);
								$playerconfig  .= '<source type="audio/mpeg" src="'.pathurlencode($obj->getFullImageURL()).'" data-poster="'.$poster.'" title="'. $counteradd . $counter.'. ' . html_encode($obj->getTitle()).'" />'."\n";
								$counterparts = $this->getCounterpartFiles($obj->getFullImage(FULLWEBPATH), $ext);
								if ( $counterparts ) {
									$playerconfig  .= $counterparts;
								}
							}
						}
    	  		$playerconfig  .= '
					</audio>
				';
				break;
			case 'video':
				$this->mode = 'video'; // Must be set. Otherwise does not find counterparts.
				$width = getOption('mediaelementjs_videoplaylistwidth');
				$height = getOption('mediaelementjs_videoplaylistheight');
				if ( empty($width) || !ctype_digit($width) ) {
					$width = '100%';
				}
				if ( empty($height) || !ctype_digit($height) ) {
					$height = 360;
				}
				$backgroundposter = getOption('mediaelementjs_videoplaylistbackground');
				if ( !empty($backgroundposter) ) {
					$backgroundposter = ' poster="'.FULLWEBPATH.'/themes/'.getCurrentTheme().'/images/'.$backgroundposter.'"';
				} else {
					$backgroundposter = '';
				}
				$playerconfig  = '
					<video id="mediaelementjsplaylist_' . $count . '" class="mep_playlist" data-showplaylist="true" width="'.$width.'" height="'.$height.'" controls="controls"' . $preload . $backgroundposter .'>'."\n";
						$files = $albumobj->getImages(0);
						$counter = '';
						foreach($files as $file) {
							$ext = getSuffix($file);
							if(in_array($ext,array('m4v','mp4','flv'))) {
								$counteradd = '';
								$counter++;
								if ( $ext == 'flv' ) {
									$type = 'video/x-flv';
								} else {
									$type = 'video/mp4';
								}
								if($counter < 10) { $counteradd = '0'; }
								$obj = newImage($albumobj,$file);
								$poster = $obj->getCustomImage(null, 640, 360, 640, 360, null, null, true);
								$playerconfig  .= '<source type="'.$type.'" src="'.pathurlencode($obj->getFullImageURL()).'" data-poster="'.$poster.'" title="'. $counteradd . $counter.'. ' . html_encode($obj->getTitle()).'" />'."\n";
								$counterparts = $this->getCounterpartFiles($obj->getFullImage(FULLWEBPATH),$ext);
								if ( $counterparts ) {
									$playerconfig  .= $counterparts;
								}
							}
						}
    	  		$playerconfig  .= '
					</video>
				';
				break;
			}
			return $playerconfig;
		}


} // mediaelementjs class

$_zp_multimedia_extension = new mediaelementjs_player(); // claim to be the flash player.
zp_register_filter('theme_head','mediaelementjs_player::mediaelementjs_js');
if(getOption('mediaelementjs_playlist')) {
	zp_register_filter('theme_head','mediaelementjs_player::mediaelementjs_playlist_js');
}