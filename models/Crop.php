<?php

class Crop extends BaseModel {
    protected string $table = 'crop';
    protected string $pk = 'crop_id';
    protected string $alias = 'cr';

    public function fields(): array {
        return [
            'crop_name' => ['label' => 'Crop name', 'type' => 'text', 'max' => 80],
            'variety' => ['label' => 'Variety', 'type' => 'text', 'max' => 80],
            'crop_category_id' => ['label' => 'Category', 'type' => 'select', 'lookup' => 'crop_category'],
            'growing_period_days' => ['label' => 'Growing period (days)', 'type' => 'number', 'int' => true, 'exclusive_min' => 0, 'max' => 3650],
            'season_id' => ['label' => 'Recommended season', 'type' => 'select', 'lookup' => 'season'],
        ];
    }

    protected function selectSql(): string {
        return 'cr.crop_id, cr.crop_name, cr.variety, cr.crop_category_id, cc.crop_category_name,
                cr.growing_period_days, cr.season_id, se.season_name,
                (SELECT COUNT(*) FROM planting x WHERE x.crop_id = cr.crop_id) AS planting_count'
            . $this->auditSelect();
    }

    protected function fromSql(): string {
        return 'crop cr
                JOIN crop_category cc ON cc.crop_category_id = cr.crop_category_id
                JOIN season se ON se.season_id = cr.season_id'
            . $this->auditJoin();
    }

    protected function searchSql(): string {
        return "(cr.crop_name || ' ' || cr.variety)";
    }

    protected function filterSql(): array {
        return [
            'crop_category_id' => ['sql' => 'cr.crop_category_id = :crop_category_id', 'type' => 'int'],
            'season_id' => ['sql' => 'cr.season_id = :season_id', 'type' => 'int'],
        ];
    }

    protected function sortMap(): array {
        return [
            'id' => 'cr.crop_id',
            'name' => 'cr.crop_name',
            'variety' => 'cr.variety',
            'category' => 'cc.crop_category_name',
            'days' => 'cr.growing_period_days',
            'season' => 'se.season_name',
        ];
    }

    public function crossCheck(array $clean, array &$errors, ?int $id): void {
        if (!isset($clean['crop_name'], $clean['variety'])) {
            return;
        }
        $sql = 'SELECT 1 FROM crop WHERE LOWER(crop_name) = LOWER(:crop_name) AND LOWER(variety) = LOWER(:variety)';
        $bind = [':crop_name' => $clean['crop_name'], ':variety' => $clean['variety']];
        if ($id !== null) {
            $sql .= ' AND crop_id <> :id';
            $bind[':id'] = $id;
        }
        if ($this->run($sql, $bind)->fetchColumn()) {
            $errors['variety'] = 'This crop and variety combination already exists.';
        }
    }
}
