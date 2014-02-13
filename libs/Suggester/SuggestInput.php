<?php

namespace Nette\Addons;

require_once dirname(__FILE__) . '/Suggesters/ISuggester.php';
include_once dirname(__FILE__) . '/Suggesters/ConstantSuggester.php';
include_once dirname(__FILE__) . '/Suggesters/ArraySuggester.php';
include_once dirname(__FILE__) . '/Suggesters/DbSuggester.php';

/**
 * A form text input that supports suggesting items based on the typed text
 *
 * Needs following Javascript libraries to be loaded (in the following order):
 * THIRD-PARTY - MIT licensed
 *  jQuery 1.10 http://jquery.com
 *  jQuery.bgiframe 3.0 (included in package) http://plugins.jquery.com/bgiframe
 * INTERNAL
 *  jQuery.suggest 1.2 (included in package) http://www.kcjitka.cz/data/suggestInput.zip
 * NETTE
 *  netteForms.js (part of Nette 2.0)
 *  suggestInput.js (part of the SuggestInput package)
 *
 * The CSS file provided with this package is supposed to be loaded, but you
 * can create your own
 *
 * @package Nette\Addons
 * @version 2.1
 * @copyright (c) 2013 Martin Pecka
 * @author Martin Pecka <peci1@seznam.cz> 
 * @license I mostly like BSD, but you can do everything you want with this 
 * library except of removing my name and the download link from the file 
 * (espacially you CAN use it free of charge in commercial applications) 
 *
 * @see SuggestInput.readme.txt for an exaple of use and a short tutorial
 */
class SuggestInput extends \Nette\Forms\Controls\TextInput
{

    /* To register the suggest input with Form - place following unstarred 
     * line eg. into your BasePresenter::__construct() or bootstrap.php
     *
     * Then you can use 
     *  $form = new Form();
     *  $form->addSuggestInput($name, $label, $suggestLink...);

     Nette\Forms\Form::extensionMethod('Nette\Forms\Form::addSuggestInput', 'Nette\Addons\SuggestInput::addSuggestInput');

    */

    /** @const string Valdation rule for allowing only those values, that can 
     * be suggested */
    const SUGGESTED_ONLY = ':suggestedOnly';

    /** @var string The code of the default language. This is the default passed to JS options as the 'lang' option. */
    public static $defaultUiLanguage = 'cs';

    /** @var string Relative or absolute link to the page that returns 
     * suggested items in JSON encoded array */
    protected $suggestLink = NULL;

    /** @var array Array of options given to the input (see 
     * jquery.suggest.js for possible options) */
    protected $jsOptions = array();

    /** @var ISuggester If you intend to use the suggestedOnly validator, 
     * you must provide a suggester of items */
    protected $suggester = NULL;

    /**
     * Construct a textinput and set suggestLink and suggester
     * 
     * @param string|NULL $label            The textual label of the control
     * @param string|NULL $suggestLink      Link to the page that returns 
     *                                      suggested items
     * @param ISuggester|NULL $suggester    The suggester of items
     * @param int|NULL $cols                If not null, declares the width of
     *                                      the input in number of chars that
     *                                      fit into
     * @param int|NULL $maxLength           If not null, declares the maximal
     *                                      length of the typed text
     * @return void
     */
    public function __construct($label = NULL, $suggestLink = NULL, SuggestInput\ISuggester $suggester = NULL, $cols = NULL, $maxLength = NULL)
    {
	parent::__construct($label, $cols, $maxLength);
	$this->suggestLink = $suggestLink;
	$this->suggester = $suggester;
    }

    /**
     * Returns the control's HTML
     *
     * Adds the javascript code for initializing the functionality
     * 
     * @return Html
     * @throws InvalidStateException If no suggest link is provided
     */
    public function getControl()
    {
        $control = parent::getControl();

        if ($this->suggestLink === NULL)
            throw new InvalidStateException('SuggustInput::$suggestLink cannot be NULL');

	$control->data('nette-suggestLink', $this->suggestLink);

	$options = $this->getJsOptions();
	if (!isset($options['lang']))
		$options['lang'] = self::$defaultUiLanguage;

	$options = substr(PHP_VERSION_ID >= 50400 ? 
		json_encode($options, JSON_UNESCAPED_UNICODE) : 
		json_encode($options), 1, -1);
	$options = preg_replace('#"([a-z0-9_]+)":#i', '$1:', $options);
	$options = preg_replace('#(?<!\\\\)"(?!:[^a-z])([^\\\\\',]*)"#i', "'$1'", $options);
	$control->data('nette-suggestInput-options', '{' . $options . '}');

	$control->class[] = 'suggestInput';

        return $control;
    }

