<?php if($content):?>
	<div class="container post">
		<?php echo $content;?>
	</div>
<?php endif;?>
<div class="grid">
	<?php foreach($images as $image): ?>
		<div class="item image">
			<div class="content"><img src="<?php echo $image->sizes->thumbnail;?>" alt="<?php echo $image->name;?>" data-full="<?php echo $image->url;?>"></div>
		</div>
	<?php endforeach;?>
</div>
