<?php
/**
 * Support for the MediaElement.js video and audio player by John Dyer (http://mediaelementjs.com). It will play natively via HTML5 in capable browsers and is responsive.
 * 
 * Audio: <var>.mp3</var>, <var>.m4a</var> - Counterpart formats <var>.oga</var> and <var>.webma</var> supported (see note below!)<br>
 * Video: <var>.m4v</var>/<var>.mp4</var>, <var>.flv</var> - Counterpart formats <var>.ogv</var> and <var>.webmv</var> supported (see note below!)
 *
 * IMPORTANT NOTE ON OGG AND WEBM COUNTERPART FORMATS:
 *
 * The counterpart formats are not valid formats for Zenphoto itself and not recognized as items as that would confuse the management.
 * Therefore these formats can be uploaded via FTP only.
 * The files needed to have the same file name (beware the character case!). In single player usage the player
 * will check via file system if a counterpart file exists if counterpart support is enabled.
 *
 * Since the flash fallback covers all essential formats ths is not much of an issue for visitors though.
 *
 * Subtitle and chapter support for videos (NOTE: NOT IMPLEMENTED YET!):
 * It supports .srt files. Like like the counterpart formats these MUST be uploaded via FTP! To differ what is what they must follow this naming convention:
 * subtitles file: <nameofyourvideo>_subtitles.srt
 * chapters file: <name of your video>_chapters.srt
 * 
 * Example: yourvideo.mp4 with yourvideo_subtitles.srt and yourvideo_chapters.srt
 *
 * CONTENT MACRO:<br>
 * Mediaelementjs attaches to the content_macro MEDIAPLAYER you can use within normal text of Zenpage pages or articles for example.
 *
 * Usage:
 * [MEDIAPLAYER <fullpath to your multimedia file> <number>]
 *
 * Example:
 * [MEDIAPLAYER http://yourdomain.com/albums/video/video.mp4]
 *
 * If you are using more than one player on a page you need to pass a 2nd parameter with for example an unique number:<br>
 * [MEDIAPLAYER http://yourdomain.com/albums/video/video1.mp4 1]<br>
 * [MEDIAPLAYER http://yourdomain.com/albums/video/video2.mp4 2]
 *
 * <b>NOTE:</b> This player does not support external albums! 
 *
 * Basic playlist support (adapted from Andrew Berezovsky – https://github.com/duozersk/mep-feature-playlist):
 * Enable the option to load the playlist script support. Then call on your theme's album.php the method $_zp_multimedia_extension->playlistPlayer();
 * echo $_zp_multimedia_extension->playlistPlayer('video','',''); //video playlist using all available .mp4,.m4v, .flv files only
 * echo $_zp_multimedia_extension->playlistPlayer('audio','',''); //audio playlist using all available .mp3,.m4a files only
 * Additionally you can set a specific albumname on the 2nd parameter to call a playlist outside of album.php 
 *
 * Notes: Mixed audio and video playlists are not possible. Counterpart formats are not supported. Also the next playlist item does not automatically play.
 *
 * @author Malte Müller (acrylian)
 * @package plugins
 * @subpackage media
 */
$plugin_is_filter = 5 | CLASS_PLUGIN;
$plugin_description = gettext("Enable <strong>mediaelement.js</strong> to handle multimedia files.");
$plugin_notice = gettext("<strong>IMPORTANT</strong>: Only one multimedia player plugin can be enabled at the time and the class-video plugin must be enabled, too.").'<br /><br />'.gettext("Please see <a href='http://http://mediaelementjs.com'>mediaelementjs.com</a> for more info about the player and its license.");
$plugin_author = "Malte Müller (acrylian)";
$plugin_disable = (getOption('album_folder_class') === 'external')?gettext('This player does not support <em>External Albums</em>.'):false;
$plugin_version = '1.0';
$option_interface = 'mediaelementjs_options';

if (!empty($_zp_multimedia_extension->name) || $plugin_disable) {
	setOption('zp_plugin_mediaelementjs_player',0);
	if (isset($_zp_multimedia_extension)) {
		trigger_error(sprintf(gettext('mediaelement.js not enabled, %s is already instantiated.'),get_class($_zp_flash_player)),E_USER_NOTICE);
	}
} else {
	addPluginType('flv', 'Video');
	addPluginType('mp3', 'Video');
	addPluginType('mp4', 'Video');
	addPluginType('m4v', 'Video');
	addPluginType('m4a', 'Video');
	zp_register_filter('content_macro', 'mediaelementjs_player::macro');
}

