<div q-namespace="Omi\View">
	<form method="POST" autocomplete="off">
		<input type="hidden" name="__submitted" value="1" />
		<div class="container bg-white card-shadow card-padding">
			<div class="row">
				<div class="col-6-12">
					<h1>Login</h1>
					<div class="qc-table-responsive form">
						<div class="table-row">
							<div class="table-cell nowrap fill">
								<label><?= _L('username') ?></label>
							</div>
							<div class="table-cell cell-fill fill">
								<input q-var="$user" class="full-width" type="text" name="user" value="<?= htmlentities($_POST["user"]) ?: "" ?>" />
							</div>
						</div>
						<div class="table-row">
							<div class="table-cell nowrap fill">
								<label><?= _L('password') ?></label>
							</div>
							<div class="table-cell cell-fill fill">
								<input q-var="$pass" class="full-width" type="password" name="pass" value="<?= htmlentities($_POST["pass"]) ?: "" ?>" />
							</div>
						</div>
						<div class="table-row">	
							<div class="table-cell nowrap fill"></div>
							<!-- login and refresh page if successful -->
							<div class="table-cell cell-fill fill">
								<button q-call="self::Login($user, $pass)">Login</button>
								<a class="f-right btn btn-accent" href="<?= $this->parentUrl->getUrlForTag("register") ?>"><?= _L('register') ?></a>
								<a class="" href="<?= $this->parentUrl->getUrlForTag("recover") ?>"><?= _L('forgot_password') ?></a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
