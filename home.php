<?php
session_start();
$urls=urldecode($_REQUEST['urls']);
$image_url=explode(",", $urls);
?>

<!DOCTYPE html>
<html lang="en">

	<head>
		<meta charset="utf-8">

		<title>Webcam-based gesture recognition with reveal.js</title>

		<meta name="description" content="Webcam-based gesture recognition with reveal.js">
		<meta name="author" content="William Wu (reveal.js by Hakim El Hattab)">

		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />

		<link rel="stylesheet" href="css/reveal.css">
		<link rel="stylesheet" href="css/theme/default.css" id="theme">

		<!-- For syntax highlighting -->
		<link rel="stylesheet" href="lib/css/zenburn.css">

		<!-- If the query includes 'print-pdf', use the PDF print sheet -->
		<script>
			document.write( '<link rel="stylesheet" href="css/print/' + ( window.location.search.match( /print-pdf/gi ) ? 'pdf' : 'paper' ) + '.css" type="text/css" media="print">' );
		</script>

		<!--[if lt IE 9]>
		<script src="lib/js/html5shiv.js"></script>
		<![endif]-->
	</head>

	<body>

		<div class="reveal">

			<!-- Any section element inside of this container is displayed as a slide -->
<div class="slides">

    <?php
    
for ($i=0;$i<count($image_url);++$i){

    echo "<section style='border:0px white solid;'><image style='width:100%;position:absolute;top:0%;left:0%;box-shadow:0px 0px 20px white;' src='".$image_url[$i]."' /></section>";
    }

    ?>

          
    </div>
		</div>
		<script src="lib/js/head.min.js"></script>
		<script src="js/reveal.min.js"></script>

		<script>

			// Full list of configuration options available here:
			// https://github.com/hakimel/reveal.js#configuration
			Reveal.initialize({
				controls: true,
				progress: true,
				history: true,

				theme: Reveal.getQueryHash().theme, // available themes are in /css/theme
				transition: Reveal.getQueryHash().transition || 'default', // default/cube/page/concave/zoom/linear/none

				// Optional libraries used to extend on reveal.js
				dependencies: [
					{ src: 'lib/js/highlight.js', async: true, callback: function() { window.hljs.initHighlightingOnLoad(); } },
					{ src: 'lib/js/classList.js', condition: function() { return !document.body.classList; } },
					{ src: 'lib/js/showdown.js', condition: function() { return !!document.querySelector( '[data-markdown]' ); } },
					{ src: 'lib/js/data-markdown.js', condition: function() { return !!document.querySelector( '[data-markdown]' ); } },
					{ src: 'plugin/zoom-js/zoom.js', async: true, condition: function() { return !!document.body.classList; } },
					{ src: 'plugin/notes/notes.js', async: true, condition: function() { return !!document.body.classList; } }
				]
			});
		</script>
		<style>
		#comp{
			position:fixed;
			left:0;
			top:0;
			width:100%;
			height:100%;
			opacity:0.1;
		}
		</style>
		<div id="disp"><canvas id="comp"></canvas></div>
		<video id="video" autoplay width="300" style="display:none"></video>
		<canvas id="canvas" style="width:300px;display:none;"></canvas>

		
		<script>

video=document.getElementById('video')
canvas=document.getElementById('canvas')
_=canvas.getContext('2d')
ccanvas=document.getElementById('comp')
c_=ccanvas.getContext('2d')
navigator.webkitGetUserMedia({audio:true,video:true},function(stream){
	s=stream
	video.src=window.webkitURL.createObjectURL(stream)
	video.addEventListener('play',
		function(){setInterval(dump,1000/25)}
	)
},function(){
	console.log('OOOOOOOH! DEEEEENIED!')
})
compression=5
width=height=0
function dump(){
	if(canvas.width!=video.videoWidth){
		width=Math.floor(video.videoWidth/compression)
		height=Math.floor(video.videoHeight/compression)
		canvas.width=ccanvas.width=width
		canvas.height=ccanvas.height=height
	}
	_.drawImage(video,width,0,-width,height)
	draw=_.getImageData(0,0,width,height)
	//c_.putImageData(draw,0,0)
	test()	
}
last=false
thresh=150
down=false
wasdown=false
function test(){
	delt=_.createImageData(width,height)
	if(last!==false){
		var totalx=0,totaly=0,totald=0,totaln=delt.width*delt.height
		,dscl=0
		,pix=totaln*4;while(pix-=4){
			var d=Math.abs(
				draw.data[pix]-last.data[pix]
			)+Math.abs(
				draw.data[pix+1]-last.data[pix+1]
			)+Math.abs(
				draw.data[pix+2]-last.data[pix+2]
			)
			if(d>thresh){
				delt.data[pix]=160
				delt.data[pix+1]=255
					delt.data[pix+2]=
				delt.data[pix+3]=255
				totald+=1
				totalx+=((pix/4)%width)
				totaly+=(Math.floor((pix/4)/delt.height))
			}
			else{
				delt.data[pix]=
					delt.data[pix+1]=
					delt.data[pix+2]=0
				delt.data[pix+3]=0
			}
		}
	}
	//slide.setAttribute('style','display:initial')
	//slide.value=(totalx/totald)/width
	if(totald){
		down={
			x:totalx/totald,
			y:totaly/totald,
			d:totald
		}
		handledown()
	}
	//console.log(totald)
	last=draw
	c_.putImageData(delt,0,0)
}
movethresh=2
brightthresh=300
overthresh=1000
function calibrate(){
	wasdown={
		x:down.x,
		y:down.y,
		d:down.d
	}
}

motion_flag=2
avg=0
state=0//States: 0 waiting for gesture, 1 waiting for next move after gesture, 2 waiting for gesture to end
function handledown(){
	 
	avg=0.9*avg+0.1*down.d
	var davg=down.d-avg,good=davg>brightthresh
	//console.log(davg)
	switch(state){
		case 0:
			if(good){//Found a gesture, waiting for next move
				
				state=1
				calibrate()
			}
			break
		case 2://Wait for gesture to end
			if(!good){//Gesture ended
				
				state=0
				
				
			}
			break;
		case 1://Got next move, do something based on direction

			var dx=down.x-wasdown.x,dy=down.y-wasdown.y
			var dirx=Math.abs(dy)<Math.abs(dx)//(dx,dy) is on a bowtie
			//console.log(good,davg)
            if(dx<-movethresh&&dirx){
                //console.log('right')
                
                	Reveal.navigateRight()
            
                
            	 }
            else if(dx>movethresh&&dirx){
                //console.log('left')
                Reveal.navigateLeft()
                
            }
            
            if(dy<-movethresh&&!dirx){
                if(davg<overthresh){
                	//console.log('down')
                     
                     motion_flag=1
                     
                window.location="http://localhost:3000"
                    
                    
                
                }
                
                
                
            }
            state=2
            break
    }
}






	</script>	
		
</body>
</html>
