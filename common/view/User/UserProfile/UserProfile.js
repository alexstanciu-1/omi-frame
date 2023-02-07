/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
QExtendClass("Omi\\View\\UserProfile", "QWebControl", {

});

jQuery(document).ready(function () {
	
});

function save()
{
	var jq = jQuery(this);
	var formJq = jq.closest("form");
	if (!formJq.length > 0)
		return;
	
	
	var newpwd = formJq.find("#newpass");
	if (newpwd.val())
	{
		var pwd = formJq.find("#currentpass");
		if (!pwd.val())
		{
			alert("Parola curenta este necesara pentru schimbarea parolei!");
			pwd[0].focus();
			return;
		}
		
		var newpwdconfirm = formJq.find("#newpassconfirm");
		if (!newpwdconfirm.val())
		{
			alert("Parola noua trebuie confirmata!");
			newpwdconfirm[0].focus();
			return;
		}
		else if (newpwd.val() !== newpwdconfirm.val())
		{
			alert("In campul confirma parola trebuie reintrodusa parola noua!");
			newpwdconfirm[0].focus();
			return;
		}
	}

	var cnpJq = formJq.find("#cnp");
	var cnp = (cnpJq.length > 0) ? jQuery.trim(cnpJq.val()) : null;
	var digitregx = /^\d+$/;
	if (!cnp || (!digitregx.test(cnp)) || (cnp.length !== 13))
	{
		alert(!cnp ? "CNP-ul este obligatoriu!" : "Formatul CNP-ului nu este corect!\nCNP-ul trebuie sa fie format din 13 caractere numerice!");
		cnpJq[0].focus();
		return false;
	}
	cnpJq.val(cnp);


	var icsJq = formJq.find("#identitycardseries");
	var ics = (icsJq.length > 0) ? jQuery.trim(icsJq.val()) : null;
	var hasDRegx = /\d$/;
	if (!ics || hasDRegx.test(ics) || (ics.length !== 2))
	{
		alert(!ics ? "Seria cartii de identiate este obligatorie!" : 
			"Formatul seriei cartii de identitate nu este corect!\nSeria cartii de identitate trebuie sa fie format din 2 caractere non-numerice!");
		icsJq[0].focus();
		return false;
	}
	icsJq.val(ics);


	var icnJq = formJq.find("#identitycardnumber");
	var icn = (icnJq.length > 0) ? jQuery.trim(icnJq.val()) : null;
	if (!icn || (!digitregx.test(icn)))
	{
		alert(!icn ? "Numarul cartii de identiate este obligatoriu!" : 
			"Numarul cartii de identitate trebuie sa fie numeric!");
		icnJq[0].focus();
		return false;
	}
	icnJq.val(icn);


	var cifJq = formJq.find("#cif");
	var cif = (cifJq.length > 0) ? jQuery.trim(cifJq.val()) : null;
	var cifEnt = cif;

	var hasro = false;
	if (cif && (cif.substr(0, 2).toUpperCase() === "RO"))
	{
		cif = cif.substr(2, cif.length);
		cif = cif.replace(/\s/, "");
		hasro = true;
	}

	if ((hasro && !cif) || (cif && (!digitregx.test(cif))))
	{
		alert("CIF-ul introdus nu este corect!\nCif-ul trebuie sa fie RO + numeric sau numeric\n");
		cifJq[0].focus();
		return false;
	}
	cifJq.val(cifEnt);


	var ibanJq = formJq.find("#iban");
	var iban = (ibanJq.length > 0) ? jQuery.trim(ibanJq.val()) : null;
	if (iban && (iban.length !== 24))
	{
		alert("IBAN-ul trebuie sa contina 24 de caractere!");
		ibanJq[0].focus();
		return false;
	}
	ibanJq.val(iban);

	// submit the form here!
	formJq[0].submit();
}