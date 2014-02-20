<?php

use Nette\Addons\SuggestInput;
use Nette\Application\UI\Form;
use Nette\Application\Responses\JsonResponse;

/**
 * Example presenter for testing the SuggestInput component
 * 
 * @package SugggestInput
 * @version 2.1
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

		$form->addText('suggestid');

        //DB suggester - suggest from a database table
        $form->addSuggestInput('suggest4', "DbSuggester (try eg. 'Milan', 'pet')")
            ->setSuggestLink($this->link('suggestDb'))
			->addJsOptions("minchars", 1)
			->setIdField($form['suggestid']);

        $form->addSubmit('sub', 'Submit');

        return $form;
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
            ->setTable('firmy')
            ->setiDColumn('id')
            ->setColumn('nazov')
            ->setWhere('`nazov` LIKE ?');
    }
}

/* ?> omitted intentionally */