class mediaelementjs_options {

	function mediaelementjs_options() {
		setOptionDefault('mediaelementjs_playpause', 1);
		setOptionDefault('mediaelementjs_progress', 1);
		setOptionDefault('mediaelementjs_current', 1);
		setOptionDefault('mediaelementjs_duration', 1);
		setOptionDefault('mediaelementjs_tracks', 0);
		setOptionDefault('mediaelementjs_volume', 1);
		setOptionDefault('mediaelementjs_fullscreen', 1);
		setOptionDefault('mediaelementjs_videowidth', '100%');
		setOptionDefault('mediaelementjs_videoheight', 270);
		setOptionDefault('mediaelementjs_audiowidth', '100%');
		setOptionDefault('mediaelementjs_audioheight', 30);
		setOptionDefault('mediaelementjs_preload', 0);
		setOptionDefault('mediaelementjs_poster', 1);
		setOptionDefault('mediaelementjs_playlist', 0);
	}

	function getOptionsSupported() {
		//$skins = getMediaelementjsSkins();
 		return array(
 			gettext('Control bar') => array(
				'key' => 'mediaelementjs_controlbar',
				'type' => OPTION_TYPE_CHECKBOX_UL,
				'order' => 0,
				'checkboxes' => array( // The definition of the checkboxes
					gettext('Play/Pause')=>'mediaelementjs_playpause',
					gettext('Progress')=>'mediaelementjs_progress',
					gettext('Current')=>'mediaelementjs_current',
					gettext('Duration')=>'mediaelementjs_duration',
					gettext('Tracks (Video only)')=>'medialementjs_tracks',
					gettext('Volume')=>'mediaelementjs_volume',
					gettext('Fullscreen')=>'mediaelementjs_fullscreen'
				),
				'desc' => gettext('Enable what should be shown in the player control bar.')),
			gettext('Video width') => array(
				'key' => 'mediaelementjs_videowidth', 'type' => OPTION_TYPE_TEXTBOX,
				'order'=>5,
				'desc' => gettext('Pixel value or percent for responsive layouts')),
			gettext('Video height') => array(
				'key' => 'mediaelementjs_videoheight', 'type' => OPTION_TYPE_TEXTBOX,
				'order'=>5,
				'desc' => gettext('Pixel value or percent for responsive layouts')),
			gettext('Audio width') => array(
				'key' => 'mediaelementjs_audiowidth', 'type' => OPTION_TYPE_TEXTBOX,
				'order'=>5,
				'desc' => gettext('Pixel value or set 100% for responsive layouts (default).')),
			gettext('Audio height') => array(
				'key' => 'mediaelementjs_audioheight', 'type' => OPTION_TYPE_TEXTBOX,
				'order'=>5,
				'desc' => gettext('Pixel value or 100% for responsive layouts (default).')),
			gettext('Preload') => array(
				'key' => 'mediaelementjs_preload', 'type' => OPTION_TYPE_CHECKBOX,
				'order'=>5,
				'desc' => gettext('If the files should be preloaded.')),
			gettext('Poster') => array(
				'key' => 'mediaelementjs_poster', 'type' => OPTION_TYPE_CHECKBOX,
				'order'=>5,
				'desc' => gettext('If a poster of the videothumb should be shown. This is cropped to fit the player size (videos only).')),
			gettext('Playlist support') => array(
				'key' => 'mediaelementjs_playlist', 'type' => OPTION_TYPE_CHECKBOX,
				'order'=>5,
				'desc' => gettext('If enabled the script for playlist support is loaded. For playlists either use the macro or modify your theme.'))
		);
	}
}
/** NOT USED YET
 * Gets the skin names and css files
 *
 */
function getMediaelementjsSkins() {
	$all_skins = array();
	$default_skins_dir = FULLWEBPATH.'/'.USER_PLUGIN_FOLDER.'/mediaelementjs_player/';
	$user_skins_dir = FULLWEBPATH.'/'.USER_PLUGIN_FOLDER.'/mediaelementjs_player/';
	$filestoignore = array( '.', '..','.DS_Store','Thumbs.db','.htaccess','.svn');
	$skins = array_diff(scandir($default_skins_dir),array_merge($filestoignore));
	$default_skins = getMediaelementjsSkinCSS($skins,$default_skins_dir);
	//echo "<pre>";print_r($default_skins);echo "</pre>";
	$skins2 = @array_diff(scandir($user_skins_dir),array_merge($filestoignore));
	if(is_array($skins2)) {
		$user_skins = getMediaelementjsSkinCSS($skins2,$user_skins_dir);
		//echo "<pre>";print_r($user_skins);echo "</pre>";
		$default_skins = array_merge($default_skins,$user_skins);
	}
	return $default_skins;
}
/** NOT USED YET
 * Gets the css files for a skin. Helper function for getMediaelementjsSkins().  
 *
 */
