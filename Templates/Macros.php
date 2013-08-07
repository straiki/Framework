<?php

/**
 * My macros
 *
 * {n:id ?} <div n:id='cond ? one : two'>
 * {n:confirm ?} - js confirm dialog
 * {n:tooltip ?} - into js tooltip
 * {n:src ?} - into <img src={$basePath}/images/ ... >
 * {n:current ?} - class='$presenter->isLinkCurrent() ? $node>
 * {empty} - ...
 * {n:phref ?} - $presenter->link($args)
 */

namespace Schmutzka\Templates;

use Nette;
use Nette\Latte\MacroNode;
use Nette\Latte\PhpWriter;
use Nette\Latte\Compiler;
use Nette\Latte\Macros\MacroSet;


class Macros extends MacroSet
{

	public static function install(Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('confirm', NULL, NULL, array($me, 'macroConfirm'));
		$me->addMacro('id', NULL, NULL, array($me, 'macroId'));
		$me->addMacro('tooltip', NULL, NULL, array($me, 'macroTooltip'));
		$me->addMacro('src', NULL, NULL, array($me, 'macroSrc'));
		$me->addMacro('current', NULL, NULL, array($me, 'macroCurrent'));
		$me->addMacro('not-empty', 'ob_start()', 'if ($iterations) ob_end_flush(); else ob_end_clean()');
		$me->addMacro('empty', 'if (!$iterations):', 'endif');
		$me->addMacro('phref', NULL, NULL, array($me, 'macroPhref'));
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
			'{' => '\" . ',
			'}' => ' . \"',
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

}
