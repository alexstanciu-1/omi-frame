<div class="container" q-namespace="Omi\View" q-args="$user = null">
	<form method="POST">
		<?php 
		
		$requested = \QUrl::$Requested."";
		$err = ($_SESSION["__err__"] && $_SESSION["__err__"][$requested]) ? $_SESSION["__err__"][$requested] : null;
		if ($err)
		{
			?>
			<div class="row m-bottom-20 if-not-60-fill-all bg-white card-padding card-shadow">
				<div class="row if-not-60-fill-all f-size-14" style="color: red;"><span class="fa fa-warning p-right-10"></span><?= $err ?></div>
			</div>
			<?php 
			unset($_SESSION["__err__"][$requested]);
		}

		?>

		<div class="row m-bottom-20 if-not-60-fill-all bg-white card-padding card-shadow">
			<h4 class="mdt-c f-size-24">Schimbare parola</h4>
			<input type="hidden" name="Id" value="<?= $user ? $user->getId() : '' ?>" />
			<input type="hidden" name="PasswordRecoveryCode" value="<?= $user ? $user->PasswordRecoveryCode : '' ?>" />
			<input type="hidden" name="__submitted" value="1" />
			<hr />
			<div class="row if-not-60-fill-all">
				<div class="col-6-12">
					<div class="qc-table-responsive if-not-60-fill">
						<div class="table-row">
							<div class="table-cell fill nowrap">
								<label for="currentpass">Parola curenta</label>
							</div>
							<div class="table-cell cell-fill fill">
								<input type="password" class="full-width rounded-corners" id="currentpass" name="_Password" />
							</div>
						</div>
						<div class="table-row">
							<div class="table-cell fill nowrap">
								<label for="newpass">Parola noua</label>
							</div>
							<div class="table-cell cell-fill fill">
								<input type="password" class="full-width rounded-corners" id="newpass" name="_NewPassword" />
							</div>
						</div>
						<div class="table-row">
							<div class="table-cell fill nowrap">
								<label for="newpassconfirm">Confirma parola noua</label>
							</div>
							<div class="table-cell cell-fill fill">
								<input type="password" class="full-width rounded-corners" id="newpassconfirm" name="_NewPasswordConfirm" />
							</div>
						</div>

						<div class="table-row">
							<div class="table-cell fill nowrap"></div>
							<div class="table-cell cell-fill fill">
								<button class="c-white d-block" onclick="save.apply(this); return false;">Salveaza</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>