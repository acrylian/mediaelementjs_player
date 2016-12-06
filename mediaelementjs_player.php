<?php
/**
 * Support for the MediaElement.js video and audio player by John Dyer (http://mediaelementjs.com - license: MIT).
 * It will play natively via HTML5 in capable browsers and is responsive.
 *
 * Audio: <var>.mp3</var>, <var>.m4a</var> - Sidecar counterpart formats <var>.oga</var> and <var>.webma</var> supported (see note below!)<br>
 * Video: <var>.m4v</var>/<var>.mp4</var>, <var>.flv</var> - Sidecar counterpart formats <var>.ogv</var> and <var>.webmv</var> supported (see note below!)
 *
 * IMPORTANT NOTE ON OGG AND WEBM COUNTERPART FORMATS:<br>
 * The files need to have the same file name (beware of the character case!). In single player usage, the player
 * will check via the file system if a counterpart file exists and if counterpart support is enabled.
 * Firefox seems to prefer the <var>.oga</var> and <var>.ogv</var> while Chrome <var>.webma</var> and <var>.webmv</var>
 *
 * Since the flash fallback covers all essential formats this is not much of an issue for visitors though.
 *
 * If you have problems with any format being recognized, you may need to tell your server about the mime types first:
 * See examples on {@link http://mediaelementjs.com} under "installation".
 *
 * Subtitle and chapter support for videos.
 * It supports .srt files. These are sidecar files like the counterpart formats.
 *
 * For each language a separate file must be supplied and each filename must end with with a 2-letter language code.<br>
 * They must follow this naming convention:<br>
 * subtitles file: <nameofyourvideo>_subtitles-en.srt<br>
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
 * [MEDIAPLAYER album1 video.mp4 400] (400px wide) or<br> [MEDIAPLAYER album1 video.mp4] (responsive)
 *
 * <b>NOTE:</b> This player does not support external albums!
 *
 * Playlist (beta):
 * Basic playlist support (adapted from James McKay - https://github.com/portablejim/mediaelement-playlist-plugin):
 * Enable the option to load the playlist script support.<br>
 * Then call on your theme's album.php the function:<br>
 * printMediaelementjsPlaylist('video', '', '');<br>(video playlist using all available .mp4, .m4v, .flv files only)<br>
 * printMediaelementjsPlaylist('audio', '', '');<br>(audio playlist using all available .mp3, .m4a files only)
 *
 * Additionally you can set a specific albumname on the 2nd parameter to call a playlist outside of album.php
 *
 * Notes: Mixed audio and video playlists are not possible.
 *
 * @author Malte Müller (acrylian), Fred Sondaar (fretzl)
 * @copyright 2016
 * @license GPL v3 or later
 * @package plugins
 * @subpackage media
 */
$plugin_is_filter = 5 | CLASS_PLUGIN;
$plugin_description = gettext("Enable <strong>mediaelement.js</strong> to handle multimedia files.") . gettext("Please see <a href='http://http://mediaelementjs.com'>mediaelementjs.com</a> for more info about the player and its license.");
$plugin_notice = gettext("<strong>IMPORTANT</strong>: Only one multimedia extension plugin can be enabled at the time.");
$plugin_author = "Malte Müller (acrylian), Fred Sondaar (fretzl)";
$plugin_disable = (getOption('album_folder_class') === 'external')?gettext('This player does not support <em>External Albums</em>.'):false;
$option_interface = 'mediaelementjs_options';

