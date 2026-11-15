<?php

class Lookup {
    private PDO $db;
    private array $cache = [];

    private array $map = [
        'soil_type' => ['table' => 'soil_type', 'pk' => 'soil_type_id', 'label' => 'soil_type_name', 'order' => 'label ASC'],
        'irrigation_type' => ['table' => 'irrigation_type', 'pk' => 'irrigation_type_id', 'label' => 'irrigation_type_name', 'order' => 'label ASC'],
        'crop_category' => ['table' => 'crop_category', 'pk' => 'crop_category_id', 'label' => 'crop_category_name', 'order' => 'label ASC'],
        'season' => ['table' => 'season', 'pk' => 'season_id', 'label' => 'season_name', 'order' => 'id ASC'],
        'planting_status' => ['table' => 'planting_status', 'pk' => 'status_id', 'label' => 'status_name', 'order' => 'id ASC'],
        'quality_grade' => ['table' => 'quality_grade', 'pk' => 'grade_id', 'label' => 'grade_name', 'order' => 'label ASC'],
        'harvest_remark' => ['table' => 'harvest_remark', 'pk' => 'remark_id', 'label' => 'remark_description', 'order' => 'id ASC'],
        'app_role' => ['table' => 'app_role', 'pk' => 'role_id', 'label' => 'role_name', 'order' => 'id ASC'],
        'farmer' => [
            'table' => 'farmer',
            'pk' => 'farmer_id',
            'label' => "last_name || ', ' || first_name",
            'order' => 'label ASC',
        ],
        'farm' => [
            'table' => 'farm',
            'pk' => 'farm_id',
            'label' => 'farm_name',
            'order' => 'label ASC',
        ],
        'crop' => [
            'table' => 'crop',
            'pk' => 'crop_id',
            'label' => "crop_name || ' (' || variety || ')'",
            'order' => 'label ASC',
        ],
        'planting' => [
            'table' => 'planting',
            'pk' => 'planting_id',
            'from' => 'planting p JOIN farm f ON f.farm_id = p.farm_id JOIN crop c ON c.crop_id = p.crop_id',
            'idexpr' => 'p.planting_id',
            'label' => "'#' || p.planting_id || ' - ' || f.farm_name || ' - ' || c.crop_name || ' (' || p.planting_date || ')'",
            'order' => 'id ASC',
        ],
    ];

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function options(string $name): array {
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }
        $m = $this->map[$name];
        $from = $m['from'] ?? $m['table'];
        $idexpr = $m['idexpr'] ?? $m['pk'];
        $sql = "SELECT {$idexpr} AS id, {$m['label']} AS label FROM {$from} ORDER BY {$m['order']}";
        $options = [];
        foreach ($this->db->query($sql)->fetchAll() as $row) {
            $options[$row['id']] = $row['label'];
        }
        $this->cache[$name] = $options;
        return $options;
    }

    public function exists(string $name, int $id): bool {
        $m = $this->map[$name];
        $stmt = $this->db->prepare("SELECT 1 FROM {$m['table']} WHERE {$m['pk']} = :id");
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }
}
