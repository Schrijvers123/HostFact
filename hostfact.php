<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.HostFact
 *
 * @copyright   Copyright (C) 2005 - 2018 SCHRIJVERS123.NL. All rights reserved.
 * @license     GNU GPL v3 or later
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Joomla! HostFact plugin.
 *
 * @since  1.5
 */
class PlgSystemHostfact extends JPlugin
{
	public function onBeforeCompileHead()
	{
		if(JFactory::getApplication()->isClient('administrator')) {
			return;
		}
		$document = jFactory::getDocument();
		
		$script = 'media/plg_system_hostfact/js/hf-orderform.js';
		$document->addScript($script);
		
	}
	public function onAfterRender()
	{
		$app = jFactory::getApplication();
		
		if (JFactory::getApplication()->isClient('administrator'))
		{
			return;
		}
		$hostfact_url = $this->params->get('orderform_url');
		if ($hostfact_url = "") {
			return;
		}
		
		$text = $app->getBody();
		
		$pattern = '#\{hostfact ([0-9]+)(.*?)?\}#i';
		// Found matches
		if (preg_match_all($pattern, $text, $matches)) {
			// No replacement when we're not dealing with HTML
			if (JFactory::getDocument()->getType() != 'html') {
				$text = preg_replace($pattern, '', $text);
				return true;
			}
			
			// Disable caching
			$cache = JFactory::getCache('com_content');
			$cache->setCaching(false);

			foreach ($matches[0] as $i => $fullMatch)
			{
				$attributes = trim($matches[2][$i]);
				if (preg_match_all('#[a-z0-9_\-]+=".*?"#i', $attributes, $attributesMatches))
				{
					
					$data = array();

					foreach ($attributesMatches[0] as $pair)
					{
						list($attribute, $value) = explode('=', $pair, 2);

						$attribute  = trim(html_entity_decode($attribute));
						$value 		= html_entity_decode(trim($value, '"'));

						if (isset($data[$attribute]))
						{
							if (!is_array($data[$attribute]))
							{
								$data[$attribute] = (array) $data[$attribute];
							}

							$data[$attribute][] = $value;
						}
						else
						{
							$data[$attribute] = $value;
						}
					}


					if ($data)
					{
						JFactory::getApplication()->input->get->set('form', $data);
					}
				}

				$formId = $matches[1][$i];
				$formAttributes = str_replace("|","&",$matches[2][$i]);
				
	
				$orderform = $this->params->get('orderform_url') . $formId. $formAttributes;
				
				$hostfact_orderform = '<iframe src="' . $orderform .'" scrolling="no" class="hf-orderform" style="width:100%;border:0;overflow-y:hidden;min-height:420px;"></iframe>';

				$text = str_replace($fullMatch, $hostfact_orderform, $text);

			}
		}
		
		
		/* Set changes on the page */

			
		$app->setBody($text);
		
	}
}
