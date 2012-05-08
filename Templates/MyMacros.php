<?php

/**
 * My macros
 *
 * {menuItem ? ?} <li n:class='$presenter->isCurrentLink("Homepage:*") ? active'><a n:href="Homepage:default">Test lists</a></li>
 * {n:id ?} <div n:id="cond ? one : two">
 * {ga ?} - google analytics code
 * {n:confirm ?} - js confirm dialog
 * {n:tooltip ?} - into  
 */

namespace Schmutzka\Templates;

use Nette\Latte\MacroNode,
	Nette\Latte\PhpWriter,
	Nette\Utils\Html;

class MyMacros extends \Nette\Latte\Macros\MacroSet
{
	public static function install(\Nette\Latte\Compiler $compiler)
    {
		$me = new static($compiler);
		$me->addMacro("confirm", NULL, NULL, array($me, "macroConfirm"));
		$me->addMacro("ga", array($me, "macroGa"));
		$me->addMacro("menuItem", array($me, "macroMenuItem"));
		$me->addMacro("id", NULL, NULL, array($me, "macroId"));
		$me->addMacro("tooltip", NULL, NULL, array($me, "macroTooltip"));
	}


	/**
	 * n:confirm="..." => onclick='confirm("...")';
	 */
	public function macroConfirm(MacroNode $node, PhpWriter $writer)
	{
		$node = $node->args;
		return $writer->write(' echo "onclick=\"return confirm(\'" . %escape($template->translate("' . $node . '")) . "\')\""');
	}


	/**
	 * n:tooltip="..." => rel='tooltip' title='$node'
	 */
	public function macroTooltip(MacroNode $node, PhpWriter $writer)
	{
		$node = $node->args;
		return $writer->write('echo "rel=\'tooltip\' title=\'" . %escape($template->translate("' . $node . '")) . " \'"');
	}


	/**
	 * Macro for google analytics code
	 * @param string unique code
	 * @param bool multiple subdomains
	 */
	public function macroGa(MacroNode $node, PhpWriter $writer)
	{
		$args = explode(",", $node->args);
		$code = $args[0];
		$subdomains = (isset($args[1]) ? $args[1] : NULL);
		
		$node = Html::el("script")->setText("
			var _gaq = _gaq || [];
			_gaq.push(['_setAccount', '" . $code . "']);" .
			($subdomains ?  "_gaq.push(['_setDomainName', '".$subdomains."']);" : NULL) . 
			"_gaq.push(['_trackPageview']);

			(function() {
				var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
				ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
				var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
			})();");

		return $writer->write('echo "'.$node .'"');
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
		if(count($args) == 2) {
			list($href, $name) = $args;
		}
		elseif(count($args) == 3) { // include specific view
			list($href, $name, $specificView) = $args;
		}

		$href = trim($href);
		$name = trim($name);

		$presenter = explode(":",$href);

		if(count($presenter) >= 3) { // module included
			$presenterCurrent = ":".$presenter[1].":".$presenter[2];
		}	
		else {
			$presenterCurrent = array_shift($presenter);
		}

		if(isset($specificView)) {
			$presenterCurrent .= ":".array_pop($presenter);
		}
		else {
			$presenterCurrent .= ":*";	
		}
		
		$presenterCurrent = trim($presenterCurrent);

		return $writer->write('echo "<li ".%escape($_presenter->isLinkCurrent("'.$presenterCurrent.'") ? " class=active" : NULL)."><a href=".htmlSpecialChars($_control->link("'.$href.'")).">'.$name.'</a></li>"');
	}
}