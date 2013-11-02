<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title></title>
		<link href='http://fonts.googleapis.com/css?family=VT323' rel='stylesheet' type='text/css'>
		<!-- cursor blink from https://gist.github.com/2902229 -->
		<style>
			* { font-size: 36px; margin: 0; padding: 0; line-height: 36px; text-transform: uppercase;font-family: "VT323", "Courier New", Courier, mono; font-weight: 700;}
			html { height: 100%; width: 100%; display: table; }
			body { display: table-row; background-color: #3D2FCB; color: #9181FB; }
			#wrapper { display: table-cell; border: 50px solid #9181FB; padding: 4px; }
			b { display: block; text-align: center; font-weight: normal; }
			div.cursor { display: inline-block; background: #3D2FCB;
			-webkit-animation: blink 2s linear 0s infinite;
			-moz-animation: blink 2s linear 0s infinite;
			-ms-animation: blink 2s linear 0s infinite;
			-o-animation: blink 2s linear 0s infinite;
			}
			@-webkit-keyframes blink {
			0%   { background: #9181FB }
			50%   { background: #9181FB }
			51%   { background: #3D2FCB }
			100% { background: #3D2FCB }
			}
			@-moz-keyframes blink {
			0%   { background: #9181FB }
			50%   { background: #9181FB }
			51%   { background: #3D2FCB }
			100% { background: #3D2FCB }
			}
			@-ms-keyframes blink {
			0%   { background: #9181FB }
			50%   { background: #9181FB }
			51%   { background: #3D2FCB }
			100% { background: #3D2FCB }
			}
			@-o-keyframes blink {
			0%   { background: #9181FB }
			50%   { background: #9181FB }
			51%   { background: #3D2FCB }
			100% { background: #3D2FCB }
			}
		</style>
	</head>
	<body>
		<div id="wrapper">
			<b>**** Super Small MVC on v<?=phpversion() ?> ****</b>
			<b>memory <?=floor(memory_get_peak_usage()/1024) ?>K of <?=ini_get('memory_limit') ?> used</b>
			<p>&nbsp;</p>
			<p>ready.</p>
			<p>load "*",8,1</p>
			<P>run</P>
			<p>?syntax error</p>
			<p><?=$error ?></p>
			<p>ready.</p>
			<div class="cursor">&nbsp;</div>
		</div>
	</body>
</html>