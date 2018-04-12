		</div><!-- ./main-container -->
	</div><!-- ./layout-container -->
	<?php if(isset($images)):?>
		<script type="application/json" id="caroubou-datas">
			<?php echo json_encode($images); ?>
		</script>
	<?php endif;?>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<script type="text/javascript" src="<?php echo $baseUrl; ?>/assets/javascripts/caroubou.js"></script>
	<script type="text/javascript" src="<?php echo $baseUrl; ?>/assets/javascripts/master.js"></script>
</body>
</html>