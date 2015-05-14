<?php
/**
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2014 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class Eeweetwitter extends Module
{
	protected $config_form = false;

	public function __construct()
	{
		$this->name = 'eeweetwitter';
		$this->tab = 'social_networks';
		$this->version = '1.0.0';
		$this->author = 'Eewee.fr';
		$this->need_instance = 1;

		/**
		 * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
		 */
		$this->bootstrap = true;

		parent::__construct();

		$this->displayName = $this->l('Twitter (eewee.fr)');
		$this->description = $this->l('Widget Twitter for your eShop');

		$this->confirmUninstall = $this->l('');
	}

	/**
	 * Don't forget to create update methods if needed:
	 * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
	 */
	public function install()
	{
		Configuration::updateValue('EEWEETWITTER_LIVE_MODE', false);

		include(dirname(__FILE__).'/sql/install.php');

		return parent::install() &&
			$this->registerHook('header') &&
			$this->registerHook('backOfficeHeader') &&
			$this->registerHook('displayFooter');
	}

	public function uninstall()
	{
		Configuration::deleteByName('EEWEETWITTER_LIVE_MODE');

		include(dirname(__FILE__).'/sql/uninstall.php');

		return parent::uninstall();
	}

	/**
	 * Load the configuration form
	 */
	public function getContent()
	{
		/**
		 * If values have been submitted in the form, process.
		 */
		$this->_postProcess();

		$this->context->smarty->assign('module_dir', $this->_path);

		$output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

		return $output.$this->renderForm();
	}

	/**
	 * Create the form that will be displayed in the configuration of your module.
	 */
	protected function renderForm()
	{
		$helper = new HelperForm();

		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$helper->module = $this;
		$helper->default_form_language = $this->context->language->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitEeweetwitterModule';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
			.'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');

		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);
		
		return $helper->generateForm(array($this->getConfigForm()));
	}

	/**
	 * Create the structure of your form.
	 * SOURCE : http://doc.prestashop.com/display/PS16/Using+the+HelperForm+class
	 */
	protected function getConfigForm()
	{
		// options
		$options1 = array(
			array(
				'id_option' => 1,		// value
				'name' => 'Method 1'    // label
			),
			array(
				'id_option' => 2,
				'name' => 'Method 2'
			),
			array(
				'id_option' => 3,
				'name' => 'Method 3'
			)
		);
		$options2 = array(
			array(
				'id_option' => 1,		// value
				'name' => 'Method A'    // label
			),
			array(
				'id_option' => 2,
				'name' => 'Method B'
			),
			array(
				'id_option' => 3,
				'name' => 'Method C'
			)
		);
		/*
		 $options2 = array();
		 foreach (Gender::getGenders((int)Context::getContext()->language->id) as $gender)
		 {
		 $options2[] = array(
		 "id" => (int)$gender->id,
		 "name" => $gender->name
		 );
		 }
		 */
		
		return array(
			'form' => array(
				'tinymce' => true,
				'legend' => array(
					'title' => $this->l('Settings'),
					'icon' => 'icon-cogs',
				),
				'input' => array(
					
					// SWITCH	
					array(
						'type' => 'switch',
						'label' => $this->l('Live mode'),
						'name' => 'EEWEETWITTER_LIVE_MODE',
						'is_bool' => false,
						'desc' => $this->l('Use this module in live mode'),
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => true,
								'label' => $this->l('Enabled')
							),
							array(
								'id' => 'active_off',
								'value' => false,
								'label' => $this->l('Disabled')
							)
						),
					),

					// TEXT
					array(
						'col' => 3,
						'type' => 'text',
						'prefix' => '<i class="icon icon-envelope"></i>',
						'suffix' => '<i class="icon icon-envelope"></i>',
						'desc' => $this->l('Enter a valid email address'),
						'name' => 'EEWEETWITTER_ACCOUNT_EMAIL',
						'label' => $this->l('Email'),
					),
					
					// TEXTAREA
					array(
						'cols' => 40,
						'rows' => 10,
						'type' => 'textarea',
						'desc' => $this->l('Lorem ipsum'),
						'name' => 'EEWEETWITTER_TEXTAREA',
						'label' => $this->l('Lorem'),
						// tinymce	
						'class' => 'rte',
						'autoload_rte' => true,
					),
					array(
						'cols' => 3,
						'type' => 'textarea',
						'desc' => $this->l('Lorem ipsum'),
						'name' => 'EEWEETWITTER_TEXTAREA2',
						'label' => $this->l('Lorem'),
					),
						
					// PASSWORD
					array(
						'type' => 'password',
						'name' => 'EEWEETWITTER_ACCOUNT_PASSWORD',
						'label' => $this->l('Password'),
					),
						
					// SELECT
					array(
						'type' => 'select',                              // This is a <select> tag.
						'label' => $this->l('Shipping method:'),         // The <label> for this <select> tag.
						'desc' => $this->l('Choose a shipping method'),  // A help text, displayed right next to the <select> tag.
						'name' => 'EEWEETWITTER_SELECT',                 // The content of the 'id' attribute of the <select> tag.
						'required' => true,                              // If set to true, this option must be set.
						'options' => array(
							'query' => $options1, 		             // $options contains the data itself.
							'id' => 'id_option',                     // The value of the 'id' key must be the same as the key for 'value' attribute of the <option> tag in each $options sub-array.
							'name' => 'name'                         // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
						)
					),
					
					// CHECKBOX
					array(
						'type'    => 'checkbox',                   	// This is an <input type="checkbox"> tag.
						'label'   => $this->l('Options'),          	// The <label> for this <input> tag.
						'desc'    => $this->l('Choose options.'),  	// A help text, displayed right next to the <input> tag.
						'name'    => 'EEWEETWITTER_CHECKBOX',      	// The content of the 'id' attribute of the <input> tag.
						'values'  => array(
							'query' => $options2,		           	// $options contains the data itself.
							'id'    => 'id_option',                	// The value of the 'id' key must be the same as the key
																	// for the 'value' attribute of the <option> tag in each $options sub-array.
							'name'  => 'name',		                // The value of the 'name' key must be the same as the key
																	// for the text content of the <option> tag in each $options sub-array.
							'expand' => array(                      // 1.6-specific: you can hide the checkboxes when there are too many.
																	// A button appears with the number of options it hides.
								['print_total'] => count($options1),
								'default' => 'show',
								'show' => array('text' => $this->l('show'), 'icon' => 'plus-sign-alt'),
								'hide' => array('text' => $this->l('hide'), 'icon' => 'minus-sign-alt')
							),
						),
					),
					
					// RADIO
					array(
						'type'      => 'radio',                               // This is an <input type="checkbox"> tag.
						'label'     => $this->l('Enable this option'),        // The <label> for this <input> tag.
						'desc'      => $this->l('Are you a customer too?'),   // A help text, displayed right next to the <input> tag.
						'name'      => 'EEWEETWITTER_RADIO',                  // The content of the 'id' attribute of the <input> tag.
						'required'  => true,                                  // If set to true, this option must be set.
						'class'     => 't',                                   // The content of the 'class' attribute of the <label> tag for the <input> tag.
						'is_bool'   => false,           	                  // If set to true, this means you want to display a yes/no or true/false option.
																			  // The CSS styling will therefore use green mark for the option value '1', and a red mark for value '2'.
																			  // If set to false, this means there can be more than two radio buttons,
																			  // and the option label text will be displayed instead of marks.
						'values'    => array(                                 // $values contains the data itself.
							array(
								'id'    => 'active_on',                       // The content of the 'id' attribute of the <input> tag, and of the 'for' attribute for the <label> tag.
								'value' => 1,                                 // The content of the 'value' attribute of the <input> tag.
								'label' => $this->l('Enabled')                // The <label> for this radio button.
							),
							array(
								'id'    => 'active_off',
								'value' => 0,
								'label' => $this->l('Disabled')
							)
						),
					),
						
					// COLOR
					array(
						'col' => 3,
						'type' => 'color',
						'desc' => $this->l('lorem ipsum'),
						'name' => 'EEWEETWITTER_COLOR',
						'label' => $this->l('Lorem'),
					),	

					// DATE
					array(
						'col' => 6,
						'type' => 'date',
						'desc' => $this->l('lorem ipsum'),
						'name' => 'EEWEETWITTER_DATE',
						'label' => $this->l('Lorem'),
					),
						
					// FILE
					array(
						'col' => 8,
						'type' => 'file',
						'desc' => $this->l('lorem ipsum'),
						'name' => 'EEWEETWITTER_FILE',
						'label' => $this->l('Lorem'),
						'display_image' => true,
						'thumb' => '../modules/'.$this->name.'/img/'.Configuration::get('EEWEETWITTER_FILE'), // aperçu de l'image
						//'image' => '/img/'.Configuration::get('EEWEETWITTER_FILE'),
				
						//'multiple' => true,
						//'max_files' => 2,
						//'ajax' => true,
					),
						
				),
					
				// SUBMIT
				'submit' => array(
					'title' => $this->l('Save'),
				),
			),
		);
	}

	/**
	 * Set values for the inputs.
	 */
	protected function getConfigFormValues()
	{
		$val = array(
			'EEWEETWITTER_LIVE_MODE'		=> Configuration::get('EEWEETWITTER_LIVE_MODE'),
			'EEWEETWITTER_ACCOUNT_EMAIL'	=> Configuration::get('EEWEETWITTER_ACCOUNT_EMAIL'),
			'EEWEETWITTER_TEXTAREA'			=> Configuration::get('EEWEETWITTER_TEXTAREA'),
			'EEWEETWITTER_TEXTAREA2'		=> Configuration::get('EEWEETWITTER_TEXTAREA2'),
			'EEWEETWITTER_ACCOUNT_PASSWORD' => Configuration::get('EEWEETWITTER_ACCOUNT_PASSWORD'),
			'EEWEETWITTER_SELECT'			=> Configuration::get('EEWEETWITTER_SELECT'),
			'EEWEETWITTER_CHECKBOX_1'		=> Configuration::get('EEWEETWITTER_CHECKBOX_1'),
			'EEWEETWITTER_CHECKBOX_2'		=> Configuration::get('EEWEETWITTER_CHECKBOX_2'),
			'EEWEETWITTER_CHECKBOX_3'		=> Configuration::get('EEWEETWITTER_CHECKBOX_3'),
			'EEWEETWITTER_RADIO'			=> Configuration::get('EEWEETWITTER_RADIO'),
			'EEWEETWITTER_COLOR'			=> Configuration::get('EEWEETWITTER_COLOR'),
			'EEWEETWITTER_DATE'				=> Configuration::get('EEWEETWITTER_DATE'),
			'EEWEETWITTER_FILE'				=> Configuration::get('EEWEETWITTER_FILE'),
		);
		//echo '<hr><hr><hr><hr><hr><hr><hr><hr><hr><hr><hr><hr><hr><hr>';
		//echo '<pre>'.var_export( $val, true ).'</pre>';
		
		return $val;
	}

	/**
	 * Save form data.
	 */
	protected function _postProcess()
	{
		if (Tools::isSubmit('submitEeweetwitterModule'))
		{
			$form_values = $this->getConfigFormValues();

			// UPDATE
			foreach (array_keys($form_values) as $key)
			{
				Configuration::updateValue($key, Tools::getValue($key));
				//echo 'key:'.$key.' - '.Tools::getValue($key).'<hr>';
			}
			
			// UPLOAD FILE
			if (isset($_FILES['EEWEETWITTER_FILE']) && isset($_FILES['EEWEETWITTER_FILE']['tmp_name']) && !empty($_FILES['EEWEETWITTER_FILE']['tmp_name']))
			{
				if ($error = ImageManager::validateUpload($_FILES['EEWEETWITTER_FILE'], 4000000))
					return $this->displayError($this->l('Invalid image.'));
				else
				{
					$ext = substr($_FILES['EEWEETWITTER_FILE']['name'], strrpos($_FILES['EEWEETWITTER_FILE']['name'], '.') + 1);
					$file_name = md5($_FILES['EEWEETWITTER_FILE']['name']).'.'.$ext;
					if (!move_uploaded_file($_FILES['EEWEETWITTER_FILE']['tmp_name'], dirname(__FILE__).'/img/'.$file_name))
						return $this->displayError($this->l('An error occurred while attempting to upload the file.'));
					else
					{
						if (Configuration::hasContext('EEWEETWITTER_FILE', null, Shop::getContext()) && Configuration::get('EEWEETWITTER_FILE') != $file_name)
							@unlink(dirname(__FILE__).'/'.Configuration::get('EEWEETWITTER_FILE'));
						Configuration::updateValue('EEWEETWITTER_FILE', $file_name);
						$this->_clearCache('eeweetwitter.tpl');
						return $this->displayConfirmation($this->l('The settings have been updated.'));
					}
				}
			}//if
			
		}//if
	}

	/**
	* Add the CSS & JavaScript files you want to be loaded in the BO.
	*/
	public function hookBackOfficeHeader()
	{
		$this->context->controller->addJS($this->_path.'js/back.js');
		$this->context->controller->addCSS($this->_path.'css/back.css');
	}

	/**
	 * Add the CSS & JavaScript files you want to be added on the FO.
	 */
	public function hookHeader()
	{
		$this->context->controller->addJS($this->_path.'/js/front.js');
		$this->context->controller->addCSS($this->_path.'/css/front.css');
	}

	public function hookDisplayFooter()
	{
		/* Place your code here. */
	}
}
