<?php

/**
 * @author Patrik Votoček
 */


/**
 * Translates the given string.
 * @param string $message
 * @return string
 */
function __($message)
{
	return Nette\Environment::getService('translator')->translate($message);
}


/**
 * Translates the given string with plural
 * @param string $single
 * @param string $plural 
 * @param int $muber plural form (positive number)
 * @return string
 */
function _n($single, $plural, $number)
{
	return Nette\Environment::getService('translator')->translate($single, array($plural, $number));
}


/**
 * Translates the given string with vsprintf.
 * @param string $message
 * @paran array $args for vsprintf 
 * @return string
 */
function _x($message, array $args)
{
	return Nette\Environment::getService('translator')->translate($message, NULL, $args);
}


/**
 * Translates the given string with plural and vsprintf
 * @param string $single
 * @param string $plural 
 * @param int $muber plural form (positive number)
 * @return string
 */
function _nx($single, $plural, $number, array $args)
{
	return Nette\Environment::getService('translator')->translate($single, array($plural, $number), $args);
}