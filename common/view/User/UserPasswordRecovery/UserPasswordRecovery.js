/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
QExtendClass("Omi\\View\\UserPasswordRecovery", "QWebControl", {

});

function save()
{
	var jq = jQuery(this);
	var formJq = jq.closest("form");
	if (!formJq.length > 0)
		return;

	var newpwd = formJq.find("#newpass");
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
	// submit the form here!
	formJq[0].submit();
}