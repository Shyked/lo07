<?php

require_once '../myPDO.include.php';

// Classe qui génère le code HTML des input en material design
class Components {

	private static $component_count = 0;

	public static function text($params) {
		$name = $params['name'];
		$label = $params['label'];
		$required = isset($params['required']) ? $params['required'] : false;
		$default = isset($params['default']) ? $params['default'] : '';
		$id = self::getComponentId('text');
		$required = $required ? ' required' : '';
		return <<<HTML
		  <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
		    <input class="mdl-textfield__input" type="text" name="{$name}" id="{$id}" value="{$default}"{$required} />
		    <label class="mdl-textfield__label" for="{$id}">{$label}</label>
		  </div>
HTML;
	}

	public static function textWithIcon($params) {
		$name = $params['name'];
		$label = $params['label'];
		$icon = $params['icon'];
		$required = isset($params['required']) ? $params['required'] : false;
		$default = isset($params['default']) ? $params['default'] : '';
		$id = self::getComponentId('text');
		$required = $required ? ' required' : '';
		return <<<HTML
		  <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label lo07-input-with-icon">
		 	  <i class="material-icons">search</i>
		    <input class="mdl-textfield__input" type="text" name="{$name}" id="{$id}" value="{$default}"{$required} />
		    <label class="mdl-textfield__label" for="{$id}">{$label}</label>
		  </div>
HTML;
	}

	public static function number($params) {
		$name = $params['name'];
		$label = $params['label'];
		$required = isset($params['required']) ? $params['required'] : false;
		$default = isset($params['default']) ? $params['default'] : '';
		$id = self::getComponentId('number');
		$required = $required ? ' required' : '';
		return <<<HTML
		  <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
		    <input class="mdl-textfield__input" type="text" pattern="-?[0-9]*(\.[0-9]+)?" name="{$name}" id="{$id}" value="{$default}"{$required} />
		    <label class="mdl-textfield__label" for="{$id}">{$label}</label>
		    <span class="mdl-textfield__error"></span>
		  </div>
HTML;
	}

	public static function textarea($params) {
		$name = $params['name'];
		$label = $params['label'];
		$size = isset($params['size']) ? $params['size'] : 5;
		$required = isset($params['required']) ? $params['required'] : false;
		$default = isset($params['default']) ? $params['default'] : '';
		$id = self::getComponentId('textarea');
		$required = $required ? ' required' : '';
		return <<<HTML
		  <div class="mdl-textfield mdl-js-textfield">
		    <textarea class="mdl-textfield__input" type="text" rows="{$size}" name="{$name}" id="{$id}"{$required}>{$default}</textarea>
		    <label class="mdl-textfield__label" for="{$id}">{$label}</label>
		  </div>
HTML;
	}

	public static function select($params) {
		$name = $params['name'] . "_selectLabel";
		$label = $params['label'];
		$list = $params['list'];
		$associative = isset($params['associative']) ? $params['associative'] : false;
		$fullwidth = isset($params['fullwidth']) ? $params['fullwidth'] : false;
		$required = isset($params['required']) ? $params['required'] : false;
		$default = isset($params['default']) ? $params['default'] : '';
		$id = self::getComponentId('select');
		$required = $required ? ' required' : '';
		$list_html = "";
		$width = $fullwidth ? "getmdl-select__fullwidth" : "getmdl-select__fix-height";
		foreach ($list as $key => $val) {
			$value = $associative ? $key : $val;
			$list_html .= "<li class='mdl-menu__item' data-val='{$value}'>{$val}</li>";
		}
		return <<<HTML
			<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label getmdl-select {$width}">
			    <input class="mdl-textfield__input" type="text" name="{$name}" id="{$id}" value="{$default}" readonly tabIndex="-1"{$required} />
			    <label for="{$id}">
			        <i class="mdl-icon-toggle__label material-icons">keyboard_arrow_down</i>
			    </label>
			    <label for="{$id}" class="mdl-textfield__label">{$label}</label>
			    <ul for="{$id}" class="mdl-menu mdl-menu--bottom-left mdl-js-menu">
			        {$list_html}
			    </ul>
			</div>
HTML;
	}

	public static function checkbox($params) {
		$name = $params['name'];
		$label = $params['label'];
		$checked = isset($params['checked']) ? $params['checked'] : '';
		$id = self::getComponentId('checkbox');
		$checked = $checked ? ' checked' : '';
		return <<<HTML
			<label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" for="{$id}">
			  <input type="checkbox" name="{$name}" id="{$id}" class="mdl-checkbox__input"{$checked} />
			  <span class="mdl-checkbox__label">{$label}</span>
			</label>
HTML;
	}

	public static function radios($params) {
		$name = $params['name'];
		$list = $params['list'];
		$required = isset($params['required']) ? $params['required'] : false;
		$default = isset($params['default']) ? $params['default'] : '';
		$required = $required ? ' required' : '';
		$html = "";
		foreach ($list as $value => $label) {
			$id = self::getComponentId('radio');
			$checked = $value == $default ? ' checked' : '';
			$html .= <<<HTML
				<label class="mdl-radio mdl-js-radio mdl-js-ripple-effect mdl-cell--12-col-phone lo07-radio" for="{$id}">
				  <input type="radio" id="{$id}" class="mdl-radio__button" name="{$name}" value="{$value}"{$checked}{$required} />
				  <span class="mdl-radio__label">{$label}</span>
				</label>
HTML;
		}
		return $html;
	}

	public static function hidden($params) {
		$name = $params['name'];
		$default = isset($params['default']) ? $params['default'] : '';
		$id = self::getComponentId('hidden');
		return <<<HTML
			<input name="{$name}" type="hidden" value="{$default}" id="{$id}" />
HTML;
	}


	public static function getComponentId($type) {
		self::$component_count++;
		return 'component-' . $type . '-' . self::$component_count;
	}


}
