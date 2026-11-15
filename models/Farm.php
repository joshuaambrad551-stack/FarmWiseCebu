<?php

class Farm extends BaseModel {
    protected string $table = 'farm';
    protected string $pk = 'farm_id';
    protected string $alias = 'fm';

    public function fields(): array {
        return [
            'farmer_id' => ['label' => 'Farmer', 'type' => 'select', 'lookup' => 'farmer'],
            'farm_name' => ['label' => 'Farm name', 'type' => 'text', 'max' => 100],
            'location' => ['label' => 'Location', 'type' => 'text', 'max' => 150],
            'area_hectares' => ['label' => 'Area (hectares)', 'type' => 'number', 'exclusive_min' => 0, 'max' => 999999.99, 'step' => '0.01'],
            'soil_type_id' => ['label' => 'Soil type', 'type' => 'select', 'lookup' => 'soil_type'],
            'irrigation_type_id' => ['label' => 'Irrigation type', 'type' => 'select', 'lookup' => 'irrigation_type'],
        ];
    }

    protected function selectSql(): string {
        return "fm.farm_id, fm.farmer_id, fr.first_name || ' ' || fr.last_name AS farmer_name, fm.farm_name, fm.location,
                fm.area_hectares, fm.soil_type_id, st.soil_type_name, fm.irrigation_type_id, it.irrigation_type_name,
                (SELECT COUNT(*) FROM planting x WHERE x.farm_id = fm.farm_id) AS planting_count"
            . $this->auditSelect();
    }

    protected function fromSql(): string {
        return 'farm fm
                JOIN farmer fr ON fr.farmer_id = fm.farmer_id
                JOIN soil_type st ON st.soil_type_id = fm.soil_type_id
                JOIN irrigation_type it ON it.irrigation_type_id = fm.irrigation_type_id'
            . $this->auditJoin();
    }

    protected function searchSql(): string {
        return "(fm.farm_name || ' ' || fm.location || ' ' || fr.first_name || ' ' || fr.last_name)";
    }

    protected function filterSql(): array {
        return [
            'irrigation_type_id' => ['sql' => 'fm.irrigation_type_id = :irrigation_type_id', 'type' => 'int'],
            'soil_type_id' => ['sql' => 'fm.soil_type_id = :soil_type_id', 'type' => 'int'],
            'farmer_id' => ['sql' => 'fm.farmer_id = :farmer_id', 'type' => 'int'],
        ];
    }

    protected function sortMap(): array {
        return [
            'id' => 'fm.farm_id',
            'name' => 'fm.farm_name',
            'farmer' => 'fr.last_name',
            'location' => 'fm.location',
            'area' => 'fm.area_hectares',
            'soil' => 'st.soil_type_name',
            'irrigation' => 'it.irrigation_type_name',
        ];
    }

    public function crossCheck(array $clean, array &$errors, ?int $id): void {
        if (!isset($clean['farmer_id'], $clean['farm_name'])) {
            return;
        }
        $sql = 'SELECT 1 FROM farm WHERE farmer_id = :farmer_id AND LOWER(farm_name) = LOWER(:farm_name)';
        $bind = [':farmer_id' => $clean['farmer_id'], ':farm_name' => $clean['farm_name']];
        if ($id !== null) {
            $sql .= ' AND farm_id <> :id';
            $bind[':id'] = $id;
        }
        if ($this->run($sql, $bind)->fetchColumn()) {
            $errors['farm_name'] = 'This farmer already has a farm with that name.';
        }
    }
}
