<div class="paginator" q-args="$countResults = null, $rowsOnPage = null, $selectedPage = null, $inSidePages = 4, $showFF = false">
	<?php
	
	if (!$selectedPage)
		$selectedPage = 1;
	$selectedPage = intval($selectedPage);

	list ($pages, $lwPage, $upPage) = self::InitPaginator($countResults, $rowsOnPage, $selectedPage, $inSidePages);
	?>

	@if ($pages && ($pages > 1))
		<div class="js-paginator">
			<div class="js-paginator-data">
				<input type="hidden" class="js-start-limit" value="<?= ($rowsOnPage * ($selectedPage - 1)) ?>" name="LIMIT[0]" />
				<input type="hidden" name="LIMIT[1]" value="<?= $rowsOnPage ?>" />
			</div>
			<div class="paginator-inner">
				@if ($showFF && (($pages > 1) && ($selectedPage > 1)))
					<a class="js-change-page pag-itm f-left" data-start="0">
						<i class="fa fa-angle-double-left"></i>
					</a>
				@endif
				@if ($selectedPage > 1)
					<a class="js-change-page pag-itm f-left" data-start="<?= ($rowsOnPage * ($selectedPage - 2)) ?>">
						<!-- <i class="fa fa-angle-left"></i> -->
						Inapoi
					</a>
				@endif
				@if ($lwPage > 0)
					<a class="f-left page-delimiter">...</a>
				@endif
				@for ($i = $lwPage; $i < $upPage; $i++)
					@var $page = $i + 1;
					@var $isSelected = $selectedPage ? ($selectedPage == $page) : ($page == 1);
					@var $endLimit = $rowsOnPage * $page;
					<a class="f-left pag-itm<?= $isSelected ? ' selected' : ' js-change-page' ?>" data-start="<?= ($rowsOnPage * $i) ?>">{{$page}}</a>
				@endfor
				@if ($upPage < $pages)
					<a class="page-delimiter f-left pag-itm">...</a>
				@endif
				@if ($selectedPage !== $pages)
					<a class="js-change-page f-left pag-itm" data-start="<?= ($rowsOnPage * $selectedPage) ?>">
						<!-- <i class="fa fa-angle-right"></i> -->
						Inainte
					</a>
				@endif
				@if ($showFF && ($selectedPage < $pages))
					<a class="js-change-page f-left pag-itm" data-start="<?= ($rowsOnPage * ($pages - 1)) ?>">
						<i class="fa fa-angle-double-right"></i>
					</a>
				@endif
				<div class="clear"></div>
			</div>
		</div>
	@endif
</div>