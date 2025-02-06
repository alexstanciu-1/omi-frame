<?php if ($_is_bool) : 
    include(static::GetTemplate("form_elements/checkbox.tpl", $config));
elseif ($_is_date) : 
    include(static::GetTemplate("form_elements/date.tpl", $config));	
elseif ($_is_file) : 
	include(static::GetTemplate("form_elements/file.tpl", $config));	
elseif ($_is_enum) : 
	$wval_name = "_rdrpl_".($md5_seed__ ?: uniqid());
	if (((count($_enum_vals) > 2) && ((!$_enum_display) || ($_enum_display != 'radio'))) || ($_enum_display && ($_enum_display == "dropdown"))) : 
        include(static::GetTemplate("form_elements/custom_select.tpl", $config));
	else :
        include(static::GetTemplate("form_elements/radio_group.tpl", $config));
	endif; 
elseif ($_is_set) : 
	include(static::GetTemplate('form_elements/select_multiple.tpl', $config));
elseif ($_is_textarea) : 
	include(static::GetTemplate('form_elements/textarea.tpl', $config));
else : 
	include(static::GetTemplate("form_elements/text.tpl", $config));
endif;