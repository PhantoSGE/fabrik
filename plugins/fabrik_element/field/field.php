<?php
/**
 * Plugin element to render fields
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.field
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

use Fabrik\Plugins\Element as Element;

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\String\String;
use Fabrik\Helpers\ArrayHelper;
use Fabrik\Helpers\Worker;

/**
 * Plugin element to render fields
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.field
 * @since       3.5
 */
class PlgFabrik_ElementField extends Element
{
	/**
	 * Shows the data formatted for the list view
	 *
	 * @param   string    $data      elements data
	 * @param   stdClass  &$thisRow  all the data in the lists current row
	 *
	 * @return  string	formatted value
	 */

	public function renderListData($data, stdClass &$thisRow)
	{
		$params = $this->getParams();
		$data = Worker::JSONtoData($data, true);
		$format = $params->get('text_format_string');

		foreach ($data as &$d)
		{
			$d = $this->numberFormat($d);

			if ($format != '')
			{
				$d = sprintf($format, $d);
			}

			if ($params->get('password') == "1")
			{
				$d = str_pad('', String::strlen($d), '*');
			}

			$this->_guessLinkType($d, $thisRow, 0);

			if ($params->get('render_as_qrcode', '0') === '1')
			{
				$d = $this->qrCodeLink($d, $thisRow);
			}
		}

		return parent::renderListData($data, $thisRow);
	}

	/**
	 * Get a json encoded string of the element default parameters
	 *
	 * @return  stdClass
	 */
	public function getDefaultAttribs()
	{
		$o = parent::getDefaultAttribs();
		$o->database_join_display_type = 'dropdown';
		$o->text_format = 'text';
		$o->integer_length = 6;
		$o->decimal_length = 2;
		$o->guess_linktype = 0;
		$o->maxlength = 255;

		return $o;
	}


	/**
	 * Prepares the element data for CSV export
	 *
	 * @param   string  $data      Element data
	 * @param   object  &$thisRow  All the data in the lists current row
	 *
	 * @return  string	Formatted CSV export value
	 */

	public function renderListData_csv($data, &$thisRow)
	{
		$params = $this->getParams();
		$data = $this->numberFormat($data);
		$format = $params->get('text_format_string');

		if ($format != '')
		{
			$data = sprintf($format, $data);
		}

		return $data;
	}

	/**
	 * Determines if the element can contain data used in sending receipts,
	 * e.g. fabrikfield returns true
	 *
	 * @deprecated - not used
	 *
	 * @return  bool
	 */

	public function isReceiptElement()
	{
		return true;
	}

	/**
	 * Draws the html form element
	 *
	 * @param   array  $data           To pre-populate element with
	 * @param   int    $repeatCounter  Repeat group counter
	 *
	 * @return  string	elements html
	 */

	public function render($data, $repeatCounter = 0)
	{
		$params = $this->getParams();
		$element = $this->getElement();
		$bits = $this->inputProperties($repeatCounter);
		/* $$$ rob - not sure why we are setting $data to the form's data
		 * but in table view when getting read only filter value from url filter this
		 * _form_data was not set to no readonly value was returned
		 * added little test to see if the data was actually an array before using it
		 */

		if (is_array($this->getFormModel()->data))
		{
			$data = $this->getFormModel()->data;
		}

		$value = $this->getValue($data, $repeatCounter);


		if (!$this->getFormModel()->failedValidation())
		{
			$value = $this->numberFormat($value);
		}

		if (!$this->isEditable())
		{
			if ($params->get('render_as_qrcode', '0') === '1')
			{
				// @TODO - skip this is new form
				$value = $this->qrCodeLink($value, $data);
			}
			else
			{
				$this->_guessLinkType($value, $data, $repeatCounter);
				$format = $params->get('text_format_string');

				if ($format != '')
				{
					$value = sprintf($format, $value);
				}

				if ($params->get('password') == "1")
				{
					$value = str_pad('', String::strlen($value), '*');
				}

				$value = $this->getReadOnlyOutput($value, $value);
			}

			return ($element->hidden == '1') ? "<!-- " . $value . " -->" : $value;
		}

		/* stop "'s from breaking the content out of the field.
		 * $$$ rob below now seemed to set text in field from "test's" to "test&#039;s" when failed validation
		 * so add false flag to ensure its encoded once only
		 * $$$ hugh - the 'double encode' arg was only added in 5.2.3, so this is blowing some sites up
		 */
		if (version_compare(phpversion(), '5.2.3', '<'))
		{
			$bits['value'] = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
		}
		else
		{
			$bits['value'] = htmlspecialchars($value, ENT_COMPAT, 'UTF-8', false);
		}

		$bits['class'] .= ' ' . $params->get('text_format');

		if ($params->get('speech', 0))
		{
			$bits['x-webkit-speech'] = "x-webkit-speech";
		}

		$layout = $this->getLayout('form');
		$layoutData = new stdClass;
		$layoutData->attributes = $bits;

		return $layout->render($layoutData);
	}

