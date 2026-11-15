<?php

class Harvest extends BaseModel {
    protected string $table = 'harvest';
    protected string $pk = 'harvest_id';
    protected string $alias = 'hv';

    public function fields(): array {
        return [
            'planting_id' => ['label' => 'Planting record', 'type' => 'select', 'lookup' => 'planting'],
            'harvest_date' => ['label' => 'Harvest date', 'type' => 'date', 'default' => date('Y-m-d')],
            'quantity_kg' => ['label' => 'Quantity (kg)', 'type' => 'number', 'min' => 0, 'max' => 99999999.99, 'step' => '0.01'],
            'selling_price_per_kg' => ['label' => 'Selling price per kg', 'type' => 'number', 'min' => 0, 'max' => 99999999.99, 'step' => '0.01'],
            'grade_id' => ['label' => 'Quality grade', 'type' => 'select', 'lookup' => 'quality_grade'],
            'remark_id' => ['label' => 'Remarks', 'type' => 'select', 'lookup' => 'harvest_remark'],
        ];
    }

    protected function selectSql(): string {
        return 'hv.harvest_id, hv.planting_id, fm.farm_name, cr.crop_name, cr.variety, pl.planting_date,
                hv.harvest_date, hv.quantity_kg, hv.selling_price_per_kg,
                (hv.quantity_kg * hv.selling_price_per_kg) AS total_value,
                hv.grade_id, qg.grade_name, hv.remark_id, hr.remark_description'
            . $this->auditSelect();
    }

    protected function fromSql(): string {
        return 'harvest hv
                JOIN planting pl ON pl.planting_id = hv.planting_id
                JOIN farm fm ON fm.farm_id = pl.farm_id
                JOIN crop cr ON cr.crop_id = pl.crop_id
                JOIN quality_grade qg ON qg.grade_id = hv.grade_id
                JOIN harvest_remark hr ON hr.remark_id = hv.remark_id'
            . $this->auditJoin();
    }

    protected function searchSql(): string {
        return "(fm.farm_name || ' ' || cr.crop_name || ' ' || cr.variety || ' ' || hr.remark_description)";
    }

    protected function filterSql(): array {
        return [
            'grade_id' => ['sql' => 'hv.grade_id = :grade_id', 'type' => 'int'],
            'remark_id' => ['sql' => 'hv.remark_id = :remark_id', 'type' => 'int'],
            'farm_id' => ['sql' => 'pl.farm_id = :farm_id', 'type' => 'int'],
            'crop_id' => ['sql' => 'pl.crop_id = :crop_id', 'type' => 'int'],
            'date_from' => ['sql' => 'hv.harvest_date >= :date_from', 'type' => 'date'],
            'date_to' => ['sql' => 'hv.harvest_date <= :date_to', 'type' => 'date'],
        ];
    }

    protected function sortMap(): array {
        return [
            'id' => 'hv.harvest_id',
            'farm' => 'fm.farm_name',
            'crop' => 'cr.crop_name',
            'date' => 'hv.harvest_date',
            'quantity' => 'hv.quantity_kg',
            'price' => 'hv.selling_price_per_kg',
            'value' => 'total_value',
            'grade' => 'qg.grade_name',
            'remark' => 'hr.remark_description',
        ];
    }

    public function crossCheck(array $clean, array &$errors, ?int $id): void {
        if (isset($clean['planting_id'], $clean['harvest_date']) && !isset($errors['planting_id']) && !isset($errors['harvest_date'])) {
            $stmt = $this->run('SELECT planting_date FROM planting WHERE planting_id = :id', [':id' => $clean['planting_id']]);
            $plantingDate = $stmt->fetchColumn();
            if ($plantingDate !== false && $clean['harvest_date'] < $plantingDate) {
                $errors['harvest_date'] = 'Harvest date cannot be earlier than the planting date (' . $plantingDate . ').';
            }
        }
    }
}
