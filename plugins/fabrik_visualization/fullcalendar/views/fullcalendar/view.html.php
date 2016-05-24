<?php
/**
 * Fabrik Calendar HTML View
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.calendar
 * @copyright   Copyright (C) 2005-2013 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Fabrik\Helpers\Html;
use Fabrik\Helpers\Text;
use Fabrik\Helpers\Worker;

jimport('joomla.application.component.view');

/**
 * Fabrik Calendar HTML View
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.calendar
 * @since       3.0
 */
class FabrikViewFullcalendar extends JViewLegacy
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string $tpl The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 */
	public function display($tpl = 'default')
	{
		$app         = JFactory::getApplication();
		$input       = $app->input;
		$model       = $this->getModel();
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$id          = $input->get('id', $usersConfig->get('visualizationid', $input->get('visualizationid', 0)));
		$model->setId($id);
		$this->row = $model->getVisualization();

		if (!$model->canView())
		{
			echo Text::_('JERROR_ALERTNOAUTHOR');

			return false;
		}

		$params              = $model->getParams();
		$this->events        = $model->setUpEvents();
		$this->params        = $params;
		$this->containerId   = $model->getJSRenderContext();
		$this->filters       = $this->get('Filters');
		$this->showFilters   = $model->showFilters();
		$this->showTitle     = $input->getInt('show-title', 1);
		$this->filterFormURL = $this->get('FilterFormURL');

		$this->canAdd               = (bool) $params->get('fullcalendar-read-only', 0) == 1 ? false : $model->getCanAdd();
		$this->requiredFiltersFound = $this->get('RequiredFiltersFound');

		if ($params->get('fullcalendar_show_messages', '1') == '1' && $this->canAdd && $this->requiredFiltersFound)
		{
			$msg = Text::_('PLG_VISUALIZATION_FULLCALENDAR_DOUBLE_CLICK_TO_ADD');
			$msg .= $model->getDateLimitsMsg();
			$app->enqueueMessage($msg);
		}

		$this->jLayouts();
		$this->jsText();
		$this->iniJs();

		$this->params = $model->getParams();
		$tpl          = $params->get('calendar_layout', $tpl);
		$tmplPath     = JPATH_ROOT . '/plugins/fabrik_visualization/fullcalendar/views/fullcalendar/tmpl/' . $tpl;
		$this->_setPath('template', $tmplPath);

		$this->css($tpl);

		return parent::display();
	}

	/**
	 * Add CSS
	 *
	 * @param $tpl
	 */
	private function css($tpl)
	{
		$lib = COM_FABRIK_LIVESITE . 'plugins/fabrik_visualization/fullcalendar/libs/fullcalendar/';
		Html::styleSheet($lib . 'fullcalendar.css');

		// Add our css
		Html::stylesheetFromPath('plugins/fabrik_visualization/fullcalendar/fullcalendar.css');
		JHTML::stylesheet('media/com_fabrik/css/list.css');
		Html::stylesheetFromPath('plugins/fabrik_visualization/fullcalendar/views/fullcalendar/tmpl/' . $tpl . '/template.css');

		// Adding custom.css, just for the heck of it
		Html::stylesheetFromPath('plugins/fabrik_visualization/fullcalendar/views/fullcalendar/tmpl/' . $tpl . '/custom.css');
	}

	/**
	 * Get Js Options
	 *
	 * @return stdClass
	 * @throws Exception
	 */
	private function jsOptions()
	{
		$model    = $this->getModel();
		$app      = JFactory::getApplication();
		$package  = $app->getUserState('com_fabrik.package', 'fabrik');
		$params   = $model->getParams();
		$Itemid   = Worker::itemId();
		$urls     = new stdClass;
		$calendar = $this->row;
		$tpl      = $params->get('fullcalendar_layout', 'bootstrap');

		// Get all list where statements - which are then included in the ajax call to ensure we get the correct data set loaded
		$urlFilters        = new stdClass;
		$urlFilters->where = $model->buildQueryWhere();

		// Don't JRoute as its wont load with sef?
		$urls->del = 'index.php?option=com_' . $package
			. '&controller=visualization.fullcalendar&view=visualization&task=deleteEvent&format=raw&Itemid=' . $Itemid
			. '&id=' . $model->getId();
		$urls->add = 'index.php?option=com_' . $package . '&view=visualization&format=raw&Itemid=' . $Itemid
			. '&id=' . $model->getId();

		$options                  = new stdClass;
		$options->url             = $urls;
		$options->dateLimits      = $model->getDateLimits();
		$options->deleteables     = $model->getDeleteAccess();
		$options->eventLists      = $model->getEventLists();
		$options->calendarId      = $calendar->id;
		$options->popwiny         = $params->get('yoffset', 0);
		$options->urlfilters      = $urlFilters;
		$options->canAdd          = $this->canAdd;
		$options->showFullDetails = (bool) $params->get('show_full_details', false);
		$options->restFilterStart = Worker::getMenuOrRequestVar('resetfilters', 0, false, 'request');
		$options->tmpl            = $tpl;

		// $$$rob @TODO not sure this is need - it isn't in the timeline viz
		$model->setRequestFilters();
		$options->filters = $model->filters;

		// End not sure
		$options->Itemid         = $Itemid;
		$options->show_day       = (bool) $params->get('show_day', true);
		$options->show_week      = (bool) $params->get('show_week', true);
		$options->default_view   = $params->get('fullcalendar_default_view', 'month');
		$options->add_type       = $params->get('add_type', 'both');
		$options->time_format    = $params->get('time_format', 'H(:mm)');
		$options->first_week_day = (int) $params->get('first_week_day', 0);
		$options->minDuration    = $params->get('minimum_duration', "00:30:00");
		$options->open           = $params->get('open-hour', "00:00:00");
		$options->close          = $params->get('close-hour', "23:59:59");
		$options->lang           = Worker::getShortLang();
		$options->showweekends   = (bool) $params->get('calendar-show-weekends', true);
		$options->readonly       = (bool) $params->get('calendar-read-only', false);
		$options->timeFormat     = $params->get('time_format', '%X');
		$options->readonlyMonth  = (bool) $params->get('readonly_monthview', false);
		$options->calOptions     = $params->get('calOptions', '{}');

		return $options;
	}

	/**
	 * Add text for javascript translations
	 */
	private function jsText()
	{
		Text::script('PLG_VISUALIZATION_FULLCALENDAR_CONF_DELETE');
		Text::script('PLG_VISUALIZATION_FULLCALENDAR_DELETE');
		Text::script('PLG_VISUALIZATION_FULLCALENDAR_VIEW');
		Text::script('PLG_VISUALIZATION_FULLCALENDAR_EDIT');
		Text::script('PLG_VISUALIZATION_FULLCALENDAR_ADD_EVENT');
		Text::script('PLG_VISUALIZATION_FULLCALENDAR_EDIT_EVENT');
		Text::script('PLG_VISUALIZATION_FULLCALENDAR_VIEW_EVENT');
		Text::script('PLG_VISUALIZATION_FULLCALENDAR_EVENT_START_END');
		Text::script('PLG_VISUALIZATION_FULLCALENDAR_DATE_ADD_TOO_LATE');
		Text::script('PLG_VISUALIZATION_FULLCALENDAR_DATE_ADD_TOO_EARLY');
		Text::script('PLG_VISUALIZATION_FULLCALENDAR_CLOSE');
	}

	/**
	 * Initialize the js
	 *
	 * @return void
	 */
	private function iniJs()
	{
		$model = $this->getModel();
		$ref   = $model->getJSRenderContext();
		$json  = json_encode($this->jsOptions());
		$js    = array();
		$js[]  = "\tvar $ref = new fabrikFullcalendar('$ref', $json);";
		$js[]  = "\tFabrik.addBlock('" . $ref . "', $ref);";
		$js[]  = "" . $model->getFilterJs();
		$js    = implode("\n", $js);

		$srcs   = Html::framework();
		$srcs['FbListFilter'] = 'media/com_fabrik/js/listfilter.js';
		$srcs['fabrikFullcalendar'] = 'plugins/fabrik_visualization/fullcalendar/fullcalendar.js';

		$shim = $model->getShim();

		$shim['fullcalendar'] = (object) array('deps' =>
			array('lib/moment/moment')
		);

		$shim['viz/fullcalendar/fullcalendar'] = (object) array('deps' =>
			array('fullcalendar', 'jquery')
		);

		Html::iniRequireJs($shim, array('fullcalendar' => 'plugins/fabrik_visualization/fullcalendar/libs/fullcalendar/fullcalendar.min'));
		Html::script($srcs, $js);
	}

	/**
	 * Create JS JLayout data
	 *
	 * @return void
	 */
	private function jLayouts()
	{
		Html::jLayoutJs(
			'fabrik-visualization-fullcalendar-viewbuttons',
			'fabrik-visualization-fullcalendar-viewbuttons',
			(object) array(),
			array(JPATH_PLUGINS . '/fabrik_visualization/fullcalendar/layouts/')
		);

		Html::jLayoutJs(
			'fabrik-visualization-fullcalendar-event-popup',
			'fabrik-visualization-fullcalendar-event-popup',
			(object) array(),
			array(JPATH_PLUGINS . '/fabrik_visualization/fullcalendar/layouts/')
		);

		Html::jLayoutJs(
			'fabrik-visualization-fullcalendar-viewevent',
			'fabrik-visualization-fullcalendar-viewevent',
			(object) array(),
			array(JPATH_PLUGINS . '/fabrik_visualization/fullcalendar/layouts/')
		);

		$modalOpts = array(
			'content' => '',
			'id' => 'fullcalendar_addeventwin',
			'title' => Text::_('PLG_VISUALIZATION_FULLCALENDAR_ADD_EVENT'),
			'modal' => false,
			'expandable' => true
		);

		Html::jLayoutJs(
			'fullcalendar_addeventwin',
			'fabrik-modal',
			(object) $modalOpts
		);

		$modalOpts = array(
			'content' => '',
			'id' => 'fullcalendar_chooseeventwin',
			'title' => Text::_('PLG_VISUALIZATION_FULLCALENDAR_PLEASE_SELECT'),
			'modal' => false,
			'expandable' => true
		);

		Html::jLayoutJs(
			'fullcalendar_chooseeventwin',
			'fabrik-modal',
			(object) $modalOpts
		);
	}
}
