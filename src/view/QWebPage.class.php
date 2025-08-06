<?php

/**
 * QWebPage
 * @class.name QWebPage
 */
abstract class QWebPage_frame_ extends QWebControl implements QIUrlController
{
	use QWebPage_Methods;
	
	public $docType = "<!doctype html>\n";
	
	public $onPdfExport = false;
}

