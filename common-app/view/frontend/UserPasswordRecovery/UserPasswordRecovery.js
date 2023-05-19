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
	/*
	var pwd = formJq.find("#currentpass");
	if (!pwd.val())
	{
		alert(_T('5a2fbae07b613', 'Parola curenta este necesara pentru schimbarea parolei!'));
		pwd[0].focus();
		return;
	}
	*/

	var newpwdconfirm = formJq.find("#newpassconfirm");
	if (!newpwdconfirm.val())
	{
		alert(_T('5a2fbaf38da4b', 'Please confirm new password!'));
		newpwdconfirm[0].focus();
		return;
	}
	else if (newpwd.val() !== newpwdconfirm.val())
	{
		alert(_T('5a2fbb0315573', 'Confirm new password is different from new password!'));
		newpwdconfirm[0].focus();
		return;
	}
	// submit the form here!
	formJq[0].submit();
}