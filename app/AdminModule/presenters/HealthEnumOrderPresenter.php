<?php

namespace App\AdminModule\Presenters;

use App\Forms\HealthEnumOrderForm;

class HealthEnumOrderPresenter extends SignPresenter {

    private $healthEnumOrderForm;

    public function __construct(HealthEnumOrderForm $healthEnumOrderForm)
    {
        parent::__construct();
        $this->healthEnumOrderForm = $healthEnumOrderForm;
    }

    public function createComponentHealthEnumOrderForm()
    {
        $form = $this->healthEnumOrderForm->create($this->langRepository->getCurrentLang($this->session));
        $form->onSuccess[] = [$this, 'saveHealthOrder'];

        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = 'div class=form-group';
        $renderer->wrappers['pair']['.error'] = 'has-error';
        $renderer->wrappers['control']['container'] = 'div class=col-md-3';
        $renderer->wrappers['label']['container'] = 'div class="col-md-3 control-label"';
        $renderer->wrappers['control']['description'] = 'span class=help-block';
        $renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';
        //$form->getElementPrototype()->class('form-horizontal');

        return $form;
    }

    public function saveHealthOrder($form, $values)
    {
        dump($values);
    }
}