function getMediaelementjsSkinCSS($skins,$dir) {
	$skin_css = array();
	foreach($skins as $skin) {
		$css = safe_glob($dir.'/'.$skin.'/*.css');
		if($css) {
			$skin_css = array_merge($skin_css,array($skin => $css[0]));	// a skin should only have one css file so we just use the first found
		}
	}
	return $skin_css;
}



class mediaelementjs_player {
	public $width = '';
	public $height = '';
	public $mode = '';
	
	function __construct() {
		
	}
	
	static function mediaelementjs_js() {
		/* 
		$skin = getOption('mediaelementjs_skin');
		if(file_exists($skin)) {
			$skin = str_replace(SERVERPATH,FULLWEBPATH,$skin); //replace SERVERPATH as that does not work as a CSS link
		} else {
			$skin = FULLWEBPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/mediaelementjs_player/mediaelementplayer.css';
		} 
		*/
		$skin = FULLWEBPATH.'/'.USER_PLUGIN_FOLDER.'/mediaelementjs_player/mediaelementplayer.css';
		?>
		<link href="<?php echo $skin; ?>" rel="stylesheet" type="text/css" />
		<script type="text/javascript" src="<?php echo FULLWEBPATH .'/'.USER_PLUGIN_FOLDER; ?>/mediaelementjs_player/mediaelement-and-player.min.js"></script>
		<script>
			$(document).ready(function(){
				$('audio.mep_player,video.mep_player').mediaelementplayer();
			});
		</script>
		<?php
	}

	static function mediaelementjs_playlist_js() {
		?>
		<link href="<?php echo FULLWEBPATH.'/'.USER_PLUGIN_FOLDER; ?>/mediaelementjs_player/mep-feature-playlist.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript" src="<?php echo FULLWEBPATH .'/'.USER_PLUGIN_FOLDER; ?>/mediaelementjs_player/mep-feature-playlist.js"></script>
		<script>
		$(document).ready(function(){
			$('audio.mep_playlist,video.mep_playlist').mediaelementplayer({
				loop: false, 
				shuffle: false,
				playlist: true,
				playlistposition: 'top',
				features: ['playlistfeature', 'prevtrack', 'playpause', 'nexttrack', 'loop', 'shuffle', 'playlist', 'current', 'progress', 'duration', 'volume'],
			});
		});
			</script>
		<?php
	}

