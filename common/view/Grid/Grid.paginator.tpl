<div class="paginator" q-args="$countResults = null, $rowsOnPage = null, $selectedPage = null, $inSidePages = 4, $showFF = false">
	<?php	
	if (!$selectedPage)
		$selectedPage = 1;
	$selectedPage = intval($selectedPage);
	list ($pages, $lwPage, $upPage) = \Omi\View\Listing::InitPaginator($countResults, $rowsOnPage, $selectedPage, $inSidePages);
	$dataStart = ($rowsOnPage * ($selectedPage - 1));
	?>
	<div class="row m-bottom-20">
		<div class="col-6-12 text-left">Showing <?= $dataStart + 1 ?> to 
			<?= ((($rwson = $dataStart + $rowsOnPage)) > $countResults) ? $countResults : $rwson ?> of <?= $countResults ?> results</div>
		<div class="col-6-12">
			@if ($pages && ($pages > 1))
				<div class="js-paginator">
					<div class="js-paginator-data">
						<input type="hidden" class="js-start-limit" value="<?= $dataStart ?>" name="LIMIT[0]" />
						<input type="hidden" name="LIMIT[1]" value="<?= $rowsOnPage ?>" />
					</div>
					<ul class="qc-pagination f-right">
						@if ($showFF && (($pages > 1) && ($selectedPage > 1)))
							<li><a class="js-change-page pag-itm f-left" data-start="0"><i class="fa fa-angle-double-left"></i></a></li>
						@endif
						@if ($selectedPage > 1)
							<li>
								<a class="js-change-page pag-itm f-left" data-start="<?= ($rowsOnPage * ($selectedPage - 2)) ?>">
									<i class="fa fa-angle-left"></i>
								</a>
							</li>
						@endif
						@if ($lwPage > 0)
							<li><a class="f-left page-delimiter">...</a></li>
						@endif
						@for ($i = $lwPage; $i < $upPage; $i++)
							@var $page = $i + 1;
							@var $isSelected = $selectedPage ? ($selectedPage == $page) : ($page == 1);
							@var $endLimit = $rowsOnPage * $page;
							<li><a class="f-left pag-itm<?= $isSelected ? ' selected' : ' js-change-page' ?>" data-start="<?= ($rowsOnPage * $i) ?>">{{$page}}</a></li>
						@endfor
						@if ($upPage < $pages)
							<li><a class="page-delimiter f-left pag-itm">...</a></li>
						@endif
						@if ($selectedPage !== $pages)
							<li>
								<a class="js-change-page f-left pag-itm" data-start="<?= ($rowsOnPage * $selectedPage) ?>">
									<i class="fa fa-angle-right"></i>
								</a>
							</li>
						@endif
						@if ($showFF && ($selectedPage < $pages))
							<li>
								<a class="js-change-page f-left pag-itm" data-start="<?= ($rowsOnPage * ($pages - 1)) ?>">
									<i class="fa fa-angle-double-right"></i>
								</a>
							</li>
						@endif
					</ul>
				</div>
			@endif
		</div>
	</div>
</div>