<?php

const STORAGE_FILE = __DIR__ . '/../data/data.json';

function load_data(): array
{
    if (!file_exists(STORAGE_FILE)) {
        $data = default_data();
        save_data($data);
        return $data;
    }

    $json = file_get_contents(STORAGE_FILE);
    $data = json_decode($json, true);

    if (!is_array($data)) {
        $data = default_data();
        save_data($data);
    }

    return $data;
}

function save_data(array $data): void
{
    file_put_contents(STORAGE_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function default_data(): array
{
    $departments = [
        ['id' => 'dep_dir', 'name' => 'Dirección General', 'leader' => 'Laura Méndez', 'parent_id' => null],
        ['id' => 'dep_hr', 'name' => 'Recursos Humanos', 'leader' => 'Carlos Paredes', 'parent_id' => 'dep_dir'],
        ['id' => 'dep_it', 'name' => 'Tecnología', 'leader' => 'Andrea Ríos', 'parent_id' => 'dep_dir'],
        ['id' => 'dep_fin', 'name' => 'Finanzas', 'leader' => 'Marcelo Díaz', 'parent_id' => 'dep_dir'],
    ];

    $employees = [
        ['id' => 'emp_001', 'name' => 'Ana Torres', 'role' => 'Analista de Nómina', 'department_id' => 'dep_fin', 'salary' => 1200, 'email' => 'ana.torres@empresa.com'],
        ['id' => 'emp_002', 'name' => 'Jorge Luna', 'role' => 'Especialista en RRHH', 'department_id' => 'dep_hr', 'salary' => 1050, 'email' => 'jorge.luna@empresa.com'],
        ['id' => 'emp_003', 'name' => 'María Prado', 'role' => 'Desarrolladora Fullstack', 'department_id' => 'dep_it', 'salary' => 1500, 'email' => 'maria.prado@empresa.com'],
        ['id' => 'emp_004', 'name' => 'Hernán Ortiz', 'role' => 'Administrador de Sistemas', 'department_id' => 'dep_it', 'salary' => 1300, 'email' => 'hernan.ortiz@empresa.com'],
    ];

    $today = date('Y-m-d');

    return [
        'departments' => $departments,
        'employees' => $employees,
        'attendance' => [
            ['id' => uniqid('att_'), 'employee_id' => 'emp_001', 'date' => $today, 'status' => 'Presente'],
            ['id' => uniqid('att_'), 'employee_id' => 'emp_003', 'date' => $today, 'status' => 'Presente'],
        ],
        'payroll' => [
            ['id' => uniqid('pay_'), 'employee_id' => 'emp_001', 'month' => date('Y-m'), 'base' => 1200, 'bonus' => 120, 'deductions' => 60],
            ['id' => uniqid('pay_'), 'employee_id' => 'emp_002', 'month' => date('Y-m'), 'base' => 1050, 'bonus' => 90, 'deductions' => 40],
        ],
        'leaves' => [
            ['id' => uniqid('leave_'), 'employee_id' => 'emp_002', 'type' => 'Vacaciones', 'from' => $today, 'to' => date('Y-m-d', strtotime('+5 days')), 'status' => 'Aprobado'],
        ],
        'evaluations' => [
            ['id' => uniqid('eval_'), 'employee_id' => 'emp_003', 'period' => '2024 Q4', 'score' => 92, 'comments' => 'Entrega proyectos a tiempo y lidera revisiones de código.'],
        ],
    ];
}

function find_department(array $data, string $id): ?array
{
    foreach ($data['departments'] as $department) {
        if ($department['id'] === $id) {
            return $department;
        }
    }

    return null;
}

function find_employee(array $data, string $id): ?array
{
    foreach ($data['employees'] as $employee) {
        if ($employee['id'] === $id) {
            return $employee;
        }
    }

    return null;
}

function add_department(array &$data, string $name, string $leader, ?string $parent): void
{
    $data['departments'][] = [
        'id' => uniqid('dep_'),
        'name' => trim($name),
        'leader' => trim($leader),
        'parent_id' => $parent ?: null,
    ];
}

function add_employee(array &$data, string $name, string $role, string $department, float $salary, string $email): void
{
    $data['employees'][] = [
        'id' => uniqid('emp_'),
        'name' => trim($name),
        'role' => trim($role),
        'department_id' => $department,
        'salary' => $salary,
        'email' => trim($email),
    ];
}

function register_attendance(array &$data, string $employeeId, string $status, string $date): void
{
    $data['attendance'][] = [
        'id' => uniqid('att_'),
        'employee_id' => $employeeId,
        'date' => $date,
        'status' => $status,
    ];
}

function register_payroll(array &$data, string $employeeId, string $month, float $base, float $bonus, float $deductions): void
{
    $data['payroll'][] = [
        'id' => uniqid('pay_'),
        'employee_id' => $employeeId,
        'month' => $month,
        'base' => $base,
        'bonus' => $bonus,
        'deductions' => $deductions,
    ];
}

function register_leave(array &$data, string $employeeId, string $type, string $from, string $to, string $status): void
{
    $data['leaves'][] = [
        'id' => uniqid('leave_'),
        'employee_id' => $employeeId,
        'type' => $type,
        'from' => $from,
        'to' => $to,
        'status' => $status,
    ];
}

function register_evaluation(array &$data, string $employeeId, string $period, int $score, string $comments): void
{
    $data['evaluations'][] = [
        'id' => uniqid('eval_'),
        'employee_id' => $employeeId,
        'period' => $period,
        'score' => $score,
        'comments' => $comments,
    ];
}

function org_chart(array $data, ?string $parentId = null): array
{
    $tree = [];
    foreach ($data['departments'] as $department) {
        if ($department['parent_id'] === $parentId) {
            $children = org_chart($data, $department['id']);
            $team = array_filter($data['employees'], fn($employee) => $employee['department_id'] === $department['id']);
            $tree[] = [
                'department' => $department,
                'children' => $children,
                'team' => $team,
            ];
        }
    }

    return $tree;
}

function payroll_summary(array $data): array
{
    $summary = [];
    foreach ($data['payroll'] as $item) {
        $total = $item['base'] + $item['bonus'] - $item['deductions'];
        $summary[] = array_merge($item, ['total' => $total]);
    }

    return $summary;
}