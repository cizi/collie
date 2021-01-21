<?php

namespace App\Forms;

use App\Model\Entity\EnumerationEntity;
use App\Model\Entity\EnumerationItemEntity;
use App\Model\EnumerationRepository;
use App\Model\HealthOrderRepository;
use Nette;
use Nette\Application\UI\Form;

class HealthEnumOrderForm
{

    use Nette\SmartObject;

    /** @var FormFactory */
    private $factory;

    /** @var EnumerationRepository */
    private $enumerationRepository;

    /** @var HealthOrderRepository */
    private $healthOrderRepository;

    /**
     * @param FormFactory $factory
     * @param EnumerationRepository $enumerationRepository
     * @param HealthOrderRepository $healthOrderRepository
     */
    public function __construct(FormFactory $factory, EnumerationRepository $enumerationRepository, HealthOrderRepository $healthOrderRepository)
    {
        $this->factory = $factory;
        $this->enumerationRepository = $enumerationRepository;
        $this->healthOrderRepository = $healthOrderRepository;
    }

    /**
     * @param array $languages
     * @param int $level
     * @return Form
     */
    public function create($currentLang)
    {
        $counter = 0;
        $orderValue = $this->healthOrderRepository->getMaxOrder();
        $form = $this->factory->create();

        $savedOrder = $this->healthOrderRepository->findOrders();
        $zdravi = $this->enumerationRepository->findEnumItems($currentLang, 14);
        /** @var EnumerationItemEntity $enum */
        foreach ($zdravi as $enum) {
            $healthContainer = $form->addContainer($enum->getOrder());
            $healthContainer->addText('item', ENUM_EDIT_ITEM_TITLE)
                ->setDefaultValue($enum->getItem())
                ->setAttribute("class", "form-control")
                ->setAttribute("readonly", "readonly")
                ->setAttribute("tabindex", $counter++);;

            $orderForm = isset($savedOrder[$enum->getOrder()]) ? $savedOrder[$enum->getOrder()] : ++$orderValue;
            $healthContainer->addText('order', SHOW_DOG_FORM_DOG_ORDER)
                ->setAttribute("class", "form-control")
                ->setType('number')
                ->setAttribute("tabindex", $counter++)
                ->setDefaultValue(false ?: $orderForm);
        }
        $form->addSubmit('submit', USER_EDIT_SAVE_BTN_LABEL)
            ->setAttribute("class", "btn btn-primary menuItem alignRight")
            ->setAttribute("style", "float: right;")
            ->setAttribute("tabindex", $counter++);

        return $form;
    }
}