if ($_zp_mediaplayer || $plugin_disable) {
	enableExtension('mediaelementjs_player', 0);
} else {
	$_zp_mediaplayer = 'mediaelementjs';
	Gallery::addImageHandlers(mediaelementjs::getSuffixes(), 'mediaelementjs');
	Gallery::addSidecarHandlers(mediaelementjs::getCounterpartSuffixes(), 'mediaelementjs');
	zp_register_filter('upload_filetypes', 'mediaelementjs::registerSidecarsUpload');

	zp_register_filter('theme_head', 'mediaelementjs::scripts');
	if (getOption('mediaelementjs_playlist')) {
		zp_register_filter('theme_head', 'mediaelementjs::playlistScripts');
	}
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
		setOptionDefault('mediaelementjs_playlist_showcontrols', 1);
		setOptionDefault('mediaelementjs_playlist_prevtrack', 1);
		setOptionDefault('mediaelementjs_playlist_playpause', 1);
		setOptionDefault('mediaelementjs_playlist_nexttrack', 1);
		setOptionDefault('mediaelementjs_playlist_loop', 0); // When enabled in the controls by the user it also functions as a sort of auto next track...
		setOptionDefault('mediaelementjs_playlist_shuffle', 1);
		setOptionDefault('mediaelementjs_playlist_progress', 1);
		setOptionDefault('mediaelementjs_playlist_current', 1);
		setOptionDefault('mediaelementjs_playlist_duration', 1);
		setOptionDefault('mediaelementjs_playlist_volume', 1);
		setOptionDefault('mediaelementjs_playlist_fullscreen', 1);
		setOptionDefault('mediaelementjs_videoplaylistbackground', '');
		setOptionDefault('mediaelementjs_playlist_preload', 1); // Otherwise Safari does not play well
	}

	function getOptionsSupported() {
		$skins = mediaelementjs::getSkins();
 		return array(
 			gettext('Player skin') => array('key'	=> 'mediaelementjs_skin', 'type' => OPTION_TYPE_SELECTOR,
 				'order' => 0,
				'selections' => $skins,
				'desc'	=> gettext("Select the skin (theme) to use. You can place custom skins within /plugins/mediaelementjs_player/skins/yourcustomskinfolder")),

 			array('type' => OPTION_TYPE_NOTE,
				'order' => 0.1,
				'desc' => gettext("<h2>Single audio and video options</h2>")),

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
					gettext('Subtitles (Video only)')=>'mediaelementjs_tracks',
					gettext('Volume')=>'mediaelementjs_volume',
					gettext('Fullscreen')=>'mediaelementjs_fullscreen',
					gettext('Always show controls')=>'mediaelementjs_showcontrols'
				),
				'desc' => gettext('Enable what should be shown in the player control bar.')),
			gettext('Video width') => array(
				'key' => 'mediaelementjs_videowidth', 'type' => OPTION_TYPE_TEXTBOX,
				'order'=>2,
				'desc' => gettext('Pixel value (px). Leave empty for responsive layouts')),
			gettext('Video Poster') => array(
				'key' => 'mediaelementjs_poster', 'type' => OPTION_TYPE_CHECKBOX,
				'order'=>4,
				'desc' => gettext('If a poster of the videothumb should be shown. This is cropped to fit the player size as the player would distort image not fitting the player dimensions otherwise.')),
			gettext('Audio width') => array(
				'key' => 'mediaelementjs_audiowidth', 'type' => OPTION_TYPE_TEXTBOX,
				'order'=>7,
				'desc' => gettext('Pixel value (px). Leave empty for responsive layouts (default)')),
      		gettext('Audio poster') => array(
				'key' => 'mediaelementjs_audioposter', 'type' => OPTION_TYPE_CHECKBOX,
				'order'=>9,
				'desc' => gettext('If an image of the videothumb should be shown with audio files. You need to set the width/height. This is cropped to fit the size.')),
      		gettext('Audio poster width') => array(
				'key' => 'mediaelementjs_audioposterwidth', 'type' => OPTION_TYPE_TEXTBOX,
				'order'=>10,
				'desc' => gettext('Max width of the audio poster (px). Image will be sized automatially in responsive layouts. May require theme CSS changes to work correctly.')),
			gettext('Audio poster height') => array(
				'key' => 'mediaelementjs_audioposterheight', 'type' => OPTION_TYPE_TEXTBOX,
				'order'=>11,
				'desc' => gettext('Height of the audio poster (px). Image will be sized automatially in responsive layouts. May require theme CSS changes to work correctly.')),
			gettext('Preload') => array(
				'key' => 'mediaelementjs_preload', 'type' => OPTION_TYPE_CHECKBOX,
				'order'=>12,
				'desc' => gettext('If the files should be preloaded (Note: if this works is browser dependent and may not work in all!).')),

			// Playlist
			array('type' => OPTION_TYPE_NOTE,
				'order' => 50,
				'desc' => gettext("<h2>Playlist options</h2>")),

			gettext('Playlist support') => array(
				'key' => 'mediaelementjs_playlist', 'type' => OPTION_TYPE_CHECKBOX,
				'order'=>51,
				'desc' => gettext('If enabled the script for playlist support is loaded. For playlists either use the macro or modify your theme.')),
			gettext('Playlist control bar') => array(
				'key' => 'mediaelementjs_playlist_controlbar',
				'type' => OPTION_TYPE_CHECKBOX_UL,
				'order' =>52,
				'checkboxes' => array( // The definition of the checkboxes
					gettext('Previous track')=>'mediaelementjs_playlist_prevtrack',
					gettext('Play/Pause')=>'mediaelementjs_playlist_playpause',
					gettext('Next track')=>'mediaelementjsp_laylist_nexttrack',
					gettext('Loop')=>'mediaelementjs_playlist_loop',
					gettext('Shuffle')=>'mediaelementjs_playlist_shuffle',
					gettext('Progress')=>'mediaelementjs_playlist_progress',
					gettext('Current')=>'mediaelementjs_playlist_current',
					gettext('Duration')=>'mediaelementjs_playlist_duration',
					gettext('Volume')=>'mediaelementjs_playlist_volume',
					gettext('Fullscreen')=>'mediaelementjs_playlist_fullscreen',
					gettext('Always show controls')=>'mediaelementjs_playlist_showcontrols'
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
				'key' => 'mediaelementjs_videoplaylistbackground', 'type' => OPTION_TYPE_GALLERY,
				'order' => 56.1,
				'desc' => gettext('Background image for the video playlist. Only shows at startup.')),
			gettext('Playlist preload') => array(
				'key' => 'mediaelementjs_playlist_preload', 'type' => OPTION_TYPE_CHECKBOX,
				'order'=>57,
				'desc' => gettext('If the files should be preloaded (Note: if this works is browser dependent and may not work in all!).')),

		);
	}
}

