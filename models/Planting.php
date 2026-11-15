<?php

class Planting extends BaseModel {
    protected string $table = 'planting';
    protected string $pk = 'planting_id';
    protected string $alias = 'pl';

    public function fields(): array {
        return [
            'farm_id' => ['label' => 'Farm', 'type' => 'select', 'lookup' => 'farm'],
            'crop_id' => ['label' => 'Crop', 'type' => 'select', 'lookup' => 'crop'],
            'planting_date' => ['label' => 'Planting date', 'type' => 'date', 'default' => date('Y-m-d')],
            'area_planted' => ['label' => 'Area planted (hectares)', 'type' => 'number', 'exclusive_min' => 0, 'max' => 999999.99, 'step' => '0.01'],
            'seed_quantity' => ['label' => 'Seed quantity', 'type' => 'number', 'min' => 0, 'max' => 999999.99, 'step' => '0.01'],
            'expected_harvest_date' => ['label' => 'Expected harvest date', 'type' => 'date'],
            'status_id' => ['label' => 'Status', 'type' => 'select', 'lookup' => 'planting_status'],
        ];
    }

    protected function selectSql(): string {
        return "pl.planting_id, pl.farm_id, fm.farm_name, fr.first_name || ' ' || fr.last_name AS farmer_name,
                pl.crop_id, cr.crop_name, cr.variety, pl.planting_date, pl.area_planted, pl.seed_quantity,
                pl.expected_harvest_date, pl.status_id, ps.status_name,
                (SELECT COUNT(*) FROM harvest x WHERE x.planting_id = pl.planting_id) AS harvest_count"
            . $this->auditSelect();
    }

    protected function fromSql(): string {
        return 'planting pl
                JOIN farm fm ON fm.farm_id = pl.farm_id
                JOIN farmer fr ON fr.farmer_id = fm.farmer_id
                JOIN crop cr ON cr.crop_id = pl.crop_id
                JOIN planting_status ps ON ps.status_id = pl.status_id'
            . $this->auditJoin();
    }

    protected function searchSql(): string {
        return "(fm.farm_name || ' ' || cr.crop_name || ' ' || cr.variety || ' ' || fr.first_name || ' ' || fr.last_name)";
    }

    protected function filterSql(): array {
        return [
            'status_id' => ['sql' => 'pl.status_id = :status_id', 'type' => 'int'],
            'farm_id' => ['sql' => 'pl.farm_id = :farm_id', 'type' => 'int'],
            'crop_id' => ['sql' => 'pl.crop_id = :crop_id', 'type' => 'int'],
            'date_from' => ['sql' => 'pl.planting_date >= :date_from', 'type' => 'date'],
            'date_to' => ['sql' => 'pl.planting_date <= :date_to', 'type' => 'date'],
        ];
    }

    protected function sortMap(): array {
        return [
            'id' => 'pl.planting_id',
            'farm' => 'fm.farm_name',
            'crop' => 'cr.crop_name',
            'planted' => 'pl.planting_date',
            'area' => 'pl.area_planted',
            'seed' => 'pl.seed_quantity',
            'expected' => 'pl.expected_harvest_date',
            'status' => 'ps.status_name',
        ];
    }

    public function crossCheck(array $clean, array &$errors, ?int $id): void {
        if (isset($clean['planting_date'], $clean['expected_harvest_date'])
            && $clean['expected_harvest_date'] < $clean['planting_date']) {
            $errors['expected_harvest_date'] = 'Expected harvest date cannot be earlier than the planting date.';
        }
        if (isset($clean['farm_id'], $clean['area_planted']) && !isset($errors['farm_id']) && !isset($errors['area_planted'])) {
            $stmt = $this->run('SELECT area_hectares FROM farm WHERE farm_id = :id', [':id' => $clean['farm_id']]);
            $farmArea = $stmt->fetchColumn();
            if ($farmArea !== false && (float) $clean['area_planted'] > (float) $farmArea) {
                $errors['area_planted'] = 'Area planted cannot exceed the farm area of ' . number_format((float) $farmArea, 2, '.', '') . ' hectares.';
            }
        }
    }
}
