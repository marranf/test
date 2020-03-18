<?php
if (!defined('_PS_VERSION_'))
  exit;
 
class Test extends Module
{
    private $_html = '';
    public function __construct()
    {
        $this->name = 'test';
        $this->tab = 'other';
        $this->version = '0.2.0';
        $this->author = 'marran';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap = true;
 
        parent::__construct();
 
        $this->displayName = $this->l('Test');
        $this->description = $this->l('Устанавливает диапазон цен для показа.');
 
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
 
        if (!Configuration::get('TESTMODULE_NAME'))
            $this->warning = $this->l('No name provided');
    }
    public function install()
    {
        if (Shop::isFeatureActive())
            Shop::setContext(Shop::CONTEXT_ALL);

        if (!parent::install() ||
            !$this->registerHook('leftColumn') ||
            !$this->registerHook('header') ||
            !$this->registerHook('footer') ||
            !$this->registerHook('home') ||
            !Configuration::updateValue('TESTMODULE_NAME', 'my friend')
        )
            return false;
        return true;
    }
    public function uninstall()
    {
        if (!parent::uninstall() ||
            !Configuration::deleteByName('TESTMODULE_NAME')
        )
        return false;
 
    return true;
    }
	public function hookFooter()
	{
        $query = 'select count(price) as count from ps_product where price >= '.Configuration::get('MIN_PRICE').' and price <= '.Configuration::get('MAX_PRICE').' limit 0, 1';

	    $res = Db::getInstance()->executeS($query);
         foreach ($res AS $row)
             $count = $row['count'];
	    $this->context->smarty->assign('count', $count);
	    $this->context->smarty->assign('price_min',Configuration::get("MIN_PRICE"));
        $this->context->smarty->assign('price_max',Configuration::get("MAX_PRICE"));
	    return $this->display(__FILE__, 'test.tpl');
	}
    private function _displayForm()
    {
        $this->_html .= '
	    <form action="' .$_SERVER['REQUEST_URI']. '" method="post">
		<fieldset>
			<legend>' .$this->l('Установка диапазона цен'). '</legend>
				<label for="min_price">' .$this->l('Цена от: '). '</label>
				<div class="margin-form">
					<input id="min_price" type="text" name="min_price" value="' .Tools::getValue('min_price', Configuration::get('MIN_PRICE')). '" />
					<p class="clear">' .$this->l('Цена от: '). '</p>	
				</div>
                <label for="max_price">' .$this->l('Цена до: '). '</label>
				<div class="margin-form">
				    <input id="max_price" type="text" name="max_price" value="' .Tools::getValue('max_price', Configuration::get('MAX_PRICE')). '" />
					<p class="clear">' .$this->l('Цена до: '). '</p>
                </div>	

				<p class="center">
					<input class="button" type="submit" name="submitTest" value="' .$this->l('Установить цены'). '"/>
				</p>
			</fieldset>
		</form>
	    ';
    }
    public function getContent()
    {
        //Обработка отправленной формы
        $this->_postProcess();
        //Создаем код формы
        $this->_displayForm();
        //Возвращаем отображаемое содержимое
        return $this->_html;
    }
    private function _postProcess()
    {
        //Проверяем отправлена ли форма
        if(Tools::isSubmit('submitTest'))
        {
            //Получаем значение поля формы
            $min_price=Tools::getValue('min_price');
            $max_price=Tools::getValue('max_price');
            //Проверяем валидность
            if($min_price != '' && $max_price != '')
            {
                //Сохраняем настройку
                Configuration::updateValue('MIN_PRICE', $min_price);
                Configuration::updateValue('MAX_PRICE',$max_price);
                //Выводим сообщение об успешном сохранении
                $this->_html .= $this->displayConfirmation($this->l('Диапазон цен установлен.'));
            } else
                //Выводим сообщение об ошибке
                $this->_html .= $this->displayError($this->l('Неправильные значения.'));
        }
    }

}