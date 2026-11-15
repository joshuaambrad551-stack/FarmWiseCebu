<?php

class Farmer extends BaseModel {
    protected string $table = 'farmer';
    protected string $pk = 'farmer_id';
    protected string $alias = 'fr';

    public function fields(): array {
        return [
            'first_name' => ['label' => 'First name', 'type' => 'text', 'max' => 50],
            'last_name' => ['label' => 'Last name', 'type' => 'text', 'max' => 50],
            'contact_number' => [
                'label' => 'Contact number',
                'type' => 'tel',
                'max' => 20,
                'pattern' => '/^[0-9+]{7,20}$/',
                'pattern_message' => 'Contact number must contain 7 to 20 digits (a leading + is allowed).',
            ],
            'address' => ['label' => 'Address', 'type' => 'text', 'max' => 150],
            'date_registered' => ['label' => 'Date registered', 'type' => 'date', 'default' => date('Y-m-d')],
        ];
    }

    protected function selectSql(): string {
        return "fr.farmer_id, fr.first_name, fr.last_name, fr.first_name || ' ' || fr.last_name AS farmer_name,
                fr.contact_number, fr.address, fr.date_registered,
                (SELECT COUNT(*) FROM farm x WHERE x.farmer_id = fr.farmer_id) AS farm_count"
            . $this->auditSelect();
    }

    protected function fromSql(): string {
        return 'farmer fr' . $this->auditJoin();
    }

    protected function searchSql(): string {
        return "(fr.first_name || ' ' || fr.last_name || ' ' || fr.contact_number || ' ' || fr.address)";
    }

    protected function filterSql(): array {
        return [
            'address' => ['sql' => 'fr.address ILIKE :address', 'type' => 'like'],
        ];
    }

    protected function sortMap(): array {
        return [
            'id' => 'fr.farmer_id',
            'name' => 'fr.last_name',
            'contact' => 'fr.contact_number',
            'address' => 'fr.address',
            'registered' => 'fr.date_registered',
            'farms' => 'farm_count',
        ];
    }

    public function crossCheck(array $clean, array &$errors, ?int $id): void {
        if (isset($clean['contact_number']) && $this->valueExists('contact_number', $clean['contact_number'], $id)) {
            $errors['contact_number'] = 'This contact number is already registered to another farmer.';
        }
    }
}