    /**
     * Returns the link to the page, where the control can check suggested 
     * items 
     * 
     * @return string
     */
    public function getSuggestLink()
    {
        return $this->suggestLink;
    }

    /**
     * Sets the link to the page, where the control can check suggested 
     * items  
     * 
     * @param string $value Link to the page (!not a Nette link!, just URL)
     * @return Nette\Addons\SuggestInput Provides fluent interface
     */
    public function setSuggestLink($value)
    {
        $this->suggestLink = $value;
        return $this;
    }

    /**
     * Returns the suggester used for getting suggested items 
     * 
     * @return Nette\Addons\SuggestInput\ISuggester
     */
    public function getSuggester()
    {
        return $this->suggester;
    }

    /**
     * Sets the suggester used for getting suggested items
     * 
     * @param Nette\Addons\SuggestInput\ISuggester $suggester The suggester you want to use
     * @return Nette\Addons\SuggestInput Provides fluent interface
     */
    public function setSuggester(SuggestInput\ISuggester $suggester)
    {
        $this->suggester = $suggester;
        return $this;
    }

    /**
     * Returns the javascript options given to the input
     * 
     * @return array
     *
     * @see jquery.suggest.js for possible options
     */
    public function getJsOptions()
    {
        return $this->jsOptions;
    }

    /**
     * Sets the javascript options given to the input
     * 
     * @param array $options The options you want to set
     * @return Nette\Addons\SuggestInput Provides fluent interface
     */
    public function setJsOptions($options)
    {
        $this->jsOptions = (array)$options;
        return $this;
    }

    /**
     * Adds the javascript options given to the input
     * 
     * @param array|string $options The options you want to add or name
     *                              of the option whose value you provide
     *                              in $value
     * @param string|NULL $value If not null, says that you provided an 
     *                           option's name in $options and here you
     *                           specify its value
     * @return Nette\Addons\SuggestInput Provides fluent interface
     */
    public function addJsOptions($options, $value = NULL)
    {
        if (is_string($options) && $value !== NULL)
            $options = array($options => $value);

        $this->setJsOptions(array_merge($this->getJsOptions(), (array)$options));
        return $this;
    }

    /**
     * Adds the JS options required to use this input as a constant suggester.
     * 
     * @param int $itemsPerPage The number of lines in the tooltip.
     * @return Nette\Addons\SuggestInput Provides fluent interface
     */
    public function useAsConstantSuggester($itemsPerPage = 10)
    {
	 $this->addJsOptions('itemsPerPage', $itemsPerPage)
         	->addJsOptions('noControl', true)
         	->addJsOptions('minchars', 0)
	 	->addJsOptions('constant', true);

	 return $this;
    }

    /**
     * Validate, whether the control's value is one of the suggested items
     *
     * Called by Nette when the form is sent, if you use 
     * ->addRule(SuggestInput::SUGGESTED_ONLY, 'message')
     * Or you can call it manually if you don't use Nette
     * 
     * @param SuggestInput $control The control to check
     * @param bool $allowEmpty Allow empty value?
     * @return void
     *
     * @throws InvalidStateException if the suggester is not set
     */
    public static function validateSuggestedOnly(SuggestInput $control, $allowEmpty = TRUE)
    {
        //calling this function by call_user_func or by variable gives
        //optional parameters with NULL value :(
        if (!isset($allowEmpty))
            $allowEmpty = TRUE;

        if ($control->getValue() == '' && $allowEmpty)
            return TRUE;

        if ($control->getSuggester() === NULL)
            throw new InvalidStateException('When using SuggestInput::'.'
                SUGGESTED_ONLY validation rule, SuggestInput\'s suggester '.
                'cannot be NULL');

        return $control->getSuggester()->isSuggested($control->getValue());
    }

    /**
     * Assigns new suggest input to the given form
     *
     * Used only for Form::extensionMethod
     * 
     * @param Form $form            The form where the input should be added
     * @param string $name          Name of the control
     * @param string $label         Label of the control
     * @param string $suggestLink   URL to the page where the control can fetch
     *                              suggested item from
     * @param ISuggester $suggester Suggester used for the control (should be 
     *                              the same that is used in the above page)
     * @param int $cols             If not null, sets the width of the control
     * @param int $maxLength        If not null, sets the maximum length of the
     *                              control's value
     *
     * @return SuggestInput
     */
    public static function addSuggestInput(\Nette\Forms\Form $form, $name, $label, $suggestLink = NULL, SuggestInput\ISuggester $suggester = NULL, $cols = NULL, $maxLength = NULL)
    {
		return $form[$name] = new SuggestInput($label, $suggestLink, $suggester, $cols, $maxLength);
    }

}

/* ?> omitted intentionally */
