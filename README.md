mediaelementjs_player
=====================

A Zenphoto plugin for the MediaElement.js video and audio player by John Dyer (http://mediaelementjs.com). It will play natively via HTML5 in capable browsers and is responsive.

Supported file formats
----------------------
- Audio: <var>.mp3</var>, <var>.m4a</var> - Counterpart formats <var>.oga</var> and <var>.webma</var> supported (see note below!)<br>
- Video: <var>.m4v</var>/<var>.mp4</var>, <var>.flv</var> - Counterpart formats <var>.ogv</var> and <var>.webmv</var> supported (see note below!)
 
**IMPORTANT NOTE ON OGG AND WEBM COUNTERPART FORMATS:**

The counterpart formats are not valid formats for Zenphoto itself and not recognized as items as that would confuse the management.

Therefore these formats can be uploaded via FTP only.The files need to have the same file name (beware the character case!). In single player usage, the player will check via the file system if a counterpart file exists and if counterpart support is enabled. Firefox seems to prefer the <var>.oga</var> and <var>.ogv</var> while Chrome <var>.webma</var> and <var>.webmv</var>
 
Since the flash fallback covers all essential formats this is not much of an issue for visitors though.
  
If you have problems with any format being recognized, you might need to tell your server about the mime types first. See examples on http://mediaelementjs.com under "installation".
 
###Subtitle and chapter support for videos (NOTE: NOT IMPLEMENTED YET!):
It supports .srt files. Like the counterpart formats MUST be uploaded via FTP! They must follow this naming convention:
subtitles file: `<nameofyourvideo>_subtitles.srt`
chapters file: `<name of your video>_chapters.srt`
  
Example: `yourvideo.mp4` with `yourvideo_subtitles.srt` and `yourvideo_chapters.srt`

###Content Macro<br>
Mediaelementjs attaches to the content_macro MEDIAPLAYER you can use within normal text of Zenpage pages or articles for example.
 
```
[MEDIAPLAYER <albumname> <imagefilename> <number> <width> <height>]
```
 
Example:
```[MEDIAPLAYER album1 video.mp4 400 300]```
 
If you are using more than one player on a page you need to pass a 2nd parameter with for example an unique number:

```
[MEDIAPLAYER album1 video1.mp4 1]
[MEDIAPLAYER album2 video2.mp4 2]
```
 
*NOTE:This player does not support external albums!* 
 
###Playlist (beta):
Basic playlist support (adapted from Andrew Berezovsky – https://github.com/duozersk/mep-feature-playlist):
Enable the option to load the playlist script support. Then call on your theme's album.php the method 

```
$_zp_multimedia_extension->playlistPlayer();
echo $_zp_multimedia_extension->playlistPlayer('video','',''); //video playlist using all available .mp4, .m4v, .flv files only
echo $_zp_multimedia_extension->playlistPlayer('audio','',''); //audio playlist using all available .mp3, .m4a files only
```

Additionally you can set a specific albumname on the 2nd parameter to call a playlist outside of album.php 
 
*Notes*: Mixed audio and video playlists are not possible. Counterpart formats are also not supported. Also the next playlist item does not automatically play.
