<?php
function outputCssContent()
{
	list ($white, $black, $darkPrimaryColor, $primaryColor, $lightPrimaryColor, $accentColor, $grey, $borderColor) = func_get_args();
	?>
// DEFAULT NON COLORS
$white: <?= $white ?>;
$black: <?= $black ?>;

$dark_primary_color: <?= $darkPrimaryColor ?>;
$primary_color: <?= $primaryColor ?>;
$light_primary_color: <?= $lightPrimaryColor ?>;
$accent_color: <?= $accentColor ?>;
$grey: <?= $grey ?>;
$border_color: <?= $borderColor ?>;

<?php
}
?>