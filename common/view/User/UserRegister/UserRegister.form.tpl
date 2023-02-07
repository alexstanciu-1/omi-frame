<div q-namespace="Omi\View" class="login-page">
	<div class="container">
		<div class="row if-not-60-fill-all bg-white card-shadow p-all-10 m-bottom-20">
			<div class="col-6-12">
				<h1><?= _L('register') ?></h1>
				<form class="qc-table-responsive form" method="POST">
					<input type="hidden" name="__submitted" value="1" />
					<div class="table-row">
						<div class="table-cell">
							<i class="fa fa-user"></i>
						</div>
						<div class="table-cell cell-fill">
							<input type="text" class="full-width" placeholder="<?= _L('username') ?>" name="Username" />
						</div>
					</div>
					<div class="table-row">
						<div class="table-cell">
							<i class="fa fa-envelope-o"></i>
						</div>
						<div class="table-cell cell-fill">
							<input type="text" class="full-width" placeholder="Email" name="Email" />
						</div>
					</div>
					<div class="table-row">
						<div class="table-cell">
							<i class="fa fa-lock"></i>
						</div>
						<div class="table-cell cell-fill">
							<input type="password" class="full-width" placeholder="<?= _L('password') ?>" name="Password" />
						</div>
					</div>
					<div class="table-row">
						<div class="table-cell">
							<i class="fa fa-lock"></i>
						</div>
						<div class="table-cell cell-fill">
							<input type="password" class="full-width" placeholder="<?= _L('confirm_password') ?>" />
						</div>
					</div>
					<div class="table-row">
						<div class="table-cell">&nbsp;</div>
						<div class="table-cell cell-fill">
							<button class="btn f-left"><?= _L('create') ?></button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>	
</div>
