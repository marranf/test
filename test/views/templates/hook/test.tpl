<div id="testmodule_block" class="block">
<h6>Количество товаров в диапазоне цен от {$price_min} до {$price_max}</h6>
  <div class="block_content">
		{foreach from=$mess item=foo}
		<div class="item">{$foo}</div>
		{/foreach}
  </div>
</div>