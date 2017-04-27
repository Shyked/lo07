<?php

require_once '../myPDO.include.php';

class Components {

	private static $component_count = 0;

	public static function text($name, $label, $default = '') {
		$id = self::getComponentId('text');
		return <<<HTML
		  <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
		    <input class="mdl-textfield__input" type="text" name="{$name}" id="{$id}" value="{$default}" />
		    <label class="mdl-textfield__label" for="{$id}">{$label}</label>
		  </div>
HTML;
	}

	public static function number($name, $label, $default = '') {
		$id = self::getComponentId('number');
		return <<<HTML
		  <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
		    <input class="mdl-textfield__input" type="text" pattern="-?[0-9]*(\.[0-9]+)?" name="{$name}" id="{$id}" value="{$default}" />
		    <label class="mdl-textfield__label" for="{$id}">{$label}</label>
		    <span class="mdl-textfield__error"></span>
		  </div>
HTML;
	}

	public static function textarea($name, $label, $size = 5, $default = '') {
		$id = self::getComponentId('textarea');
		return <<<HTML
		  <div class="mdl-textfield mdl-js-textfield">
		    <textarea class="mdl-textfield__input" type="text" rows="{$size}" name="{$name}" id="{$id}">{$default}</textarea>
		    <label class="mdl-textfield__label" for="{$id}">{$label}</label>
		  </div>
HTML;
	}

	public static function select($name, $label, $list, $default = '') {
		$id = self::getComponentId('select');
		$list_html = "";
		foreach ($list as $key => $val) {
			$list_html .= "<li class='mdl-menu__item'>{$val}</li>";
		}
		return <<<HTML
			<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label getmdl-select getmdl-select__fix-height">
			    <input class="mdl-textfield__input" type="text" name="{$name}" id="{$id}" value="{$default}" readonly tabIndex="-1" />
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

	public static function checkbox($name, $label, $checked = false) {
		$id = self::getComponentId('checkbox');
		$checked = $checked ? ' checked' : '';
		return <<<HTML
			<label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" for="{$id}">
			  <input type="checkbox" name="{$name}" id="{$id}" class="mdl-checkbox__input"{$checked} />
			  <span class="mdl-checkbox__label">{$label}</span>
			</label>
HTML;
	}

	public static function radios($name, $list, $default = '') {
		$html = "";
		foreach ($list as $value => $label) {
			$id = self::getComponentId('radio');
			$checked = $value == $default ? ' checked' : '';
			$html .= <<<HTML
				<label class="mdl-radio mdl-js-radio mdl-js-ripple-effect mdl-cell--12-col-phone lo07-radio" for="{$id}">
				  <input type="radio" id="{$id}" class="mdl-radio__button" name="{$name}" value="{$value}"{$checked} />
				  <span class="mdl-radio__label">{$label}</span>
				</label>
HTML;
		}
		return $html;
	}


	public static function getComponentId($type) {
		self::$component_count++;
		return 'component-' . $type . '-' . self::$component_count;
	}


}
