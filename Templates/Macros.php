<?php

namespace Schmutzka\Templates;

use Nette;
use Nette\Latte\MacroNode;
use Nette\Latte\PhpWriter;
use Nette\Latte\Compiler;
use Nette\Latte\Macros\MacroSet;


class Macros extends MacroSet
{

	/**
	 * @param  Compiler
	 */
	public static function install(Compiler $compiler)
	{
		$set = new static($compiler);
		$set->addMacro('phref', NULL, NULL, array($set, 'macroPhref'));
		$set->addMacro('current', NULL, NULL, array($set, 'macroCurrent'));
		$set->addMacro('src', NULL, NULL, array($set, 'macroSrc'));
		$set->addMacro('confirm', NULL, NULL, array($set, 'macroConfirm'));
		$set->addMacro('tooltip', NULL, NULL, array($set, 'macroTooltip'));
		$set->addMacro('id', NULL, NULL, array($set, 'macroId'));
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
		return $writer->write('echo " onclick=\"return confirm(\'" . %escape($template->translate("' . $node . '")) . "\')\""');
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