	/**
	 * Get the JS configuration of jplayer
	 *
	 * @param string $moviepath the direct path of a movie
	 * @param string $imagefilename the filename of the movie
	 * @param string $count number (preferredly the id) of the item to append to the css for multiple players on one page
	 * @param string $width Not supported as jPlayer is dependend on its CSS based skin to change sizes. Can only be set via plugin options.
	 * @param string $height Not supported as jPlayer is dependend on its CSS based skin to change sizes. Can only be set via plugin options.
	 *
	 */
	function getPlayerConfig($moviepath, $imagefilename, $count='', $width='', $height='') {
		global $_zp_current_album, $_zp_current_image;
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
		if(empty($width)) {
			$this->width = $this->getWidth();
		} else {
			$this->width = $width;
		}
		if(empty($height)) {
			$this->height = $this->getHeight();
		} else {
			$this->height = $height;
		}
		if($this->width = '100%') {
			$style= ' style="max-width: 100%"';	
		} else {
			$style = '';
		}
		if(empty($count)) {
			$multiplayer = false;
			$count = '1';
		}	else {
			$multiplayer = true; // since we need extra JS if multiple players on one page
			$count = $count;
		}
		$playerconfig  = '';
		if(getOption('mediaelementjs_preload')) {
			$preload = ' preload="preload"';
		} else {
			$preload = ' preload="none"';
		}
		$counterparts = $this->getCounterpartFiles($moviepath,$ext);
		switch($this->mode) {
			case 'audio':
				$playerconfig  = '
					<audio id="mediaelementjsplayer'.$count.'" class="mep_player" width="'.$this->width.'" height="'.$this->height.'" controls="controls"'.$preload.$style.'>
    				<source type="audio/mp3" src="'.pathurlencode($moviepath).'" />';
    			if(count($counterparts) != 0) {
    				foreach($counterparts as $counterpart) {
    					$playerconfig .= $counterpart;
    				}
    			} 
    	  	$playerconfig  .= '		
    				<object width="'.$this->width.'" height="'.$this->height.'" type="application/x-shockwave-flash" data="'.FULLWEBPATH.'/'.USER_PLUGIN_FOLDER.'/mediaelementjs_player/flashmediaelement.swf">
        			<param name="movie" value="'.FULLWEBPATH.'/'.USER_PLUGIN_FOLDER.'/mediaelementjs_player/flashmediaelement.swf" />
        			<param name="flashvars" value="controls=true&file='.pathurlencode($moviepath).'" />
        			<p>'.gettext('Sorry, no playback capabilities.').'</p>
    				</object>
					</audio>
				'; 
				break;
			case 'video':
				$poster = '';
				if(getOption('mediaelementjs_poster')) {
					if(is_null($_zp_current_image)) {
						$poster = '';
					} else {
						$poster = ' poster="'.$_zp_current_image->getCustomImage(null, $this->width, $this->height, $this->width, $this->height, null, null, true).'"';
					}
				} 
				$playerconfig  = '
					<video id="mediaelementjsplayer'.$count.'" class="mep_player" width="'.$this->width.'" height="'.$this->height.'" controls="controls"'.$preload.$style.$poster.'>
    				<source type="video/mp4" src="'.pathurlencode($moviepath).'" />';
    		if(count($counterparts) != 0) {
    				foreach($counterparts as $counterpart) {
    					$playerconfig .= $counterpart;
    				}
    			}		
				$playerconfig  .= '		
    				<!-- <track kind="subtitles" src="subtitles.srt" srclang="en" /> -->
    				<!-- <track kind="chapters" src="chapters.srt" srclang="en" /> -->
    				<object width="'.$this->width.'" height="'.$this->height.'" type="application/x-shockwave-flash" data="'.FULLWEBPATH.'/'.USER_PLUGIN_FOLDER.'/mediaelementjs_player/flashmediaelement.swf">
        			<param name="movie" value="'.FULLWEBPATH.'/'.USER_PLUGIN_FOLDER.'/mediaelementjs_player/flashmediaelement.swf" />
        			<param name="flashvars" value="controls=true&file='.pathurlencode($moviepath).'" />
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
	 * @param string $moviepath the direct path of a movie (within the slideshow), if empty (within albums) the current image is used
	 * @param string $imagefilename the filename of the movie. if empty (within albums) the function getImageTitle() is used
	 * @param string $count unique text for when there are multiple player items on a page
	 */
	function printPlayerConfig($moviepath='',$imagefilename='',$count ='') {
		echo $this->getPlayerConfig($moviepath,$imagefilename,$count);
	}


	/** 
	 * Gets the counterpart formats (webm,ogg) for html5 browser compatibilty
	 * NOTE: THese formats need to be uploaded via FTP as they are not valid file types for Zenphoto to avoid confusion
	 *
	 * @param string $moviepath full link to the multimedia file to get counterpart formats to.
	 * @param string $ext the file format extention to search the counterpart for (as we already have fetched that)
	 */
	function getCounterpartFiles($moviepath,$ext) {
		$counterparts = array();
		switch($this->mode) {
			case 'audio':
				$suffixes = array('oga','webma');
				break;
			case 'video':
				$suffixes = array('ogv','webmv');
				break;
		}
		foreach($suffixes as $suffix) {
			$counterpart = str_replace($ext,$suffix,$moviepath,$count);
			if(file_exists(str_replace(FULLWEBPATH,SERVERPATH,$counterpart))) {
				switch($suffix) {
					case 'oga':
						$type = 'audio/ogg';
						break;
					case 'webma':
						$type = 'audio/webm';
						break;
					case 'ogv':
						$type = 'video/ogg';
						break;
					case 'webmv':
						$type = 'video/webm';
						break;
				}
				$source = '<source type="'.$type.'" src="'.pathurlencode($counterpart).'" />';
				array_push($counterparts,$source);
			}
		}
		return $counterparts;
	}
	
	/**
	 * Returns the height of the player
	 * @param object $image the image for which the width is requested (not used)
	 *
	 * @return mixed
	 */
	function getWidth($image=NULL) {
		switch($this->mode) {
			case 'audio':
				$width = getOption('mediaelementjs_audiowidth');
				if(empty($width)) {
					return '100%';
				} else {
					return $width;
				}
				break;
			case 'video': 
				$width = getOption('mediaelementjs_videowidth');
				if(empty($width)) {
					return '100%';
				} else {
					return $width;
				}
				break;
		}
	}

	/**
	 * Returns the width of the player
	 * @param object $image the image for which the height is requested (not used!)
	 *
	 * @return mixed
	 */
	function getHeight($image=NULL) {
		switch($this->mode) {
			case 'audio':
				$height = getOption('mediaelementjs_audioheight');
				if(empty($height)) {
					return '30';
				} else {
					return $height;
				}
				break;
			case 'video':
				$height = getOption('mediaelementjs_videoheight');
				if(empty($height)) {
					return 'auto';
				} else {
					return $height;
				}
				break;
		}
	}
	
	
	
	static function getMacroplayer($moviepath, $count = 1) {
		global $_zp_multimedia_extension;
		$moviepath = trim($moviepath, '\'"');
		$player = $_zp_multimedia_extension->getPlayerConfig($moviepath, '', (int) $count);
		return $player;
	}

	static function macro($macros) {
		$macros['MEDIAPLAYER'] = array(
						'class'	 => 'function',
						'params' => array('string', 'int*'),
						'value'	 => 'medialementjs_player::getMacroplayer',
						'owner'	 => 'medialementjs_player',
						'desc'	 => gettext('Provide the path to media file as %1 and a unique number as %2. (If there is only player instance on the page the parameter may be omitted.)')
		);
		return $macros;
	}
	/**
	 * Returns the width of the player
	 * @param object $image the image for which the height is requested (not used!)
	 *
	 * @return mixed
	 */
	function playlistPlayer($mode,$albumfolder='',$count='') {
		global $_zp_current_album;
		if(empty($count)) {
			$multiplayer = false;
			$count = '1';
		}	else {
			$multiplayer = true; // since we need extra JS if multiple players on one page
			$count = $count;
		}
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
		if(getOption('mediaelementjs_preload')) {
			$preload = ' preload="preload"';
		} else {
			$preload = ' preload="none"';
		}
		$counteradd = '';
		switch($mode) {
			case 'audio':
				$width = getOption('mediaelementjs_audiowidth');
				$height = 'auto';
				if($width = '100%') {
					$style= ' style="max-width: 100%;clear: both;"';	
				} else {
					$style = '';
				}
				$playerconfig  = '
					<audio id="mediaelementjsplayer'.$count.'" class="mep_playlist" width="'.$width.'" height="'.$height.'" controls="controls"'.$preload.$style.'>';
						$files = $albumobj->getImages(0);
						$counter = '';
						foreach($files as $file) {
							$ext = getSuffix($file);
							if(in_array($ext,array('m4a','mp3'))) {
								$counteradd = '';
								$counter++;
								if($counter < 10) $counteradd = '0';
								$obj = newImage($albumobj,$file);
								$playerconfig  .= '<source type="audio/mp3" src="'.pathurlencode($obj->getFullImageURL()).'" title="'.$counteradd.$counter.'. '.html_encode($obj->getTitle()).'" />';
								/*
								$counterparts = $this->getCounterpartFiles($moviepath,$ext);
								if(count($counterparts) != 0) {
    							foreach($counterparts as $counterpart) {
    								$playerconfig .= $counterpart;
    							}
    						} 
    						*/
							}
						}
    	  	$playerconfig  .= '		
					</audio>
				'; 
				break;
			case 'video':
				$width = getOption('mediaelementjs_videowidth');
				$height = getOption('mediaelementjs_videoheight');
				if($width = '100%') {
					$style= ' style="max-width: 100%;display:block;"';	
				} else {
					$style = '';
				}
				$playerconfig  = '
					<video id="mediaelementjsplayer'.$count.'" class="mep_playlist" width="'.$width.'" height="'.$height.'" controls="controls"'.$preload.$style.'>';
						$files = $albumobj->getImages(0);
						$counter = '';
						foreach($files as $file) {
							$ext = getSuffix($file);
							if(in_array($ext,array('m4v','mp4','flv'))) {
								$counteradd = '';
								$counter++;
								if($counter < 10) $counteradd = '0';
								$obj = newImage($albumobj,$file);
								$playerconfig  .= '<source type="video/mp4" src="'.pathurlencode($obj->getFullImageURL()).'" title="'.$counteradd.$counter.'. '.html_encode($obj->getTitle()).')" />';
								/*
								$counterparts = $this->getCounterpartFiles($moviepath,$ext);
								if(count($counterparts) != 0) {
    							foreach($counterparts as $counterpart) {
    								$playerconfig .= $counterpart;
    							}
    						} 
    						*/
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