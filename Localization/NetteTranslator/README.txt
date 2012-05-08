Nette Translator (c) Patrik Votoček (Vrtak-CZ), 2010 (http://patrik.votocek.cz)

Requirements
------------
Nette Framework 2.0-beta or higher. (PHP 5.3 edition)

Documentation and Examples
--------------------------
This is Gettext translator with editor. Editor is specia Nette Debug Bar panel.
Load languages from .mo file(s) and save changes with generates .mo & .po files.

Enable Translator
-----------------
Add this line to your config.neon:
translator:
	factory: NetteTranslator\Gettext::getTranslator

Add Files
---------
Add files in bootstrap.php or other file where you configurate application.
Nette\Environment::getService('translator')->addFile('%appDir%/AdminModule/lang', 'admin');

There must be at least one file added, otherwise please don't use NetteTranslator.

Enable Editor (panel)
---------------------
To enable panel add folowing code to your bootstrap.php or to the
file where you register your Gettext files (AFTER files registration!):
($container is instance of Nette\DI\IContainer)
NetteTranslator\Panel::register($container, $container->translator);

According to modules, if a translation file exists with the name of current module,
if will be automatically selected as default dictionary in Editor.

Translate String
----------------
Nette\Environment::getService('translator')->translate('This is translation text');
or plural version
Nette\Environment::getService('translator')
	->translate('This is translation text', array('This is transtaltion texts', 2));
or use shortcuts
__('This is translation text');
or plural version shortcuts
_n('This is translation text', 'This is transtaltion texts', 2);