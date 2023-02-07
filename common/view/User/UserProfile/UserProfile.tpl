<div q-namespace="Omi\View" q-args="$user = null">	
	<div class="p-20">
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
						</div>
					</div>
				</div>
			</div>
			<div class="row m-bottom-20 if-not-60-fill-all bg-white card-padding card-shadow">
				<h4 class="mdt-c f-size-24">Date generale</h4>		
				<hr />
				<div class="row if-not-60-fill-all">
					<input type="hidden" name="__submitted" value="1" />
					<input type="hidden" name="Id" value="<?= isset($user->Id) ? htmlentities($user->Id) : "" ?>" />
					<div class="col p-all-10">
						<div class="qc-table-responsive if-not-60-fill">
							<div class="table-row">
								<div class="table-cell fill">
									<label class="nowrap">Forma de adresare</label>
								</div>
								<div class="table-cell cell-fill fill">
									<div class="qc-radio pull-left">
										<input type="radio" name="Person[Gender]" value="<?= \Omi\Person::Male ?>" id="title_1" <?= (isset($user->Person) && isset($user->Person->Gender) && ($user->Person->Gender == \Omi\Person::Male)) ? "checked" : "" ?> />
										<label for="title_1">Dl.</label>
									</div>
									<div class="qc-radio pull-left">
										<input type="radio" name="Person[Gender]" id="title_2" value="<?= \Omi\Person::Female ?>" <?= (isset($user->Person) && isset($user->Person->Gender) && ($user->Person->Gender == \Omi\Person::Female)) ? "checked" : "" ?> />
										<label for="title_2">Dna.</label>
									</div>
									<br class="clearfix" />
								</div>
							</div>
						</div>
					</div>
					<div class="col-6-12 p-all-10 form">
						<div class="qc-table-responsive if-not-44-fill">
							<div class="table-row">
								<input type="hidden" name="Person[Id]" value="<?= (isset($user->Person) && isset($user->Person->Id)) ? htmlentities($user->Person->Id) : "" ?>" />
								<div class="table-cell fill">
									<label for="name">Nume</label>
								</div>
								<div class="table-cell cell-fill fill">
									<input type="text" class="full-width rounded-corners" id="name" name="Person[Name]" value="<?= (isset($user->Person) && isset($user->Person->Name)) ? htmlentities($user->Person->Name) : "" ?>" />
								</div>
							</div>
							<div class="table-row">
								<div class="table-cell fill">
									<label for="email">Email</label>
								</div>
								<div class="table-cell cell-fill fill">
									<input type="text" class="full-width rounded-corners" id="email" name="Email" value="<?= isset($user->Email) ? htmlentities($user->Email) : '' ?>" />
								</div>
							</div>
							<div class="table-row">
								<div class="table-cell fill">
									<label for="address">Adresa</label>
								</div>
								<div class="table-cell cell-fill fill">
									<input type="hidden" name="Person[Address][Id]" value="<?= (isset($user->Person) && isset($user->Person->Address) && isset($user->Person->Address->Id)) ? htmlentities($user->Person->Address->Id) : '' ?>" />
									<input type="text" class="full-width rounded-corners" name="Person[Address][Details]" value="<?= (isset($user->Person) && isset($user->Person->Address) && isset($user->Person->Address->Details)) ? htmlentities($user->Person->Address->Details) : '' ?>" id="address" />
								</div>
							</div>
							<!-- <div class="table-row">
								<div class="table-cell">
									<label>&nbsp;</label>
								</div>
								<div class="table-cell cell-fill">
									<button class="btn btn-accent">Schimb Parola</button>
								</div>
							</div> -->
						</div>
					</div>
					<div class="col-6-12 p-all-10 form">
						<div class="qc-table-responsive if-not-44-fill">
							<div class="table-row">
								<div class="table-cell fill">
									<label for="surname">Prenume</label>
								</div>
								<div class="table-cell cell-fill fill">
									<input type="text" class="full-width rounded-corners" id="surname" name="Person[Firstname]" value="<?= (isset($user->Person) && isset($user->Person->Firstname)) ? htmlentities($user->Person->Firstname) : "" ?>" />
								</div>
							</div>
							<div class="table-row">
								<div class="table-cell fill">
									<label for="phone">Telefon</label>
								</div>
								<div class="table-cell cell-fill fill">
									<input type="text" class="full-width rounded-corners" id="phone" name="Person[Phone]" value="<?= (isset($user->Person) && isset($user->Person->Phone)) ? htmlentities($user->Person->Phone) : "" ?>" />
								</div>
							</div>
							<div class="table-row">
								<div class="table-cell fill">
									<label class="nowrap">Zi de nastere</label>
								</div>
								<div class="table-cell cell-fill fill">
									@include(\Omi\View\Birthdate, "Person[BirthDate]", ($user->Person && $user->Person->BirthDate) ? $user->Person->BirthDate : null);
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row m-bottom-20 if-not-60-fill-all bg-white card-padding card-shadow">
				<h4 class="mdt-c f-size-24">Date facturare</h4>
				<h5>Persoana fizica</h5>
				<hr class="dashed" />
				<div class="col-6-12 p-all-10 form">
					<div class="qc-table-responsive if-not-60-fill">
						<div class="table-row">
							<div class="table-cell fill">
								<label for="cnp">CNP</label>
							</div>
							<div class="table-cell cell-fill fill">
								<input type="text" class="full-width rounded-corners" id="cnp" placeholder="1800000000000" name="Person[UniqueIdentifier]" value="<?= (isset($user->Person) && isset($user->Person->UniqueIdentifier)) ? htmlentities($user->Person->UniqueIdentifier) : '' ?>" />
							</div>
						</div>
						<div class="table-row">
							<div class="table-cell fill">
								<label class="nowrap">Carte de identitate</label>
							</div>
							<div class="table-cell cell-fill fill">
								<div class="col-4-12 p-right-10">
									<input type="text" id="identitycardseries" class="full-width rounded-corners" placeholder="SS" name="Person[IdentityCardSeries]" value="<?= (isset($user->Person) && isset($user->Person->IdentityCardSeries)) ? htmlentities($user->Person->IdentityCardSeries) : '' ?>" />
								</div>
								<div class="col-8-12">
									<input type="text" id="identitycardnumber" class="full-width rounded-corners" placeholder="123456" name="Person[IdentityCardNumber]" value="<?= (isset($user->Person) && isset($user->Person->IdentityCardNumber)) ? htmlentities($user->Person->IdentityCardNumber) : '' ?>" />
								</div>
								<div class="clearfix"><!-- --></div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-6-12 p-all-10 form">
					<div class="qc-table-responsive if-not-44-fill">
						<div class="table-row">
							<div class="table-cell fill">
								<label for="passport" class="nowrap">Seria Pasaport</label>
							</div>
							<div class="table-cell cell-fill fill">
								<input type="text" class="full-width rounded-corners" id="passport" name="Person[PassportSeries]" value="<?= (isset($user->Person) && isset($user->Person->PassportSeries)) ? htmlentities($user->Person->PassportSeries) : '' ?>" />
							</div>
						</div>
						<div class="table-row">
							<div class="table-cell fill">
								<label>Data Expirarii (pasaportului)</label>
							</div>
							<div class="table-cell cell-fill fill">
								@var $dateParams = ['minDate' => time()."000"];
								@var $date = (isset($user->Person) && isset($user->Person->PassportExpireDate)) ? $user->Person->PassportExpireDate : null;
								@include(\Omi\View\Date, "Person[PassportExpireDate]", $date, ($date ? date("d.m.Y", strtotime($date)) : null), $dateParams, ' js-passport-expire full-width rounded-corners');
							</div>
						</div>
					</div>
				</div>

				<h5>Persoana Juridica</h5>
				<hr class="dashed" />
				<div class="col-6-12 p-all-10 form">
					<input type="hidden" name="Person[Company][Id]" value="<?= (isset($user->Person) && isset($user->Person->Company) && isset($user->Person->Company->Id)) ? htmlentities($user->Person->Company->Id) : "" ?>" />
					<div class="qc-table-responsive if-not-44-fill">
						<div class="table-row">
							<div class="table-cell fill">
								<label for="denumire" class="nowrap">Denumire</label>
							</div>
							<div class="table-cell cell-fill fill">
								<input type="text" class="full-width rounded-corners" id="denumire" placeholder="SC ... SRL" name="Person[Company][Name]" value="<?= (isset($user->Person) && isset($user->Person->Company) && isset($user->Person->Company->Name)) ? htmlentities($user->Person->Company->Name) : '' ?>" />
							</div>
						</div>
						<div class="table-row">
							<div class="table-cell fill">
								<label for="nr_inregistrare" class="nowrap">Nr. Inregistrare</label>
							</div>
							<div class="table-cell cell-fill fill">
								@include(\Omi\View\RegistrationNumber, "Person[Company][RegistrationNo]", ($user->Person && $user->Person->Company && $user->Person->Company->RegistrationNo) ? $user->Person->Company->RegistrationNo : null);
							</div>
						</div>
						<div class="table-row">
							<div class="table-cell fill">
								<label for="cif">CIF</label>
							</div>
							<div class="table-cell cell-fill fill">
								<input type="text" class="full-width rounded-corners" id="cif" placeholder="RO...." name="Person[Company][TaxIdentificationNo]" value="<?= (isset($user->Person) && isset($user->Person->Company) && isset($user->Person->Company->TaxIdentificationNo)) ? htmlentities($user->Person->Company->TaxIdentificationNo) : '' ?>" />
							</div>
						</div>
					</div>
				</div>

				<div class="col-6-12 p-all-10 form">
					<div class="qc-table-responsive if-not-44-fill">
						<div class="table-row">
							<div class="table-cell fill">
								<label for="adresa_sediu" class="nowrap">Adresa Sediu</label>
							</div>
							<div class="table-cell cell-fill fill">
								<input type="hidden" name="Person[Company][HeadOffice][Id]" value="<?= (isset($user->Person) && isset($user->Person->Company) && isset($user->Person->Company->HeadOffice) && isset($user->Person->Company->HeadOffice->Id)) ? htmlentities($user->Person->Company->HeadOffice->Id) : '' ?>" />
								<input type="text" class="full-width rounded-corners" id="adresa_sediu" name="Person[Company][HeadOffice][Details]" 
									  value="<?= (isset($user->Person) && isset($user->Person->Company) && isset($user->Person->Company->HeadOffice) && isset($user->Person->Company->HeadOffice->Details)) ? htmlentities($user->Person->Company->HeadOffice->Details) : '' ?>" placeholder="str ..." />
							</div>
						</div>
						<div class="table-row">
							<div class="table-cell fill">
								<label for="banca">Banca</label>
							</div>
							<div class="table-cell cell-fill fill">
								<input type="text" class="full-width rounded-corners" id="banca" placeholder="banca" name="Person[Company][Bank]" value="<?= (isset($user->Person) && isset($user->Person->Company) && isset($user->Person->Company->Bank)) ? htmlentities($user->Person->Company->Bank) : '' ?>" />
							</div>
						</div>
						<div class="table-row">
							<div class="table-cell fill">
								<label for="iban" class="nowrap">IBAN</label>
							</div>
							<div class="table-cell cell-fill fill">
								<input type="text" class="full-width rounded-corners" id="iban" placeholder="cont" name="Person[Company][BankAccount]" value="<?= (isset($user->Person) && isset($user->Person->Company) && isset($user->Person->Company->BankAccount)) ? htmlentities($user->Person->Company->BankAccount) : '' ?>" />
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row m-bottom-20 if-not-60-fill-all bg-white card-padding card-shadow">
				<div class="col">
					<button class="c-white centered d-block" onclick="save.apply(this); return false;">Salveaza datele</button>
				</div>
			</div>
		</form>
	</div>
</div>