class mediaelementjs extends Video {

	public $mode = '';

	function __construct($album, $filename, $quiet = false) {
		global $_zp_supported_images;
		parent::__construct($album, $filename, $quiet);
		switch ($this->suffix) {
			case 'm4a':
			case 'mp3':
				$this->mode = 'audio';
				break;
			case 'mp4':
			case 'm4v':
			case 'flv':
			case 'yt':
				$this->mode = 'video';
				break;
		}
	}

	/**
	 * Gets the skin names and css files. The css file must be minified and named accordingly (*.min.css)
	 *
	 */
	static function getSkins() {
		$default_skins = array(
				'Mediaelement default skin' => SERVERPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/mediaelementjs_player/mediaelementplayer.min.css'
		);
		$user_skins_dir = SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/mediaelementjs_player/skins/';
		$filestoignore = array('.', '..', '.DS_Store', 'Thumbs.db', '.htaccess', '.svn');
		$skins2 = @array_diff(scandir($user_skins_dir), array_merge($filestoignore));
		if (is_array($skins2)) {
			$user_skins = self::getCSS($skins2, $user_skins_dir);
			//echo "<pre>";print_r($user_skins);echo "</pre>";
			$default_skins = array_merge($default_skins, $user_skins);
		}
		return $default_skins;
	}

