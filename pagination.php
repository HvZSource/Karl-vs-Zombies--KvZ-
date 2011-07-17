<div class="pagination">
<?php if($page > 0): ?>
	<?php print $pages; ?> Pages 
<?php endif; ?>

<?php if($page > 0): ?>

	<?php if($page > 1): ?>
	    <a href="javascript:goTo(1);">first</a>
		<a href="javascript:goTo(<?php print ($page - 1); ?>);"><</a>
	<?php endif; ?>
	
	<?php
	$prev_start = (($page - 2) >= 1) ? $page - 2 : 1;
	?>
	<?php for($i = $prev_start; $i < $page; $i++): ?>
		<a href="javascript:goTo(<?php print $i; ?>);"><?php print $i; ?></a>
	<?php endfor; ?>
	
	<strong class="current"><?php print $page; ?></strong>
	
	<?php
	$next_start = (($page + 2) <= $pages) ? $page + 2 : $pages;
	?>
	<?php for($i = ($page + 1); $i <= $next_start; $i++): ?>
		<a href="javascript:goTo(<?php print $i; ?>);"><?php print $i; ?></a> 
	<?php endfor; ?>
	
	<?php if($page < $pages): ?>
		<a href="javascript:goTo(<?php print ($page + 1); ?>);">></a>
		<a href="javascript:goTo(<?php print $pages; ?>);">last</a>
	<?php endif; ?>

<?php endif; ?>

<?php if($page > 0): ?>
	 <strong><?php print $count; ?></strong> Players
<?php endif; ?>

</div>
