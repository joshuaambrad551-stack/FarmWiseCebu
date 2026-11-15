<?php

class Report {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public static function types(): array {
        return [
            'farmers_farms' => 'Farmers and their farms',
            'crops_per_farm' => 'Crops planted per farm',
            'planting_status' => 'Planting records by status',
            'harvests' => 'Harvest records',
            'harvest_total' => 'Total harvested quantity',
            'harvest_value' => 'Estimated harvest value',
        ];
    }

    public function run(string $type, array $filters): array {
        $bind = [];
        $conditions = [];
        $dateColumn = [
            'farmers_farms' => 'fr.date_registered',
            'crops_per_farm' => 'pl.planting_date',
            'planting_status' => 'pl.planting_date',
            'harvests' => 'hv.harvest_date',
            'harvest_total' => 'hv.harvest_date',
            'harvest_value' => 'hv.harvest_date',
        ][$type];

        $from = $this->validDate($filters['date_from'] ?? '');
        $to = $this->validDate($filters['date_to'] ?? '');
        if ($from) {
            $conditions[] = "$dateColumn >= :date_from";
            $bind[':date_from'] = $from;
        }
        if ($to) {
            $conditions[] = "$dateColumn <= :date_to";
            $bind[':date_to'] = $to;
        }

        $farmId = $filters['farm_id'] ?? '';
        $farmColumn = $type === 'farmers_farms' ? 'fm.farm_id' : 'pl.farm_id';
        if ($type !== 'planting_status' && ctype_digit((string) $farmId) && $farmId !== '') {
            $conditions[] = "$farmColumn = :farm_id";
            $bind[':farm_id'] = (int) $farmId;
        }

        $statusId = $filters['status_id'] ?? '';
        if ($type === 'planting_status' && ctype_digit((string) $statusId) && $statusId !== '') {
            $conditions[] = 'pl.status_id = :status_id';
            $bind[':status_id'] = (int) $statusId;
        }

        $where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';

        switch ($type) {
            case 'farmers_farms':
                $columns = [
                    ['farmer', 'Farmer', 'text'], ['address', 'Farmer address', 'text'],
                    ['farm_name', 'Farm', 'text'], ['location', 'Farm location', 'text'],
                    ['area_hectares', 'Area (ha)', 'decimal'], ['soil_type_name', 'Soil type', 'text'],
                    ['irrigation_type_name', 'Irrigation', 'text'],
                ];
                $sql = "SELECT fr.last_name || ', ' || fr.first_name AS farmer, fr.address, fm.farm_name, fm.location,
                               fm.area_hectares, st.soil_type_name, it.irrigation_type_name
                        FROM farmer fr
                        LEFT JOIN farm fm ON fm.farmer_id = fr.farmer_id
                        LEFT JOIN soil_type st ON st.soil_type_id = fm.soil_type_id
                        LEFT JOIN irrigation_type it ON it.irrigation_type_id = fm.irrigation_type_id
                        $where
                        ORDER BY fr.last_name ASC, fr.first_name ASC, fm.farm_name ASC";
                $totals = ['area_hectares' => 'sum'];
                break;

            case 'crops_per_farm':
                $columns = [
                    ['farm_name', 'Farm', 'text'], ['crop_name', 'Crop', 'text'], ['variety', 'Variety', 'text'],
                    ['plantings', 'Plantings', 'int'], ['area_planted', 'Total area planted (ha)', 'decimal'],
                    ['seed_quantity', 'Total seed quantity', 'decimal'],
                ];
                $sql = "SELECT fm.farm_name, cr.crop_name, cr.variety, COUNT(pl.planting_id) AS plantings,
                               SUM(pl.area_planted) AS area_planted, SUM(pl.seed_quantity) AS seed_quantity
                        FROM planting pl
                        JOIN farm fm ON fm.farm_id = pl.farm_id
                        JOIN crop cr ON cr.crop_id = pl.crop_id
                        $where
                        GROUP BY fm.farm_id, fm.farm_name, cr.crop_id, cr.crop_name, cr.variety
                        ORDER BY fm.farm_name ASC, cr.crop_name ASC";
                $totals = ['plantings' => 'sum', 'area_planted' => 'sum', 'seed_quantity' => 'sum'];
                break;

            case 'planting_status':
                $columns = [
                    ['status_name', 'Status', 'status'], ['planting_id', 'Planting #', 'int'], ['farm_name', 'Farm', 'text'],
                    ['crop_name', 'Crop', 'text'], ['planting_date', 'Planting date', 'date'],
                    ['expected_harvest_date', 'Expected harvest', 'date'], ['area_planted', 'Area (ha)', 'decimal'],
                ];
                $sql = "SELECT ps.status_name, pl.planting_id, fm.farm_name, cr.crop_name, pl.planting_date,
                               pl.expected_harvest_date, pl.area_planted
                        FROM planting pl
                        JOIN planting_status ps ON ps.status_id = pl.status_id
                        JOIN farm fm ON fm.farm_id = pl.farm_id
                        JOIN crop cr ON cr.crop_id = pl.crop_id
                        $where
                        ORDER BY ps.status_id ASC, pl.planting_date ASC, pl.planting_id ASC";
                $totals = ['area_planted' => 'sum'];
                break;

            case 'harvests':
                $columns = [
                    ['harvest_id', 'Harvest #', 'int'], ['farm_name', 'Farm', 'text'], ['crop_name', 'Crop', 'text'],
                    ['harvest_date', 'Harvest date', 'date'], ['quantity_kg', 'Quantity (kg)', 'number'],
                    ['grade_name', 'Grade', 'text'], ['remark_description', 'Remarks', 'text'],
                ];
                $sql = "SELECT hv.harvest_id, fm.farm_name, cr.crop_name, hv.harvest_date, hv.quantity_kg,
                               qg.grade_name, hr.remark_description
                        FROM harvest hv
                        JOIN planting pl ON pl.planting_id = hv.planting_id
                        JOIN farm fm ON fm.farm_id = pl.farm_id
                        JOIN crop cr ON cr.crop_id = pl.crop_id
                        JOIN quality_grade qg ON qg.grade_id = hv.grade_id
                        JOIN harvest_remark hr ON hr.remark_id = hv.remark_id
                        $where
                        ORDER BY hv.harvest_date DESC, hv.harvest_id DESC";
                $totals = ['quantity_kg' => 'sum'];
                break;

            case 'harvest_total':
                $columns = [
                    ['crop_name', 'Crop', 'text'], ['harvests', 'Harvest records', 'int'],
                    ['quantity_kg', 'Total harvested (kg)', 'number'],
                ];
                $sql = "SELECT cr.crop_name, COUNT(hv.harvest_id) AS harvests, SUM(hv.quantity_kg) AS quantity_kg
                        FROM harvest hv
                        JOIN planting pl ON pl.planting_id = hv.planting_id
                        JOIN crop cr ON cr.crop_id = pl.crop_id
                        $where
                        GROUP BY cr.crop_id, cr.crop_name
                        ORDER BY quantity_kg DESC, cr.crop_name ASC";
                $totals = ['harvests' => 'sum', 'quantity_kg' => 'sum'];
                break;

            default:
                $columns = [
                    ['farm_name', 'Farm', 'text'], ['harvests', 'Harvest records', 'int'],
                    ['quantity_kg', 'Quantity (kg)', 'number'], ['estimated_value', 'Estimated value', 'money'],
                ];
                $sql = "SELECT fm.farm_name, COUNT(hv.harvest_id) AS harvests, SUM(hv.quantity_kg) AS quantity_kg,
                               SUM(hv.quantity_kg * hv.selling_price_per_kg) AS estimated_value
                        FROM harvest hv
                        JOIN planting pl ON pl.planting_id = hv.planting_id
                        JOIN farm fm ON fm.farm_id = pl.farm_id
                        $where
                        GROUP BY fm.farm_id, fm.farm_name
                        ORDER BY estimated_value DESC, fm.farm_name ASC";
                $totals = ['harvests' => 'sum', 'quantity_kg' => 'sum', 'estimated_value' => 'sum'];
                break;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bind);
        $rows = $stmt->fetchAll();

        $sums = [];
        foreach ($totals as $key => $op) {
            $sums[$key] = array_sum(array_map(fn($r) => (float) $r[$key], $rows));
        }

        return ['columns' => $columns, 'rows' => $rows, 'totals' => $sums];
    }

    private function validDate(string $value): ?string {
        if ($value === '') {
            return null;
        }
        $d = DateTime::createFromFormat('Y-m-d', $value);
        return ($d && $d->format('Y-m-d') === $value) ? $value : null;
    }
}
