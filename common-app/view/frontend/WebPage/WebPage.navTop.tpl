@php $isHolder = \QApi::Call("IsHolder");
@php $top_Holder = \QApi::Call("GetHolder");
@php $controller = \QApp::$UrlController;
@php $user = \QApi::Call('\Omi\User::GetCurrentUser');
@php $is_customer_login = ($user->Customer);

<nav q-args="$userData = null, $branding = null" id="nav-menu" class="top-menu qc-navbar menu-model-1 display-block">
	<?php
		$owner = $userData ? \QApi::Call('GetCurrentOwner') : null;
		$logo = ($branding && ($lfp = $branding->getFullPath("Logo")) && file_exists($lfp) && is_file($lfp)) ? $lfp : null;
		$ui_at_top_level = ($top_Holder->Id == $owner->Id);
		if (!$logo && Default_Logo && file_exists(Default_Logo))
			$logo = Default_Logo;
	?>
	<div class="navbar-section expand">
		
		<div class="qc-navbar _bb1 _bgwhite">
			<div class="navbar-section stretch">
				@if ($logo)
					<a href="" class="navbar-brand">
						<img style="max-width: 12rem;" src='<?= $logo ?>' r-src="<?= $logo ?>" />
					</a>
				@else
					<a href="" class="navbar-brand _twhite">
						<img src="<?= Q_APP_REL . 'code/res/main/images/logo.png' ?>" />
					</a>
				@endif
			</div>
			<div class="navbar-section stretch end">
				<ul class="qc-menu top-right-menu">
					<li>
						<a href="javascript: void(0);"><!-- <span style="font-weight: 400;"><?= _T('5a280aea42ad2', 'Welcome'); ?></span> -->{{$user->Person->Firstname}} {{$user->Person->Name}}</a>
					</li>
					<li>
						<a class="logout-btn" href="<?= $controller->getUrlForTag('logout') ?>">
							<!-- <?= _T('5a280aea42ad2', 'Log Out'); ?> -->
							<i class="zmdi zmdi-power"></i>
						</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
</nav>
