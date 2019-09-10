<div id="clk_loader" class="loading style-2" style="display: none;"><div class="loading-wheel"></div></div>



<script type="text/javascript">
	$( document ).ready(function() {
		//listen to ajax start event
		$( document ).ajaxStart(function() {
		  $('#clk_loader').fadeIn();
          {if $preloader_debug == true}
		      console.log('Ajax Preloader Show');
          {/if}
		});

		//listen to ajax complete event
		$( document ).ajaxComplete(function() {
		  $('#clk_loader').fadeOut();
          {if $preloader_debug == true}
		      console.log('Ajax Preloader Hide');
		  {/if}
        });
	});
</script>


<style type="text/css">
	.loading {
    width: 100%;
    height: 100%;
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    background-color: {$preloader_bg_color};
    opacity: 0.5;

    z-index: 9999;
}
.loading-wheel {
    width: 20px;
    height: 20px;
    margin-top: -40px;
    margin-left: -40px;
    
    position: absolute;
    top: 50%;
    left: 50%;
    
    border-width: 30px;
    border-radius: 50%;
    -webkit-animation: spin 1s linear infinite;
}
.style-2 .loading-wheel {
    border-style: double;
    border-color: {$preloader_color} transparent;
}
@-webkit-keyframes spin {
    0% {
        -webkit-transform: rotate(0);
    }
    100% {
        -webkit-transform: rotate(-360deg);
    }
}
</style>