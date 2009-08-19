<?php
/**
 * Plugin configuration file for wfWebDebugFormPlugin
 * 
 * Configuration registers the new form panel with the web debug toolbar
 * 
 * @package    wfWebDebugFormPlugin
 * @subpackage config
 * @author     Ryan Weaver <ryan@thatsquality.com>
 * version $Id$
 */

class wfWebDebugFormPluginConfiguration extends sfPluginConfiguration
{
	public function initialize()
	{
		$this->dispatcher->connect('debug.web.load_panels', array($this, 'configureWebDebugToolbar'));
	}
	
	/**
	 * Event listener function attachs the new form panel to the web debug toolbar
	 * 
	 * @param sfEvent $event
	 */
	public function configureWebDebugToolbar(sfEvent $event)
  {
    $webDebugToolbar = $event->getSubject();
 
    $webDebugToolbar->setPanel('forms', new wfWebDebugPanelForm($webDebugToolbar));
  }
}
