<?php

class ModuleController {
    private const PER_PAGE = 10;

    private array $cfg;
    private PDO $db;
    private Lookup $lookup;
    private BaseModel $model;

    public function __construct(array $cfg, PDO $db, Lookup $lookup) {
        $this->cfg = $cfg;
        $this->db = $db;
        $this->lookup = $lookup;
        $this->model = new $cfg['model']($db);
    }

    public function run(): void {
        if (!empty($this->cfg['admin_only'])) {
            Auth::requireAdmin();
        } else {
            Auth::require();
        }

        $action = $_GET['action'] ?? 'list';
        $id = (isset($_GET['id']) && ctype_digit((string) $_GET['id'])) ? (int) $_GET['id'] : null;

        switch ($action) {
            case 'create':
                $this->form(null);
                break;
            case 'edit':
                $id === null ? $this->notFound() : $this->form($id);
                break;
            case 'view':
                $id === null ? $this->notFound() : $this->show($id);
                break;
            case 'delete':
                $id === null ? $this->notFound() : $this->delete($id);
                break;
            default:
                $this->listing();
        }
    }

    private function notFound(): void {
        flash('warning', 'The requested record was not found.');
        redirect($this->cfg['page']);
    }

    private function listing(): void {
        $page = (isset($_GET['page']) && ctype_digit((string) $_GET['page'])) ? (int) $_GET['page'] : 1;
        $data = $this->model->paginate($_GET, $page, self::PER_PAGE);
        render('list', [
            'cfg' => $this->cfg,
            'data' => $data,
            'lookup' => $this->lookup,
            'perPage' => self::PER_PAGE,
            'pageTitle' => $this->cfg['title'],
        ]);
    }

    private function show(int $id): void {
        $record = $this->model->find($id);
        if (!$record) {
            $this->notFound();
        }
        render('show', [
            'cfg' => $this->cfg,
            'record' => $record,
            'pageTitle' => $this->cfg['singular'] . ' Details',
        ]);
    }

    private function truthy($value): bool {
        return $value === true || $value === 't' || $value === '1' || $value === 1;
    }

    private function form(?int $id): void {
        $fields = $this->model->fields();
        $record = null;
        if ($id !== null) {
            $record = $this->model->find($id);
            if (!$record) {
                $this->notFound();
            }
        }

        $errors = [];
        $input = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_valid()) {
                abort(400, 'Your form session has expired. Please go back and try again.');
            }
            $input = $_POST;
            [$clean, $errors] = Validator::clean($fields, $_POST, $this->lookup, $id === null);
            $this->model->crossCheck($clean, $errors, $id);

            if (!$errors) {
                try {
                    if ($id === null) {
                        $newId = $this->model->create($clean, Auth::id());
                        flash('success', $this->cfg['singular'] . ' #' . $newId . ' was created successfully.');
                    } else {
                        $this->model->update($id, $clean, Auth::id());
                        flash('success', $this->cfg['singular'] . ' #' . $id . ' was updated successfully.');
                    }
                    redirect($this->cfg['page']);
                } catch (PDOException $e) {
                    $errors['_form'] = $this->friendlyError($e);
                }
            }
        } elseif ($record) {
            foreach ($fields as $name => $f) {
                if ($f['type'] === 'checkbox') {
                    $input[$name] = $this->truthy($record[$name]) ? '1' : '0';
                } elseif ($f['type'] !== 'password') {
                    $input[$name] = (string) $record[$name];
                }
            }
        } else {
            foreach ($fields as $name => $f) {
                if ($f['type'] === 'checkbox') {
                    $input[$name] = '1';
                } elseif (isset($f['default'])) {
                    $input[$name] = $f['default'];
                }
            }
            foreach (array_keys($fields) as $name) {
                if (isset($_GET[$name]) && !isset($input[$name])) {
                    $input[$name] = (string) $_GET[$name];
                }
            }
        }

        render('form', [
            'cfg' => $this->cfg,
            'fields' => $fields,
            'lookup' => $this->lookup,
            'input' => $input,
            'errors' => $errors,
            'id' => $id,
            'pageTitle' => ($id === null ? 'Add ' : 'Edit ') . $this->cfg['singular'],
        ]);
    }

    private function delete(int $id): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect($this->cfg['page']);
        }
        if (!csrf_valid()) {
            abort(400, 'Your form session has expired. Please go back and try again.');
        }
        if (!Auth::isAdmin()) {
            flash('danger', 'Only an Administrator can delete records.');
            redirect($this->cfg['page']);
        }
        if (!empty($this->cfg['protect_self']) && $id === Auth::id()) {
            flash('danger', 'You cannot delete your own account.');
            redirect($this->cfg['page']);
        }

        try {
            $deleted = $this->model->delete($id);
            if ($deleted) {
                flash('success', $this->cfg['singular'] . ' #' . $id . ' was deleted successfully.');
            } else {
                flash('warning', 'The record was already removed.');
            }
        } catch (PDOException $e) {
            if ($e->getCode() === '23503') {
                flash('danger', 'This ' . strtolower($this->cfg['singular']) . ' cannot be deleted because other records still depend on it. Remove or reassign those records first.');
            } else {
                error_log($e->getMessage());
                flash('danger', 'The record could not be deleted. Please try again.');
            }
        }
        redirect($this->cfg['page']);
    }

    private function friendlyError(PDOException $e): string {
        $code = $e->getCode();
        if ($code === '23505') {
            return 'A record with the same unique value already exists.';
        }
        if ($code === '23503') {
            return 'One of the selected related records no longer exists.';
        }
        if ($code === '23514' || $code === '22003') {
            return 'One of the values you entered is outside the allowed range.';
        }
        error_log($e->getMessage());
        return 'The record could not be saved. Please check your entries and try again.';
    }
}