	/**
	 * Gets the css files for a skin. Helper function for getSkins().
	 *
	 */
	static function getCSS($skins, $dir) {
		$skin_css = array();
		foreach ($skins as $skin) {
			$css = safe_glob($dir . $skin . '/*.css');
			if ($css) {
				$skin_css = array_merge($skin_css, array($skin => $css[0])); // a skin should only have one css file so we just use the first found
			}
		}
		return $skin_css;
	}
	/**
	 * Gets an array of suffixes of the supported format
	 * @param string $which Unused
	 * @return array
	 */
	static function getSuffixes($which = '') {
		return array('mp4', 'm4v', 'm4a', 'mp3', 'flv', 'yt');
	}
		/**
	 * Register suffixes for uploaders
	 *
	 * @param array $types array of suffixes
	 * @return array
	 */
	static function registerSidecarsUpload($types) {
		$types = array_merge($types, array('srt'));
		return $types;
	}

	/**
	 * Returns the image file name for the thumbnail image.
	 *
	 * @param string $path override path
	 *
	 * @return string
	 */
	function getThumbImageFile($path = NULL) {
		global $_zp_gallery;
		if (is_null($path)) {
			$path = SERVERPATH;
		}
		if (is_null($this->objectsThumb)) {
			switch ($this->suffix) {
				case "mp3":
					$img = 'mp3Default.png';
					break;
				case "m4a":
					$img = 'm4aDefault.png';
					break;
				case "mp4": // generic suffix for mp4 stuff - considered video
					$img = 'mp4Default.png';
					break;
				case "m4v": // specific suffix for mp4 video
				case "yt":
					$img = 'm4vDefault.png';
					break;
				case "flv":
					$img = 'flvDefault.png';
					break;
			}
			$imgfile = $path . '/' . THEMEFOLDER . '/' . internalToFilesystem($_zp_gallery->getCurrentTheme()) . '/images/' . $img;
			if (!file_exists($imgfile)) { // first check if the theme has a default image
				$imgfile = $path . "/" . USER_PLUGIN_FOLDER . '/mediaelementjs_player/' . $img;
				if (!file_exists($imgfile)) { // check user plugin folder, otherwise use core default
					$imgfile = $path . "/" . ZENFOLDER . '/' . PLUGIN_FOLDER . '/mediaelementjs_player/defaultimages/' . $img;
				}
			}
		} else {
			$imgfile = ALBUM_FOLDER_SERVERPATH . internalToFilesystem($this->imagefolder) . '/' . $this->objectsThumb;
		}
		return $imgfile;
	}

	/**
	 * Gets the controls for the player as set via options
	 * @param string $mode 'single' normal single player, 'playlist'
	 * @return string
	 */
	static function getPlayerControls($mode) {
		$array = array();
		switch ($mode) {
			case 'single':
				if (getOption('mediaelementjs_playpause')) {
					$array[] = 'playpause';
				}
				//if(getOption('mediaelementjs_loop')) $array[] = 'loop';
				if (getOption('mediaelementjs_progress')) {
					$array[] = 'progress';
				}
				if (getOption('mediaelementjs_current')) {
					$array[] = 'current';
				}
				if (getOption('mediaelementjs_duration')) {
					$array[] = 'duration';
				}
				if (getOption('mediaelementjs_tracks')) {
					$array[] = 'tracks';
				}
				if (getOption('mediaelementjs_volume')) {
					$array[] = 'volume';
				}
				if (getOption('mediaelementjs_fullscreen')) {
					$array[] = 'fullscreen';
				}
				break;
			case 'playlist':
				$array[] = 'playlistfeature'; //required
				if (getOption('mediaelementjs_playlist_prevtrack')) {
					$array[] = 'prevtrack';
				}
				if (getOption('mediaelementjs_playlist_playpause')) {
					$array[] = 'playpause';
				}
				if (getOption('mediaelementjs_playlist_nexttrack')) {
					$array[] = 'nexttrack';
				}
				if (getOption('mediaelementjs_playlist_loop')) {
					$array[] = 'loop';
				}
				if (getOption('mediaelementjs_playlist_shuffle')) {
					$array[] = 'shuffle';
				}
				$array[] = 'playlist'; // playlist icon
				if (getOption('mediaelementjs_playlist_progress')) {
					$array[] = 'progress';
				}
				if (getOption('mediaelementjs_playlist_current')) {
					$array[] = 'current';
				}
				if (getOption('mediaelementjs_playlist_duration')) {
					$array[] = 'duration';
				}
				if (getOption('mediaelementjs_playlist_volume')) {
					$array[] = 'volume';
				}
				if (getOption('mediaelementjs_playlist_fullscreen')) {
					$array[] = 'fullscreen';
				}
				break;
		}
		$count = '';
		$featurecount = count($array);
		$features = '';
		foreach ($array as $f) {
			$count++;
			$features .= "'" . $f . "'";
			if ($count != $featurecount) {
				$features .= ',';
			}
		}
		return $features;
	}

