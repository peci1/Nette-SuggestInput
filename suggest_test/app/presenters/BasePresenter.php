<?php

/**
 * The base of all presenters 
 * 
 * @package expedicemars
 * @version $id$
 * @copyright (c) 2009 Martin Pecka (Clevis)
 * @author Martin Pecka <martin.pecka@clevis.cz> 
 * @license 
 */
abstract class BasePresenter extends /*Nette\Application\*/Presenter
{

    /**
     * Handles the requested action and prepares view
     *
     * In this method you can validate input parameters, you can redirect
     * (it is sure no output has been written yet) and you should choose
     * which view to use for rendering the page (and set the $view to the 
     * view's name, or do not set it to use the default view).
     * The Default in actionDefault is name of the action we have to do. 
     * You get the $action variable filled by the router.
     * You can add some parameters to this method, then they will be required
     * from the router (eg. actionDefault($id) requires id param from the 
     * router)
     *
     * @remarks Must be public
     * 
     * @return void
     */
    public function actionDefault()
    {

    }

    /**
     * Render the view Default
     *
     * Set view-specific template variables
     * The Default in renderDefault stands for view name. The view to use is 
     * set in $this->view variable
     * 
     * @remarks Must be public
     *
     * @return void
     */
    public function renderDefault()
    {

    }

}

/* ?> omitted intentionally */
