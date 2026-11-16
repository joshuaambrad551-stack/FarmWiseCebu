
<?php

class Dashboard {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function counts(): array {
        $sql = "SELECT
                    (SELECT COUNT(*) FROM farmer) AS farmers,
                    (SELECT COUNT(*) FROM farm) AS farms,
                    (SELECT COUNT(*) FROM crop) AS crops,
                    (SELECT COUNT(*) FROM planting) AS plantings,
                    (SELECT COUNT(*) FROM harvest) AS harvests,
                    (SELECT COALESCE(SUM(quantity_kg), 0) FROM harvest) AS total_quantity,
                    (SELECT COALESCE(SUM(quantity_kg * selling_price_per_kg), 0) FROM harvest) AS total_value";
        return $this->db->query($sql)->fetch();
    }

    public function plantingsByStatus(): array {
        $sql = "SELECT ps.status_name, COUNT(pl.planting_id) AS total
                FROM planting_status ps
                LEFT JOIN planting pl ON pl.status_id = ps.status_id
                GROUP BY ps.status_id, ps.status_name
                ORDER BY ps.status_id ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function quantityByCrop(): array {
        $sql = "SELECT cr.crop_name, SUM(hv.quantity_kg) AS quantity
                FROM harvest hv
                JOIN planting pl ON pl.planting_id = hv.planting_id
                JOIN crop cr ON cr.crop_id = pl.crop_id
                GROUP BY cr.crop_id, cr.crop_name
                ORDER BY quantity DESC, cr.crop_name ASC
                LIMIT 8";
        return $this->db->query($sql)->fetchAll();
    }

    public function recentPlantings(int $limit = 5): array {
        $sql = "SELECT pl.planting_id, fm.farm_name, cr.crop_name, cr.variety, pl.planting_date, pl.area_planted, ps.status_name
                FROM planting pl
                JOIN farm fm ON fm.farm_id = pl.farm_id
                JOIN crop cr ON cr.crop_id = pl.crop_id
                JOIN planting_status ps ON ps.status_id = pl.status_id
                ORDER BY pl.planting_date DESC, pl.planting_id DESC
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function recentHarvests(int $limit = 5): array {
        $sql = "SELECT hv.harvest_id, fm.farm_name, cr.crop_name, hv.harvest_date, hv.quantity_kg, qg.grade_name
                FROM harvest hv
                JOIN planting pl ON pl.planting_id = hv.planting_id
                JOIN farm fm ON fm.farm_id = pl.farm_id
                JOIN crop cr ON cr.crop_id = pl.crop_id
                JOIN quality_grade qg ON qg.grade_id = hv.grade_id
                ORDER BY hv.harvest_date DESC, hv.harvest_id DESC
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
