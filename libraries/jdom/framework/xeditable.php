<?php
/**
* @author		Girolamo Tomaselli http://bygiro.com - girotomaselli@gmail.com
* @license		GNU General Public License
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class JDomFrameworkXeditable extends JDomFramework
{	

	var $assetName = 'xeditable';
	
	var $framework;
	var $inputs;
	
	var $attachJs = array();
	var $attachCss = array();
	
	protected static $loaded = array();	
	
	/*
	 * Constuctor
	 * 	@namespace 	: requested class
	 *  @options	: Configuration
	 *
	 *
	 */
	function __construct($args)
	{
		parent::__construct($args);
		
		$this->arg('framework'	, null, $args,'bootstrap');
		$this->arg('inputs'	, null, $args,array());		
	}
	
	function build()
	{	
		$hash = MD5($this->framework . implode('',$this->inputs));
		// Only load once
		if (!empty(static::$loaded[$hash]))
		{
			return;
		}
		$doc = JFactory::getDocument();
		
		$compatibleInputs = array();
		switch(strtolower($this->framework)){
			case 'jquery':
				JDom::_('framework.jquery');
				$root = 'assets/'. $this->assetName .'/jquery-editable';
				
				// base assets
				$this->attachJs[] = $root .'/js/jquery-editable-poshytip.js';		
				$this->attachCss[] = $root .'/css/jquery-editable-poshytip.css';
				
				break;

			case 'jqueryui':
				JDom::_('framework.jquery.ui');
				
				$root = 'assets/'. $this->assetName .'/jqueryui-editable';
				
				// base assets
				$this->attachJs[] = $root .'/js/jqueryui-editable.js';		
				$this->attachCss[] = $root .'/css/jqueryui-editable.css';
				
				break;
				
			case 'bootstrap3':
			
				break;
				
			case 'bootstrap':
			default:
				JDom::_('framework.bootstrap');
				$root = 'assets/'. $this->assetName .'/bootstrap-editable';
				
				// base assets
				$this->attachJs[] = $root .'/js/bootstrap-editable.js';		
				$this->attachCss[] = $root .'/css/bootstrap-editable.css';
		
				$compatibleInputs = array(
					'typeaheadjs' => array(
						'js' => 'lib/typeahead.js',
						'css' => 'lib/typeahead.js-bootstrap.css',
						'js' => 'typeaheadjs.js',
					),
					'wysihtml5' => array(
						'js' => 'bootstrap-wysihtml5-0.0.2/bootstrap-wysihtml5-0.0.2.js',
						'css' => 'bootstrap-wysihtml5-0.0.2/bootstrap-wysihtml5-0.0.2.css',
						'js' => 'bootstrap-wysihtml5-0.0.2/wysihtml5-0.3.0.js',
						'css' => 'bootstrap-wysihtml5-0.0.2/wysiwyg-color.css',
						'js' => 'wysihtml5.js'
					),
					'address' => array(
						'js' => 'address.js',
						'css' => 'address.css'
					)
				);
				break;
		}
		
		// inputs assets
		$compatibleInputs_keys = array_values($compatibleInputs);
		foreach($this->inputs as $in){
			if(!in_array($in,$compatibleInputs_keys)){
				continue;
			}
			
			foreach($compatibleInputs[$in] as $type => $extraFile){
				if($type == 'js'){
					$this->attachJs[] = $root .'/inputs-ext/'. $in .'/'. $extraFile;
				} else {
					$this->attachCss[] = $root .'/inputs-ext/'. $in .'/'. $extraFile;
				}
			}
		}
		
		static::$loaded[$hash] = true;
	}
	
	function buildCss()
	{
	//	$this->attachCss[] = 'bootstrap.min.css';
	}
	
	function buildJs()
	{
	//	$this->attachCss[] = 'bootstrap.min.css';
	}
}