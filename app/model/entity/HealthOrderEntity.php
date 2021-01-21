<?php

namespace App\Model\Entity;

class HealthOrderEntity
{

    /** @var int */
    private $enum_poradi;

    /** @var int */
    private $zobrazeni_poradi;

    /**
     * @return int
     */
    public function getEnumPoradi(): int
    {
        return $this->enum_poradi;
    }

    /**
     * @param int $enum_poradi
     */
    public function setEnumPoradi(int $enum_poradi)
    {
        $this->enum_poradi = $enum_poradi;
    }

    /**
     * @return int
     */
    public function getZobrazeniPoradi(): int
    {
        return $this->zobrazeni_poradi;
    }

    /**
     * @param int $zobrazeni_poradi
     */
    public function setZobrazeniPoradi(int $zobrazeni_poradi)
    {
        $this->zobrazeni_poradi = $zobrazeni_poradi;
    }

    /**
     * @return int[]
     */
    public function extract()
    {
        return [
            'enum_poradi' => $this->getEnumPoradi(),
            'zobrazeni_poradi' => $this->getZobrazeniPoradi(),
        ];
    }
}
