<?php

use Nette\Addons\SuggestInput;
use Nette\Application\UI\Form;
use Nette\Application\Responses\JsonResponse;

/**
 * Example presenter for testing the SuggestInput component
 * 
 * @package SugggestInput
 * @version 2.0
 * @copyright (c) 2013 Martin Pecka
 * @author Martin Pecka <peci1@seznam.cz> 
 * @license I mostly like BSD, but you can do everything you want with this 
 * library except of removing my name and the download link from the file 
 * (espacially you CAN use it free of charge in commercial applications)  
 */
class SuggesterTestPresenter extends BasePresenter
{

    /** @var array Items matched for current query */
    protected $matches;

    /**
     * Create the form with some suggest inputs 
     * 
     * @return AppForm
     */
    protected function createComponentTestForm()
    {
        $form = new Form();

	//to set the default (Javascript) UI language, call the following (L10N strings are in jquery.suggest.js)
	//Nette\Addons\SuggestInput::$defaultUiLanguage = 'en';

        //see further functions' definitions for suggester factories and 
        //action definitions

        //the simplest case - suggesting from an array
        $form->addSuggestInput('suggest1', "Simple ArraySuggester (try eg. 'ahoj')")

            //here is the link to the action that provides suggestions
            ->setSuggestLink($this->link('suggestAhoj'));



        //you can also use this as a simple input with a non-changing tooltip
        //which is displayed while the input is active
        $form->addSuggestInput('suggest2', "Using ConstantSuggester as tooltip")
            ->setSuggestLink($this->link('suggestConstant'))

	    //use this input as constant suggester
	    ->useAsConstantSuggester()

	    //and this way you set some JS options (defined in jquery.suggest.js)
	    ->addJsOptions('lang', 'en');



        //checking if the submitted value is one of the suggested ones
        $form->addSuggestInput('suggest3', "Suggested values check on form send (try eg. 'ahoj')")
            ->setSuggestLink($this->link('suggestAhoj'))

            //here is the suggester that is used, only required if you want to use the following validator
            ->setSuggester($this['ahojSuggester'])

            //this validation rule checks if the entered value is one of the suggested items
            //the FALSE at the end means we do not allow empty value as a value
            ->addRule(SuggestInput::SUGGESTED_ONLY, 'Select a value from the suggested list', FALSE);



        //DB suggester - suggest from a database table
        $form->addSuggestInput('suggest4', "DbSuggester (try eg. 'Milan', 'pet')")
            ->setSuggestLink($this->link('suggestDb'));



        $form->addSuggestInput('suggest5', 'Retrieving data through a signal')
            ->setSuggestLink($this->link('signalSuggest!'))
            ->useAsConstantSuggester()
            ->addJsOptions('componentName', $this->getName()); //important when using signals



        $form->addSubmit('sub', 'Submit');

        return $form;
    }

    /**
     * Set matching items for current query given in typedText parameter 
     *
     * If you do not tend on separating the computing logic in action and
     * the drawing logic in render, you can move the render function body
     * here. This allows you to get rid of the $this->matches variable
     * 
     * @param string $typedText The text the user typed in the input
     *
     * @return void
     */
    public function actionSuggestAhoj($typedText = '')
    {        
        $this->matches = $this['ahojSuggester']->getSuggestions($typedText);
    }

    /**
     * Send the matching items in JSON 
     * 
     * @return void
     */
    public function renderSuggestAhoj()
    {
        $this->sendResponse(new JsonResponse($this->matches));
    }

    /**
     * Set matching items to the constant items of the suggester
     * 
     * @param string $typedText Unused here
     *
     * @return void
     */
    public function actionSuggestConstant($typedText = '')
    {        
        $this->matches = $this['constantSuggester']->getSuggestions(NULL);
    }

    /**
     * Send the suggested items in JSON 
     * 
     * @return void
     */
    public function renderSuggestConstant()
    {
        $this->sendResponse(new JsonResponse($this->matches));
    }

    /**
     * Set matching items to the constant items of the suggester
     * 
     * @param string $typedText Unused here
     *
     * @return void
     */
    public function actionSuggestDb($typedText = '')
    {        
        $this->matches = $this['dbSuggester']->getSuggestions($typedText);
    }

    /**
     * Send the suggested items in JSON 
     * 
     * @return void
     */
    public function renderSuggestDb()
    {
        $this->sendResponse(new JsonResponse($this->matches));
    }

    /**
     * Set matching items to the constant items of the suggester
     * 
     * @param string $typedText Unused here
     *
     * @return void
     */
    public function handleSignalSuggest($typedText = '')
    {        
        $matches = $this['constantSuggester']->getSuggestions(NULL);
        $this->sendResponse(new JsonResponse($matches));
    }

    /**
     * Create an example array suggester 
     * 
     * @return Nette\Addons\SuggestInput\ISuggester
     */
    protected function createComponentAhojSuggester()
    {
        $suggester = new Nette\Addons\SuggestInput\ArraySuggester();

        $data = array(
            'ahoj', 'ahojk', 'ahojky', 'bah', 'bahn', 'bahno', 'cíl', 'cíle', 'cílevě', 'cílevě', 'cílevěd', 'cílevědo', 'cílevědomý', 'cílevědomý', 
            'ahoj', 'ahojk', 'ahojky', 'bah', 'bahn', 'bahno', 'cíl', 'cíle', 'cílevě', 'cílevě', 'cílevěd', 'cílevědo', 'cílevědomý', 'cílevědomý', 
            'ahoj 1', 'ahojk', 'ahojky', 'bah', 'bahn', 'bahno', 'cíl', 'cíle', 'cílevě', 'cílevě', 'cílevěd', 'cílevědo', 'cílevědomý', 'cílevědomý', 
            'ahoj 2', 'ahojk', 'ahojky', 'bah', 'bahn', 'bahno', 'cíl', 'cíle', 'cílevě', 'cílevě', 'cílevěd', 'cílevědo', 'cílevědomý', 'cílevědomý', 
            'ahoj 3', 'ahojk', 'ahojky', 'bah', 'bahn', 'bahno', 'cíl', 'cíle', 'cílevě', 'cílevě', 'cílevěd', 'cílevědo', 'cílevědomý', 'cílevědomý', 
            'žščřcjďťŇ', 'ŽŠČŘĎŤň', 'ahoj ahoj', 'hola hou'
        );
        $suggester
            ->setItems($data)
            //setting this will match only items beginning with query string
            //->matchFromBeginning(TRUE)
            ;

        return $suggester;
    }

    /**
     * Create an example constant suggester 
     * 
     * @return Nette\Addons\SuggestInput\ISuggester
     */
    protected function createComponentConstantSuggester()
    {
        $suggester = new Nette\Addons\SuggestInput\ConstantSuggester();
        $data = array('A great tooltip', 'Is on more lines!', 'Type "Expedice Mars 2009" in the last input!');
        return $suggester->setItems($data);
    }

    private $dbConnection;
    public function injectDbConnection(\Nette\Database\Context $connection)
    {
        $this->dbConnection = $connection;
    }

    /**
     * Create an example DB-sourced suggester
     *
     * @return Nette\Addons\SuggestInput\ISuggester
     */
    protected function createComponentDbSuggester()
    {
        $suggester = new Nette\Addons\SuggestInput\DbSuggester($this->dbConnection);
        return $suggester
            ->setTable('organizers')
            ->setColumn('name')
            ->setWhere('`name` LIKE ?');
    }
}

/* ?> omitted intentionally */
