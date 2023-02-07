<div q-namespace="Omi\View" class="login-page" q-args="$email = null, $recoverEmailSent = false">
	<div class="container">
		<div class="row m-bottom-20 if-not-60-fill-all bg-white card-padding card-shadow">
			<h1 class="mdt-c f-size-24"><?= _L('recover_password') ?></h1>
			<hr />
			<div class="col-6-12">
				<form method="POST" autocomplete="false" class="qc-table-responsive form">
					<input type="hidden" name="__submitted" value="1" />
					<?php 
				
					$requested = \QUrl::$Requested."";
					$err = ($_SESSION["__err__"] && $_SESSION["__err__"][$requested]) ? $_SESSION["__err__"][$requested] : null;
					if ($err)
					{
						?>
							<div class="table-row">
								<div class="table-cell"></div>
								<div class="table-cell cell-fill">
									<div class="f-size-14" style="color: red;"><span class="fa fa-warning p-right-10"></span><?= $err ?></div>
								</div>
							</div>
						<?php 
						unset($_SESSION["__err__"][$requested]);
					}
					else if ($recoverEmailSent)
					{
						?>
						<div class="table-row">
								<div class="table-cell"></div>
								<div class="table-cell cell-fill">
									<div class="f-size-14">Un email a fost trimis la adresa de email <?= $email ?>.</div>
								</div>
							</div>
						<?php
					}
					?>

					<div class="table-row">
						<div class="table-cell">
							<i class="fa fa-envelope-o"></i>
						</div>
						<div class="table-cell cell-fill">
							<input type="text" class="full-width" placeholder="Email" name="email" />
						</div>
					</div>
					<div class="table-row">
						<div class="table-cell">&nbsp;</div>
						<div class="table-cell cell-fill">
							<button class="btn f-left"><?= _L('send') ?></button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
