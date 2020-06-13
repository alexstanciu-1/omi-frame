<div class="mainwrap">
	<div class="logo"><a href=''><img src="https://www.omiframe.com/i/logo.png"/></a></div>
	<div class="menu_wraper">
			<div class="second-menu-page">
				<a href="<?= $this->url("classes") ?>">Classes</a>
				<a href="<?= $this->url("data-model") ?>">Data Model</a>
				<!-- <a href="<?= $this->url("admin") ?>">Admin</a> -->
				<!-- <a href="<?= $this->url("generate-binds") ?>">Generate Binds</a> -->
				<!-- <a href="<?= $this->url("api-gen") ?>">APIs</a> -->
				<a href="<?= $this->url("security_model") ?>">Security Model</a>
				<a href="<?= $this->url("security_type") ?>">Security Type</a>
				<!-- <a href="<?= $this->url("url-controller") ?>">URL Controllers</a> -->
				<!-- <a href="<?= $this->url("generated") ?>">Generated</a> -->
			</div>
			<div style="clear: both;"><!-- --></div>
	</div>
 </div>
<div class="header-strip">
	<div class="left"><!-- --></div>
	<div class="right"><h2><?= $this->heading ?></h2></div>
</div>
<div style="clear: both;"><!-- --></div>
<div class="mainwrap">
	<div style="clear: both;"><!-- --></div>
	<?php
	
	if ($this->content)
		$this->content->render();
	
	?>
</div>

<div style="clear: both;"><!-- --></div>
<p class="copyright">A solution by <a href="http://www.omibit.com/">Omibit LLC</a></p>