<?php
namespace Mocha\Html;

class Form {
	public static $model = [];

	public static function setModel($data) {
		if (is_array($data))
			self::$model = $data;
		else if (is_object($data)) {
			foreach ($data as $k => $v) {
				if ($k[0] == '_') continue;
				self::$model[$k] = $v;
			}
		}
	}

	public static function checkbox($name, $label = null, $value = null, array $attributes = []) {
		if (isset(self::$model[$name]) && self::$model[$name] == $value)
			$attributes['checked'] = 'checked';
		$id = preg_replace('/^[a-zA-Z0-9_]/', '_', $name . '_' . $value);
		$label = $label != null ? " <label for=\"$id\">$label</label>" : '';
		return sprintf('<input type="checkbox" id="%s" name="%s" value="%s" %s/>%s',
			$id, $name, $value, self::formatAttributes($attributes), $label);
	}

	public static function hidden($name, array $attributes = []) {
		return sprintf('<input type="hidden" name="%s" value="%s" %s/>',
			$name, isset(self::$model[$name]) ? self::$model[$name] : '', self::formatAttributes($attributes));
	}

	public static function text($name, array $attributes = []) {
		return sprintf('<input type="text" name="%s" value="%s" %s/>',
			$name, isset(self::$model[$name]) ? self::$model[$name] : '', self::formatAttributes($attributes));
	}

	public static function select($name, $options = null, $valueOnly = false, $attributes = []) {
		$selected = isset(self::$model[$name]) ? self::$model[$name] : '';

		if ($options == null) {
			if (isset(self::$model[$name.'_options']))
				$options = self::$model[$name.'_options'];
			else
				$options = [];
		} else if (is_callable($options))
			$options = call_user_func($options);

		$html = sprintf('<select name="%s"%s>', $name, self::formatAttributes($attributes));
		foreach ($options as $key => $val)
			if (is_array($val)) {
				$html .= "<optgroup label=\"$key\">";
				foreach ($val as $skey => $sval)
					$html .= sprintf('<option value="%s"%s>%s</option>',
						htmlspecialchars($valueOnly ? $sval : $skey),
						(($valueOnly ? $sval : $skey) == $selected ? ' selected' : ''), htmlspecialchars($sval));
				$html .= '</optgroup>';
			} else {
				$html .= sprintf('<option value="%s"%s>%s</option>',
					htmlspecialchars($valueOnly ? $val : $key),
					(($valueOnly ? $val : $key) == $selected ? ' selected' : ''), htmlspecialchars($val));
			}
		return $html.'</select>';
	}

	public static function submit($value, array $attributes = []) {
		return sprintf('<button type="submit" %s>%s</button>', self::formatAttributes($attributes), $value);
	}

	public static function ok($value = 'OK') {
		return self::button($value, null, ['type' => 'submit', 'class' => 'btn btn-primary']);
	}

	public static function cancel($url) {
		return self::button('Cancel', $url);
	}

	public static function button($value, $uri = null, $params = []) {
		$attr = [];
		foreach ($params + ['type' => 'button', 'class' => 'btn btn-default'] as $k => $v)
			$attr[] = sprintf('%s="%s"', $k, $v);
		$attr = implode(' ', $attr);
		$html = sprintf('<button %s>%s</button>', $attr, $value);
		if ($uri) {
			$html = '<a href="'.$uri.'">'.$html."</a>";
		}
		return $html;
	}

	public static function textarea($name, $x = 100, $y = 4, array $attributes = []) {
		return sprintf('<textarea name="%s" cols="%d" rows="%d" %s>%s</textarea>',
			$name, $x, $y, self::formatAttributes($attributes), isset(self::$model[$name]) ? self::$model[$name] : '');
	}

	static function formatAttributes(array $attributes) {
		if (!$attributes)
			return '';
		$attr = [];
		foreach ($attributes as $k => $v)
			$attr[] = sprintf('%s="%s"', $k, htmlspecialchars($v));
		return implode(' ', $attr);
	}
}