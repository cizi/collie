<?php

namespace App\AdminModule\Presenters;

use App\Forms\HealthEnumOrderForm;
use App\Model\Entity\HealthOrderEntity;
use App\Model\HealthOrderRepository;

class HealthEnumOrderPresenter extends SignPresenter {

    /** @var HealthEnumOrderForm */
    private $healthEnumOrderForm;

    /** @var HealthOrderRepository */
    private $healthOrderRepository;

    public function __construct(HealthEnumOrderForm $healthEnumOrderForm, HealthOrderRepository $healthOrderRepository)
    {
        parent::__construct();
        $this->healthEnumOrderForm = $healthEnumOrderForm;
        $this->healthOrderRepository = $healthOrderRepository;
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
        $healthOrderEntities = [];
        $orderIsUnique = true;
        foreach ($values as $order => $value) {
            if (isset($value['order']) && !empty(trim($value['order']))) {
                $orderIsUnique = !isset($healthOrderEntities[$value['order']]);
                $healthOrderEntity = new HealthOrderEntity();
                $healthOrderEntity->setEnumPoradi($order);
                $healthOrderEntity->setZobrazeniPoradi($value['order']);
                $healthOrderEntities[$value['order']] = $healthOrderEntity;
            }
            if (!$orderIsUnique) {
                $this->flashMessage(HEALTH_ORDER_NOT_UNIQUE, "alert-danger");
                break;
            }
        }

        if ($orderIsUnique && $this->healthOrderRepository->updateOrders($healthOrderEntities)) {
            $this->flashMessage(WEBCONFIG_WEB_SAVE_SUCCESS, "alert-success");
        } else {
            $this->flashMessage(REFEREE_SAVED_FAILED, "alert-danger");
        }
        $this->redirect("default");
    }
}