	/**
	 * Determines the value for the element in the form view
	 *
	 * @param   array  $data           Form data
	 * @param   int    $repeatCounter  When repeating joined groups we need to know what part of the array to access
	 * @param   array  $opts           Options, 'raw' = 1/0 use raw value
	 *
	 * @return  string	value
	 */

	public function getValue($data, $repeatCounter = 0, $opts = array())
	{
		$value = parent::getValue($data, $repeatCounter, $opts);

		if (is_array($value))
		{
			return array_pop($value);
		}

		return $value;
	}

	/**
	 * Format guess link type
	 *
	 * @param   string  &$value         Original field value
	 * @param   array   $data           Record data
	 * @param   int     $repeatCounter  Repeat counter
	 *
	 * @return  void
	 */

	protected function _guessLinkType(&$value, $data, $repeatCounter = 0)
	{
		$params = $this->getParams();
		$guessed = false;

		if ($params->get('guess_linktype') == '1')
		{
			$w = new Worker;
			$opts = $this->linkOpts();
			$title = $params->get('link_title', '');

			if (Worker::isEmail($value) || String::stristr($value, 'http'))
			{
				$guessed = true;
			}
			elseif (String::stristr($value, 'www.'))
			{
				$value = 'http://' . $value;
				$guessed = true;
			}

			if ($title !== '')
			{
				$opts['title'] = strip_tags($w->parseMessageForPlaceHolder($title, $data));
			}

			$value = FabrikHelperHTML::a($value, $value, $opts);
		}
	}

	/**
	 * Get the link options
	 *
	 * @return  array
	 */

	protected function linkOpts()
	{
		$fbConfig = JComponentHelper::getParams('com_fabrik');
		$params = $this->getParams();
		$target = $params->get('link_target_options', 'default');
		$opts = array();
		$opts['rel'] = $params->get('rel', '');

		switch ($target)
		{
			default:
				$opts['target'] = $target;
				break;
			case 'default':
				break;
			case 'lightbox':
				FabrikHelperHTML::slimbox();
				$opts['rel'] = 'lightbox[]';

				if ($fbConfig->get('use_mediabox', false))
				{
					$opts['target'] = 'mediabox';
				}

				break;
		}

		return $opts;
	}

	/**
	 * Returns javascript which creates an instance of the class defined in formJavascriptClass()
	 *
	 * @param   int  $repeatCounter  Repeat group counter
	 *
	 * @return  array
	 */

	public function elementJavascript($repeatCounter)
	{
		$params = $this->getParams();

		$id = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);

		$input_mask = trim($params->get('text_input_mask', ''));

		if (!empty($input_mask))
		{
			$opts->use_input_mask = true;
			$opts->input_mask = $input_mask;
		}
		else
		{
			$opts->use_input_mask = false;
			$opts->input_mask = '';
		}

		if ($this->getParams()->get('autocomplete', '0') == '2')
		{
			$autoOpts = array();
			$autoOpts['max'] = $this->getParams()->get('autocomplete_rows', '10');
			$autoOpts['storeMatchedResultsOnly'] = false;
			FabrikHelperHTML::autoComplete($id, $this->getElement()->id, $this->getFormModel()->getId(), 'field', $autoOpts);
		}

