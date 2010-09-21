<?php

/**
 * Example presenter for testing the suggesting input 
 * 
 * @uses BasePresenter
 * @package SugggestInput
 * @version 1.1.0
 * @copyright (c) 2009 Martin Pecka (Clevis)
 * @author Martin Pecka <martin.pecka@clevis.cz> 
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
        $form = new AppForm();

        //see further functions' definitions for suggester factories and 
        //action definitions

        //the simplest case - suggesting from an array
        $form->addSuggestInput('suggest1', "Simple ArraySuggester")

            //here is the link to the action that provides suggestions
            ->setSuggestLink($this->link('suggestAhoj'));



        //you can also use this as a simple input with a non-changing tooltip
        //which is displayed while the input is active
        $form->addSuggestInput('suggest2', "Using ConstantSuggester as tooltip")
            ->setSuggestLink($this->link('suggestConstant'))

            //and this way you set some JS options (defined in jquery.suggest.js)
            ->addJsOptions('itemsPerPage', 10)
            ->addJsOptions('noControl', true)
            ->addJsOptions('minchars', 0)
            ->addJsOptions('constant', true);



        //checking if the submitted value is one of the suggested ones
        $form->addSuggestInput('suggest3', "Suggested values check on form send")
            ->setSuggestLink($this->link('suggestAhoj'))

            //here is the suggester that is used, only required if you want to use the following validator
            ->setSuggester($this['ahojSuggester'])

            //this validation rule checks if the entered value is one of the suggested items
            //the FALSE at the end means we do not allow empty value as a value
            ->addRule(SuggestInput::SUGGESTED_ONLY, 'Vybraná možnost musí být jedna z nabízených', FALSE);



        //dibi suggester - suggest from a database table
        $form->addSuggestInput('suggest4', "DibiSuggester")
            ->setSuggestLink($this->link('suggestDibi'));

        $form->addSuggestInput('suggest5', 'Retrieving data through a signal')
            ->setSuggestLink($this->link('signalSuggest!'))
            ->addJsOptions('itemsPerPage', 10)
            ->addJsOptions('noControl', true)
            ->addJsOptions('minchars', 0)
            ->addJsOptions('constant', true)
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
        $this->terminate(new JsonResponse($this->matches));
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
        $this->terminate(new JsonResponse($this->matches));
    }

    /**
     * Set matching items to the constant items of the suggester
     * 
     * @param string $typedText Unused here
     *
     * @return void
     */
    public function actionSuggestDibi($typedText = '')
    {        
        $this->matches = $this['dibiSuggester']->getSuggestions($typedText);
    }

    /**
     * Send the suggested items in JSON 
     * 
     * @return void
     */
    public function renderSuggestDibi()
    {
        $this->terminate(new JsonResponse($this->matches));
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
        $this->matches = $this['constantSuggester']->getSuggestions(NULL);
        $this->terminate(new JsonResponse($this->matches));
    }

    /**
     * Create an example array suggester 
     * 
     * @return ISuggester
     */
    protected function createComponentAhojSuggester()
    {
        $suggester = new ArraySuggester();

        $data = array(
            'ahoj', 'ahojk', 'ahojky', 'bah', 'bahn', 'bahno', 'cíl', 'cíle', 'cílev', 'cílevě', 'cílevěd', 'cílevědo', 'cílevědom', 'cílevědomý', 
            'ahoj', 'ahojk', 'ahojky', 'bah', 'bahn', 'bahno', 'cíl', 'cíle', 'cílev', 'cílevě', 'cílevěd', 'cílevědo', 'cílevědom', 'cílevědomý', 
            'ahoj 1', 'ahojk', 'ahojky', 'bah', 'bahn', 'bahno', 'cíl', 'cíle', 'cílev', 'cílevě', 'cílevěd', 'cílevědo', 'cílevědom', 'cílevědomý', 
            'ahoj 2', 'ahojk', 'ahojky', 'bah', 'bahn', 'bahno', 'cíl', 'cíle', 'cílev', 'cílevě', 'cílevěd', 'cílevědo', 'cílevědom', 'cílevědomý', 
            'ahoj 3', 'ahojk', 'ahojky', 'bah', 'bahn', 'bahno', 'cíl', 'cíle', 'cílev', 'cílevě', 'cílevěd', 'cílevědo', 'cílevědom', 'cílevědomý', 
            'ěščřžýáíéúůďťň', 'ĚŠČŘŽÝÁÍÉÚŮĎŤŇ', 'ahoj ahoj', 'hola hou'
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
     * @return ISuggester
     */
    protected function createComponentConstantSuggester()
    {
        $suggester = new ConstantSuggester();
        $data = array('A great tooltip', 'Is on more lines!', 'Type "Expedice Mars 2009" in the last input!');
        return $suggester->setItems($data);
    }

    /**
     * Create an example dibi suggester 
     * 
     * @return void
     */
    protected function createComponentDibiSuggester()
    {
        $suggester = new DibiSuggester();
        return $suggester
            ->setTable('suggestions')
            ->setColumn('text')
            ->setWhere('[text] LIKE %s');
    }

}

/* ?> omitted intentionally */
