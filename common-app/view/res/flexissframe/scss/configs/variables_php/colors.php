<?php

function outputCssColorsVariables()
{
	list($whiteColor, $blackColor, $primaryColor, $secondaryColor, $infoColor, $successColor, $warningColor, $errorColor) = func_get_args();
	if (!$whiteColor)
		$whiteColor = '#FFFFFF';
	
	if (!$blackColor)
		$blackColor = '#212121';
	
	if (!$infoColor)
		$infoColor = '#1E9FF2';
	
	if (!$successColor)
		$successColor = '#28D093';
	
	if (!$warningColor)
		$warningColor = '#FEC069';
	
	if (!$errorColor)
		$errorColor = '#FF4960';
	?>
// Black and White
$white_color: <?= $whiteColor ?>;
$black_color: <?= $blackColor ?>;

// Grey Colors
$dark_grey_color: #6A6F82;
$grey_color: #A6ABBE;
$light_grey_color: #E5E5ED;

// Main colors
// PRIMARY
$primary_color: <?= $primaryColor ?>;
$light_primary_color: lighten($primary_color, 5%);
$dark_primary_color: darken($primary_color, 5%);

// SECONDARY
$secondary_color: <?= $secondaryColor ?>;
$light_secondary_color: lighten($secondary_color, 5%);
$dark_secondary_color: darken($secondary_color, 5%);

// Status Colors
// INFO COLOR
$info_color: <?= $infoColor ?>;
$info_light_color: $info_color;
$info_dark_color: $info_color;

// SUCCESS CIOLOR
$success_color: <?= $successColor ?>;
$success_light_color: $success_color;
$success_dark_color: $success_color;

// WARNING COLOR
$warning_color: <?= $warningColor ?>;
$warning_light_color: $warning_color;
$warning_dark_color: $warning_color;

// ERROR COLOR
$error_color: <?= $errorColor ?>;
$error_light_color: $error_color;
$error_dark_color: $error_color;

// Extra colors
$tomato_color: #FF6347;
$watermelon_color: #EB617D;
<?php
}
?>
