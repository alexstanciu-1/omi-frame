<?php if ($_is_bool) : 
    include(static::GetTemplate("form_elements/checkbox.tpl", $config));
elseif ($_is_date) : 
    include(static::GetTemplate("form_elements/date.tpl", $config));	
elseif ($_is_file) : 
	include(static::GetTemplate("form_elements/file.tpl", $config));	
elseif ($_is_enum) :
	if (((defined('Q_GEN_RADIO_UP_TO') && Q_GEN_RADIO_UP_TO && count($_enum_vals) > Q_GEN_RADIO_UP_TO) && ((!$_enum_display) || ($_enum_display != 'radio'))) || ($_enum_display && ($_enum_display == "dropdown"))) : 
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