		return array('FbField', $id, $opts);
	}

	/**
	 * Get the class to manage the form element
	 * to ensure that the file is loaded only once
	 *
	 * @param   array   &$srcs   Scripts previously loaded
	 * @param   string  $script  Script to load once class has loaded
	 * @param   array   &$shim   Dependant class names to load before loading the class - put in requirejs.config shim
	 *
	 * @return void
	 */

	public function formJavascriptClass(&$srcs, $script = '', &$shim = array())
	{
		$params = $this->getParams();
		$input_mask = trim($params->get('text_input_mask', ''));

		if (!empty($input_mask))
		{
			$s = new stdClass;
			$s->deps = array('fab/element');
			$folder = 'components/com_fabrik/libs/masked_input/';
			$s->deps[] = $folder . 'jquery.maskedinput';
			$shim['element/field/field'] = $s;
		}

		parent::formJavascriptClass($srcs, $script, $shim);

		// $$$ hugh - added this, and some logic in the view, so we will get called on a per-element basis
		return false;
	}

	/**
	 * Get database field description
	 *
	 * @return  string  db field type
	 */

	public function getFieldDescription()
	{
		$p = $this->getParams();

		if ($this->encryptMe())
		{
			return 'BLOB';
		}

		switch ($p->get('text_format'))
		{
			case 'text':
			default:
				$type = "VARCHAR(" . $p->get('maxlength', 255) . ")";
				break;
			case 'integer':
				$type = "INT(" . $p->get('integer_length', 11) . ")";
				break;
			case 'decimal':
				$total = (int) $p->get('integer_length', 11) + (int) $p->get('decimal_length', 2);
				$type = "DECIMAL(" . $total . "," . $p->get('decimal_length', 2) . ")";
				break;
		}

		return $type;
	}

	/**
	 * Can the element plugin encrypt data
	 *
	 * @return  bool
	 */

	public function canEncrypt()
	{
		return true;
	}

	/**
	 * Manipulates posted form data for insertion into database
	 *
	 * @param   mixed  $val   This elements posted form data
	 * @param   array  $data  Posted form data
	 *
	 * @return  mixed
	 */

	public function storeDatabaseFormat($val, $data)
	{
		if (is_array($val))
		{
			foreach ($val as $k => $v)
			{
				$val[$k] = $this->_indStoreDatabaseFormat($v);
			}

			$val = implode(GROUPSPLITTER, $val);
		}
		else
		{
			$val = $this->_indStoreDatabaseFormat($val);
		}

		return $val;
	}

	/**
	 * Manipulates individual values posted form data for insertion into database
	 *
	 * @param   string  $val  This elements posted form data
	 *
	 * @return  string
	 */

	protected function _indStoreDatabaseFormat($val)
	{
		return $this->unNumberFormat($val);
	}

	/**
	 * Get the element's cell class
	 *
	 * @since 3.0.4
	 *
	 * @return  string	css classes
	 */

	public function getCellClass()
	{
		$params = $this->getParams();
		$classes = parent::getCellClass();
		$format = $params->get('text_format');

		if ($format == 'decimal' || $format == 'integer')
		{
			$classes .= ' ' . $format;
		}

		return $classes;
	}

	/**
	 * Output a QR Code image
	 *
	 * @since 3.1
	 */

	public function onAjax_renderQRCode()
	{
		$input = $this->app->input;
		$this->setId($input->getInt('element_id'));
		$this->loadMeForAjax();
		$this->getElement();
		$url = 'index.php';
		$this->language->load('com_fabrik.plg.element.field', JPATH_ADMINISTRATOR);

		if (!$this->canView())
		{
			$this->app->enqueueMessage(FText::_('PLG_ELEMENT_FIELD_NO_PERMISSION'));
			$this->app->redirect($url);
			exit;
		}

		$rowId = $input->get('rowid', '', 'string');

		if (empty($rowId))
		{
			// $app->enqueueMessage(FText::_('PLG_ELEMENT_FIELD_NO_SUCH_FILE'));
			$this->app->redirect($url);
			exit;
		}

		$listModel = $this->getListModel();
		$row = $listModel->getRow($rowId, false);

		if (empty($row))
		{
			// $app->enqueueMessage(FText::_('PLG_ELEMENT_FIELD_NO_SUCH_FILE'));
			$this->app->redirect($url);
			exit;
		}

		$elName = $this->getFullName(true, false);
		$value = $row->$elName;

		require JPATH_SITE . '/components/com_fabrik/libs/qrcode/qrcode.php';

		// Usage: $a=new QR('234DSKJFH23YDFKJHaS');$a->image(4);
		$qr = new QR($value);
		$img = $qr->image(4);

		if (!empty($img))
		{
			// Some time in the past
			header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			header('Accept-Ranges: bytes');
			header('Content-Length: ' . strlen($img));
			header('Content-Type: ' . 'image/gif');

			// Serve up the file
			echo $img;

			// And we're done.
			exit();
		}
		else
		{
			$this->app->enqueueMessage(FText::_('PLG_ELEMENT_FIELD_NO_SUCH_FILE'));
			$this->app->redirect($url);
			exit;
		}
	}

	/**
	 * Get a link to this element which will call onAjax_renderQRCode().
	 *
	 * @param   value  string  element's value
	 * @param   thisRow  array  row data
	 *
	 * @since 3.1
	 *
	 * @return   string  QR code link
	 */

	protected function qrCodeLink($value, $thisRow)
	{
		if (is_object($thisRow))
		{
			$thisRow = ArrayHelper::fromObject($thisRow);
		}

		$package = $this->app->getUserState('com_fabrik.package', 'fabrik');
		$formModel = $this->getFormModel();
		$formId = $formModel->getId();
		$rowId = $formModel->getRowId();

		if (empty($rowId))
		{
			/**
			 * Meh.  See commentary at the start of $formModel->getEmailData() about rowid.  For now, if this is a new row,
			 * the only place we're going to find it is in the list model's lastInsertId.  Bah humbug.
			 * But check __pk_val first anyway, what the heck.
			 */
			
			$rowId = ArrayHelper::getValue($thisRow, '__pk_val', '');
			
			if (empty($rowId))
			{
				/**
				 * Nope.  Try lastInsertId. Or maybe on top of the fridge?  Or in the microwave?  Down the back
				 * of the couch cushions? 
				 */
				
				$rowId = $formModel->getListModel()->lastInsertId;
				
				/**
				 * OK, give up.  If *still* no rowid, we're probably being called from something like getEmailData() on onBeforeProcess or
				 * onBeforeStore, and it's a new form, so no rowid yet.  So no point returning anything yet.
				 */
					
				if (empty($rowId))
				{
					return '';
				}
			}
		}

		/*
		 * YAY!!!  w00t!!  We have a rowid.  Whoop de freakin' doo!!
		 */
		
		$elementid = $this->getId();
		$src = COM_FABRIK_LIVESITE
		. 'index.php?option=com_' . $package . '&amp;view=pluginAjax&amp;plugin=field&amp;method=ajax_renderQRCode&amp;'
				. 'format=raw&amp;element_id=' . $elementid . '&amp;formid=' . $formId . '&amp;rowid=' . $rowId . '&amp;repeatcount=0';

		$layout = $this->getLayout('qr');
		$displayData = new stdClass;
		$displayData->src = $src;

		return $layout->render($displayData);
	}
	
	/**
	 * Turn form value into email formatted value
	 *
	 * @param   mixed  $value          Element value
	 * @param   array  $data           Form data
	 * @param   int    $repeatCounter  Group repeat counter
	 *
	 * @return  string  email formatted value
	 */
	
	protected function getIndEmailValue($value, $data = array(), $repeatCounter = 0)
	{
		$params = $this->getParams();
		
		if ($params->get('render_as_qrcode', '0') === '1')
		{
			return html_entity_decode($this->qrCodeLink($value, $data));
		}
		else
		{
			return parent::getIndEmailValue($value, $data, $repeatCounter);
		}
	}
	
}