	/**
	 * mediaelememtjs scripts
	 */
	static function scripts() {
		$skin = getOption('mediaelementjs_skin');
		if (file_exists($skin)) {
			$skin = str_replace(SERVERPATH, FULLWEBPATH, $skin); //replace SERVERPATH as that does not work as a CSS link
		} else {
			$skin = FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/mediaelementjs_player/skin/mediaelementplayer/mediaelementplayer.min.css';
		}
		$features = mediaelementjs::getPlayerControls('single');
		if (getOption('mediaelementjs_showcontrols')) {
			$showcontrols = 'true';
		} else {
			$showcontrols = 'false';
		}
		?>
		<link href="<?php echo $skin; ?>" rel="stylesheet" type="text/css" />
		<script type="text/javascript" src="<?php echo FULLWEBPATH .'/'.ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/mediaelementjs_player/mediaelement-and-player.min.js"></script>
		<script>
			$(document).ready(function(){
				$('audio.mep_player,video.mep_player').mediaelementplayer({
					alwaysShowControls: <?php echo $showcontrols; ?>,
					features: [<?php echo $features; ?>],
					// subtitles
					<?php if(getOption('mediaelementjs_tracks')) {
						$locale = getUserLocale();
						$lang = substr(getHyphenLocale($locale), 0, 2);
						?>
					startLanguage: '<?php echo $lang; ?>'
					<?php } ?>
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
	/**
	 * mediaelememtjs scripts for playlist addon
	 */
	static function playlistScripts() {
		$playlistfeatures = mediaelementjs::getPlayerControls('playlist');
		if (getOption('mediaelementjs_playlist_showcontrols')) {
			$showcontrols = 'true';
		} else {
			$showcontrols = 'false';
		}
		if (getOption('mediaelementjs_playlist_loop')) {
			$loopplaylist = 'true';
		} else {
			$loopplaylist = 'false';
		}
		?>
		<link href="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/mediaelementjs_player/mediaelement-playlist-plugin.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript" src="<?php echo FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER; ?>/mediaelementjs_player/mediaelement-playlist-plugin.js"></script>
		<script>
		$(document).ready(function(){
			$('audio.mep_playlist,video.mep_playlist').mediaelementplayer({
				alwaysShowControls: <?php echo $showcontrols; ?>,
				loopplaylist: <?php echo $loopplaylist; ?>,	// plays next track automatically and continues with the first track after the last has finished...and so on
				continuous: true, 		// this (undocumented) option is specifically for auto-next-track. Player stops after last track !!
										// not sure if both "loop" and "continuous" options should be used
				features: [<?php echo $playlistfeatures; ?>],
				audioVolume: 'vertical', // need to specify, otherwise won't show.
				enableAutosize: true
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
	 */
	function getContent($width = NULL, $height = NULL) {
		global $_zp_current_image;
		$count = $this->getID();
		$moviepath = $this->getFullImage(FULLWEBPATH);
		$movietitle = $this->getTitle();
		$suffixes = self::getSuffixes();
		$type = getMimeString($this->suffix);
		if (!in_array($this->suffix, $suffixes)) {
			echo '<p>' . gettext('This multimedia format is not supported by mediaelement.js.') . '</p>';
			return NULL;
		}
		if (getOption('mediaelementjs_preload')) {
			$preload = ' preload="metadata"';
		} else {
			$preload = ' preload="none"';
		}
		$content = '';
		$counterparts = $this->getCounterpartCode();
		switch ($this->mode) {
			case 'audio':
				//$width = getOption('mediaelementjs_audiowidth');
				//if (empty($width) || !ctype_digit($width)) {
				//	$width = '100%';
				//}

				$style = '';
				if (!is_null($width) && ctype_digit($width)) { // http://php.net/manual/en/function.ctype-digit.php
					//$width = $width;
					$style = ' style="width:100%;max-width:' . $width . 'px;"';
				} else {
					if (is_null($width)) {
						$width = getOption('mediaelementjs_audiowidth');
						$style = ' style="width:100%;max-width:' . $width . 'px;"';
					}
				}

				if (empty($width) || !ctype_digit($width)) {
					$width = '100%';
					$style = ' style="width:100%;max-width:100%;"';
				}

				$height = 30; // Fixed height. Do not see the need for a "height" option

				$content .= '
				<div class="mediacontainer"' . $style . '>';

				if (getOption('mediaelementjs_audioposter')) {
					$posterwidth = getOption('mediaelementjs_audioposterwidth');
					$posterheight = getOption('mediaelementjs_audioposterheight');
					if (empty($posterwidth) || empty($posterheight)) {
						$content .= '<img class="mediaelementjs_audioposter" src="' . $this->getThumb() . '" alt="">' . "\n";
					} else {
						if (is_null($this->objectsThumb)) {
							$content .= '<img class="mediaelementjs_audioposter" src="' . $this->getCustomImage(250, null, null, null, null, null, null, true, null) . '" alt="" width="250" height="250" style="max-width: 100% ;height:auto;">' . "\n";
						} else {
							$content .= '<img class="mediaelementjs_audioposter" src="' . $this->getCustomImage(NULL, $posterwidth, $posterheight, $posterwidth, $posterheight, null, null, true, null) . '" alt="" width="' . $posterwidth . '" height="' . $posterheight . '"style="max-width: 100%; height:auto;">' . "\n";
						}
					}
				}
				$content .= '
					<audio id="mediaelementjsplayer_' . $count . '" class="mep_player" width="' . $width . '" height="' . $height . '" controls="controls"' . $preload . $style . '>
    				<source type="' . $type . '" src="' . pathurlencode($moviepath) . '" />' . "\n";
				if (!empty($counterparts)) {
					$content .= $counterparts;
				}
				$content .= '
    				<object width="' . $width . '" height="' . $height . '" type="application/x-shockwave-flash" data="' . FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/mediaelementjs_player/flashmediaelement.swf">
        			<param name="movie" value="' . FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/mediaelementjs_player/flashmediaelement.swf" />
        			<param name="flashvars" value="controls=true&amp;file=' . pathurlencode($moviepath) . '" />
        			<p>' . gettext('Sorry, no playback capabilities.') . '</p>
    				</object>
					</audio>
					</div>
				';
				break;
			case 'video':
				// Apparently "VideoResolution_x" and "VideoResolution_y" (and "VideoAspect_ratio") are already stored in the DB
				// No need to call the getID3 library again
				switch ($this->suffix) {
					case 'yt':
						$filecontent = file_get_contents($moviepath);
						$type = 'video/youtube';
						$moviepath = html_encode(trim($filecontent));
						$width = 1280;
						$height = 720;
						$style = ' style="max-width: 100%"';
						break;
					default:
						$vwidth = $this->get('VideoResolution_x');
						$vheight = $this->get('VideoResolution_y');

// For a number of entries the "VideoAspect_ratio" in the DB is either "1" or not calculated at all ("NULL")
// so we use "VideoResolution_x" and "VideoResolution_y" to calculate it here
						$ratio = 2;
						if ($vheight > 0) {
							$ratio = round(($vwidth / $vheight), 3);
						}

						$style = '';
						if (!is_null($width) && ctype_digit($width)) { // http://php.net/manual/en/function.ctype-digit.php
							//$width = $width;
							$style = ' style="max-width:' . $width . 'px;"';
						} else {
							if (is_null($width)) {
								$width = getOption('mediaelementjs_videowidth');
								$style = ' style="max-width:' . $width . 'px;"';
							}
						}

						if (empty($width) || !ctype_digit($width)) {
							$width = $vwidth;
							$style = ' style="max-width:100%;"';
						}

						if ($ratio > 0) {
							$height = (int) ceil($width / $ratio);
						}
						break;

				}
				$poster = '';
				if (getOption('mediaelementjs_poster')) {
					// User should take care of correct poster size to prevent too much distortion
					if (is_null($this->objectsThumb)) {
						$poster = ' poster="' . $this->getCustomImage(250, null, null, null, null, null, null, true, null) . '"';
					} else {
						$poster = ' poster="' . $this->getCustomImage(null, $width, $height, $width, $height, null, null, true, NULL) . '"';
					}
				}
				$content .= '
				<div class="mediacontainer"' . $style . '>
					<video id="mediaelementjsplayer_' . $count . '" class="mep_player" width="' . $width . '" height="' . $height . '" controls="controls"' . $preload . $poster . $style . '>
    				<source type="' . $type . '" src="' . pathurlencode($moviepath) . '" />' . "\n";
				if (!empty($counterparts)) {
					$content .= $counterparts;
				}
				if (getOption('mediaelementjs_tracks')) {
					$tracks = $this->getSubtitleCode();
					if (!empty($tracks)) {
						$content .= $tracks;
					}
				}
				$content .= '
    				<object width="' . $width . '" height="' . $height . '" type="application/x-shockwave-flash" data="' . FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/mediaelementjs_player/flashmediaelement.swf">
        			<param name="movie" value="' . FULLWEBPATH . '/' . ZENFOLDER . '/' . PLUGIN_FOLDER . '/mediaelementjs_player/flashmediaelement.swf" />
        			<param name="flashvars" value="controls=true&amp;file=' . pathurlencode($moviepath) . '" />
        			<p>' . gettext('Sorry, no playback capabilities.') . '</p>
    				</object>
					</video>
					</div>
				';
				break;
		}
		return $content;
	}

	/**
	 * Get the HTML configuration of Mediaelementjs playlist
	 * @param string $mode what kind of playlist. Set to either 'audio' or 'video'
	 * @param string $albumfolder the name of a specific album to get the mediafiles from
	 * @param string $count number of the item to append to the id for multiple playlists on one page
	 */
	static function playlistPlayer($mode, $albumfolder = '', $count = '') {
		global $_zp_current_album;

		if (empty($albumfolder)) {
			$albumobj = $_zp_current_album;
		} else {
			$albumobj = newAlbum($albumfolder);
		}
		if (empty($count)) {
			$multiplayer = false;
			$count = '1';
		} else {
			$multiplayer = true; // since we need extra JS if multiple players on one page
			$count = $count;
		}
		$content = '';
		if (getOption('mediaelementjs_playlist_preload')) {
			$preload = ' preload="preload"';
		} else {
			$preload = ' preload="none"';
		}
		$counteradd = '';
		switch ($mode) {
			case 'audio':
				$width = getOption('mediaelementjs_audioplaylistwidth');
				$height = getOption('mediaelementjs_audioplaylistheight');
				if (empty($width) || !ctype_digit($width)) { // http://php.net/manual/en/function.ctype-digit.php
					$width = '100%';
				}
				if (empty($height) || !ctype_digit($height)) { // http://php.net/manual/en/function.ctype-digit.php
					$height = 300;
				}
				$content = '
					<audio id="mediaelementjsplaylist_' . $count . '" class="mep_playlist" data-showplaylist="true" width="' . $width . '" height="' . $height . '" controls="controls"' . $preload . '>' . "\n";
				$files = $albumobj->getImages(0);
				$counter = '';
				foreach ($files as $file) {
					$ext = getSuffix($file);
					$type = getMimeString($ext);
					if (in_array($ext, array('m4a', 'mp3'))) {
						$counteradd = '';
						$counter++;
						if ($counter < 10)
							$counteradd = '0';
						$obj = newImage($albumobj, $file);
						// Any poster size will do for now. Gets scaled as a background image.
						// Actually would like to get the provided full poster-image...don't know how...
						$poster = $obj->getCustomImage(null, 640, 360, 640, 360, null, null, true);
						$content .= '<source type="' . $type . '" src="' . pathurlencode($obj->getFullImageURL()) . '" data-poster="' . $poster . '" title="' . $counteradd . $counter . '. ' . html_encode($obj->getTitle()) . '" />' . "\n";
						$counterparts = $obj->getCounterpartCode();
						if ($counterparts) {
							$content .= $counterparts;
						}
					}
				}
				$content .= '
					</audio>
				';
				break;
			case 'video':
				$width = getOption('mediaelementjs_videoplaylistwidth');
				$height = getOption('mediaelementjs_videoplaylistheight');
				if (empty($width) || !ctype_digit($width)) {
					$width = '100%';
				}
				if (empty($height) || !ctype_digit($height)) {
					$height = 360;
				}
				$backgroundposter = getOption('mediaelementjs_videoplaylistbackground');
				if ($backgroundposter) {
					$obj = getItembyID('images', $backgroundposter);
					if (is_object($obj) && $obj->loaded && (zp_loggedin(VIEW_UNPUBLISHED_RIGHTS) || $obj->getShow())) {
						$backgroundposter = ' poster="' . html_encode($obj->getSizedImage(640)) . '"'; // not sure which size we need here actually
					}
				}
				$content = '
					<video id="mediaelementjsplaylist_' . $count . '" class="mep_playlist" data-showplaylist="true" width="' . $width . '" height="' . $height . '" controls="controls"' . $preload . $backgroundposter . '>' . "\n";
				$files = $albumobj->getImages(0);
				$counter = '';
				foreach ($files as $file) {
					$ext = getSuffix($file);
					$type = getMimeString($ext);
					if (in_array($ext, array('m4v', 'mp4', 'flv'))) {
						$counteradd = '';
						$counter++;
						if ($counter < 10) {
							$counteradd = '0';
						}
						$obj = newImage($albumobj, $file);
						$poster = $obj->getCustomImage(null, 640, 360, 640, 360, null, null, true);
						$content .= '<source type="' . $type . '" src="' . pathurlencode($obj->getFullImageURL()) . '" data-poster="' . $poster . '" title="' . $counteradd . $counter . '. ' . html_encode($obj->getTitle()) . '" />' . "\n";
						$counterparts = $obj->getCounterpartCode();
						if ($counterparts) {
							$content .= $counterparts;
						}
					}
				}
				$content .= '
					</video>
				';
				break;
		}
		return $content;
	}

} // mediaelementjs class

/**
 * Theme function wrapper for user convenience of echo mediaelementjs::playlistPlayer()
 *
 * @param string $option 'audio' or 'video'
 * @param string $albumfolder album folder name to generate the playlist of
 */
function printMediaelementjsPlaylist($option, $albumfolder, $count) {
	echo mediaelementjs::playlistPlayer($option, $albumfolder, $count);
}