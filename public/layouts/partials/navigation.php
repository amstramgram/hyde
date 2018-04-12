<nav class="navigation-wrapper">
	<ul class="menu">
		<?php foreach ($posts as $post): ?>
			<li class="menu-item <?php echo ( $post->slug == str_replace('/','',$_SERVER['REQUEST_URI']) )?'current-menu-item':'';?>">
				<a href="<?php echo $post->permalink;?>">
					<span class="meta-date"><?php echo $post->fullDate ;?></span>
					<span class="meta-title"><?php echo $post->title;?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>