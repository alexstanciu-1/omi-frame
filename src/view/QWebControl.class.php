<?php

/**
 * QWebControl
 *
 * @author Alex
 * @class.name QWebControl
 */
abstract class QWebControl_frame_ extends QViewBase
{
	use QWebControl_Methods;
	
	/**
	 * Specify the method to be redered
	 *
	 * @var string
	 */
	public $renderMethod = "render";
	/**
	 * True if the control should be re-rendered
	 *
	 * @var boolean
	 */
	public $changed;

}
