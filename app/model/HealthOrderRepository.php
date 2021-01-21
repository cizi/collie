<?php

namespace App\Model;

use App\Model\Entity\HealthOrderEntity;

class HealthOrderRepository  extends BaseRepository {

    /**
     * @return int
     */
    public function getMaxOrder() {
        try {
            $query = ["select max(`zobrazeni_poradi`) from appdata_poradi_vysetreni"];
            return $this->connection->query($query)->fetchSingle() ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * @return array
     * @throws \Dibi\Exception
     */
    public function findOrders() {
        $query = ["select * from appdata_poradi_vysetreni"];
        $result = $this->connection->query($query);
        return $result->fetchPairs('enum_poradi', 'zobrazeni_poradi');
    }

    /**
     * @param array $healthOrderEntities
     * @return bool
     * @throws \Dibi\Exception
     */
    public function updateOrders(array $healthOrderEntities) {
        $result = true;
        $this->connection->begin();
        try {
            $this->connection->query("DELETE FROM appdata_poradi_vysetreni");
            /** @var HealthOrderEntity $healthOrderEntity */
            foreach ($healthOrderEntities as $healthOrderEntity) {
                $query = ["insert into appdata_poradi_vysetreni", $healthOrderEntity->extract()];
                $this->connection->query($query);
            }

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollback();
            $result = false;
        }

        return $result;
    }
}
