<?php

/**
 * My macros
 *
 * {menuItem ? ?} - <li n:class='$presenter->isCurrentLink("Homepage:*") ? active'><a n:href="Homepage:default">Test lists</a></li>
 * {n:id ?} <div n:id="cond ? one : two">
 * {n:confirm ?} - js confirm dialog
 * {n:tooltip ?} - into js tooltip
 * {n:src ?} - into <img src={$basePath}/images/ ... >
 * {n:current ?} - class="$presenter->isLinkCurrent() ? $node>
 * {empty} - ...
 * {n:phref ?} - $presenter->link($args)
 */

namespace Schmutzka\Templates;

use Nette;
use Nette\Latte\MacroNode;
use Nette\Latte\PhpWriter;

class Macros extends Nette\Latte\Macros\MacroSet
{

	public static function install(Nette\Latte\Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro("confirm", NULL, NULL, array($me, "macroConfirm"));
		$me->addMacro("menuItem", array($me, "macroMenuItem"));
		$me->addMacro("id", NULL, NULL, array($me, "macroId"));
		$me->addMacro("tooltip", NULL, NULL, array($me, "macroTooltip"));
		$me->addMacro("src", NULL, NULL, array($me, "macroSrc"));
		$me->addMacro("current", NULL, NULL, array($me, "macroCurrent"));
		$me->addMacro("not-empty", "ob_start()", 'if ($iterations) ob_end_flush(); else ob_end_clean()');
		$me->addMacro("empty", 'if (!$iterations):', "endif");
		$me->addMacro("phref", NULL, NULL, array($me, "macroPhref"));
	}


	/**
	 * n:phref="..."
	 */
	public function macroPhref(MacroNode $node, PhpWriter $writer)
	{
		return $writer->write('echo \' href="\' . %escape($_presenter->link(%node.word, %node.array?)) . \'"\'');

	}

	/**
	 * n:current="..."
	 */
	public function macroCurrent(MacroNode $node, PhpWriter $writer)
	{
		$node = $node->args;
		return $writer->write('if ($_l->tmp = array_filter(array($presenter->isLinkCurrent() ? "'. $node .'" :null))) echo \' class="\' . %escape(implode(" ", array_unique($_l->tmp))) . \'"\'');
	}


	/**
	 * n:src="..."
	 */
	public function macroSrc(MacroNode $node, PhpWriter $writer)
	{
		$replace = array(
			"{" => "\" . ",
			"}" => " . \"",
		);
		$node = strtr($node->args, $replace);
		
		return $writer->write('echo \' src="\' . %escape($basePath) . "/images/" . "'.$node.'" . \'"\'');
	}


	/**
	 * n:confirm="..." => onclick='confirm("...")';
	 */
	public function macroConfirm(MacroNode $node, PhpWriter $writer)
	{
		$node = $node->args;
		return $writer->write(' echo " onclick=\"return confirm(\'" . %escape($template->translate("' . $node . '")) . "\')\""');
	}


	/**
	 * n:tooltip="..." => rel='tooltip' title='$node'
	 */
	public function macroTooltip(MacroNode $node, PhpWriter $writer)
	{
		$node = $node->args;
		return $writer->write('echo " rel=\'tooltip\' title=\'" . %escape($template->translate("' . $node . '")) . " \'"');
	}


	/**
	 * n:id="..."
	 */
	public function macroId(MacroNode $node, PhpWriter $writer)
	{
		return $writer->write('if ($_l->tmp = array_filter(%node.array)) echo \' id="\' . %escape(implode(" ", array_unique($_l->tmp))) . \'"\'');
	}


	/**
	 * Macro for menu item
	 * @use: {menuItem Homepage:default, Test lists}
	 * @result: <li n:class='$presenter->isCurrentLink("Homepage:*") ? active'><a n:href="Homepage:default">Test lists</a></li>
	 * @use: {menuItem Homepage:default, Test lists, TRUE}
	 * @result: <li n:class='$presenter->isCurrentLink("Homepage:default") ? active'><a n:href="Homepage:default">Test lists</a></li>
	 * @note: dynamic and changing stuffs have to be written as a function in 'write' function
	 */
	public function macroMenuItem(MacroNode $node, PhpWriter $writer)
	{
		$args = explode(",", $node->args);

		if (count($args) == 2) {
			list($href, $name) = $args;

		} elseif (count($args) == 3) { // include specific view
			list($href, $name, $specificView) = $args;
		}

		$href = trim($href);
		$name = trim($name);

		$presenter = explode(":",$href);

		if (count($presenter) >= 3) { // module included
			$presenterCurrent = ":".$presenter[1].":".$presenter[2];

		} else {
			$presenterCurrent = array_shift($presenter);
		}

		if (isset($specificView)) {
			$presenterCurrent .= ":".array_pop($presenter);

		} else {
			$presenterCurrent .= ":*";	
		}
		
		$presenterCurrent = trim($presenterCurrent);

		return $writer->write('echo "<li ".%escape($_presenter->isLinkCurrent("'.$presenterCurrent.'") ? " class=active" : NULL)."><a href=".htmlSpecialChars($_control->link("'.$href.'", array("id" => NULL)))."> ". %escape($template->translate("'.$name.'")) . "</a></li>"');
	}

}