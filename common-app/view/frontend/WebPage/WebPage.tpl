<!DOCTYPE html<?php $_qBHV = \QWebPage::BrowserHtmlVersion(); echo ($_qBHV >= 5) ? "" : " PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\""; ?>>
<html <?= ($_qBHV >= 5) ? "" : " xmlns=\"http://www.w3.org/1999/xhtml\""; ?>>
    @include($this::head)
    @include($this::body)
</html>