<?php

require_once dirname(__FILE__) . '/Suggesters/ISuggester.php';
include_once dirname(__FILE__) . '/Suggesters/ConstantSuggester.php';
include_once dirname(__FILE__) . '/Suggesters/ArraySuggester.php';
include_once dirname(__FILE__) . '/Suggesters/DibiSuggester.php';

/**
 * A form text input that supports suggesting items based on the typed text
 *
 * Needs following Javascript libraries to be loaded:
 * THIRD-PARTY - mostly GPL || MIT licensed
 *  jQuery http://jquery.com
 *  jQuery.dimensions (included in package; only for jQuery <1.8) http://plugins.jquery.com/project/dimensions
 *  jQuery.bgiframe.min (included in package) http://plugins.jquery.com/node/46/release
 * INTERNAL
 *  jQuery.suggest 1.3 (included in package) http://www.kcjitka.cz/data/suggestInput.zip
 *
 * The css file provided with this package is supposed to be loaded, but you
 * can create your own
 *
 * @package Nette\Forms
 * @version 1.1.1
 * @copyright (c) 2013 Martin Pecka
 * @author Martin Pecka <peci1@seznam.cz> 
 * @license I mostly like BSD, but you can do everything you want with this 
 * library except of removing my name and the download link from the file 
 * (espacially you CAN use it free of charge in commercial applications) 
 *
 * @see SuggestInput.readme.txt for an exaple of use and a short tutorial
 */
class /*Nette\Forms\*/SuggestInput extends /*Nette\Forms\*/TextInput
{

    /* To register the suggest input with Form - place following unstarred 
     * line eg. into your BasePresenter::__construct() or bootstrap.php
     *
     * Then you can use 
     *  $form = new Form();
     *  $form->addSuggestInput($name, $label, $suggestLink...);

     Form::extensionMethod('Form::addSuggestInput', 'SuggestInput::addSuggestInput');
     or Form::extensionMethod('addSuggestInput', 'SuggestInput::addSuggestInput'); in PHP 5.3

    */

    /** @const string Valdation rule for allowing only those values, that can 
     * be suggested */
    const SUGGESTED_ONLY = ':suggestedOnly';

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
	public function __construct($label = NULL, $suggestLink = NULL, ISuggester $suggester = NULL, $cols = NULL, $maxLength = NULL)
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

        $script = sprintf(
            'jQuery(function() {
                jQuery("#%s").suggest(\'%s\',%s);
            });',
            $this->getHtmlId(),
            $this->suggestLink,
            json_encode($this->getJsOptions())
        );

        $div = Html::el('div');
        $js = Html::el('script')
            ->setText($script)
            ->setType('text/javascript');

        $div->add($js);
        $div->add($control);

        return $div;
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
     * @return SuggestInput Provides fluent interface
     */
    public function setSuggestLink($value)
    {
        $this->suggestLink = $value;
        return $this;
    }

    /**
     * Returns the suggester used for getting suggested items 
     * 
     * @return ISuggester
     */
    public function getSuggester()
    {
        return $this->suggester;
    }

    /**
     * Sets the suggester used for getting suggested items
     * 
     * @param ISuggester $suggester The suggester you want to use
     * @return SuggestInput Provides fluent interface
     */
    public function setSuggester(ISuggester $suggester)
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
     * @return SuggestInput Provides fluent interface
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
     * @return SuggestInput Provides fluent interface
     */
    public function addJsOptions($options, $value = NULL)
    {
        if (is_string($options) && $value !== NULL)
            $options = array($options => $value);

        $this->setJsOptions(array_merge($this->getJsOptions(), (array)$options));
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
    public static function addSuggestInput(Form $form, $name, $label, $suggestLink = NULL, ISuggester $suggester = NULL, $cols = NULL, $maxLength = NULL)
    {
		return $form[$name] = new SuggestInput($label, $suggestLink, $suggester, $cols, $maxLength);
    }

}

/* ?> omitted intentionally */
