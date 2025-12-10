<?php
session_start();

require __DIR__ . '/lib/storage.php';

$data = load_data();
$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add_department':
            add_department($data, $_POST['name'], $_POST['leader'], $_POST['parent_id'] ?? null);
            $message = 'Departamento creado correctamente.';
            break;
        case 'add_employee':
            $salary = (float) $_POST['salary'];
            add_employee($data, $_POST['name'], $_POST['role'], $_POST['department_id'], $salary, $_POST['email']);
            $message = 'Empleado registrado exitosamente.';
            break;
        case 'add_attendance':
            register_attendance($data, $_POST['employee_id'], $_POST['status'], $_POST['date']);
            $message = 'Asistencia guardada.';
            break;
        case 'add_payroll':
            register_payroll(
                $data,
                $_POST['employee_id'],
                $_POST['month'],
                (float) $_POST['base'],
                (float) $_POST['bonus'],
                (float) $_POST['deductions']
            );
            $message = 'Nómina calculada.';
            break;
        case 'add_leave':
            register_leave($data, $_POST['employee_id'], $_POST['type'], $_POST['from'], $_POST['to'], $_POST['status']);
            $message = 'Permiso o vacaciones añadidos.';
            break;
        case 'add_evaluation':
            register_evaluation($data, $_POST['employee_id'], $_POST['period'], (int) $_POST['score'], $_POST['comments']);
            $message = 'Evaluación registrada.';
            break;
    }

    save_data($data);
    $_SESSION['message'] = $message;
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

$orgChart = org_chart($data);
$payroll = payroll_summary($data);
$departments = $data['departments'];
$employees = $data['employees'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PROY_FINAL - Gestión de Empleados</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
<header>
    <div class="branding">
        <h1>PROY_FINAL</h1>
        <p>Sistema integral inspirado en los talleres del curso</p>
    </div>
    <nav>
        <a href="#departamentos">Departamentos</a>
        <a href="#empleados">Empleados</a>
        <a href="#asistencia">Asistencia</a>
        <a href="#nomina">Nómina</a>
        <a href="#permisos">Permisos</a>
        <a href="#evaluaciones">Evaluaciones</a>
        <a href="#organigrama">Organigrama</a>
    </nav>
</header>

<main>
    <?php if ($message): ?>
        <div class="alert"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <section id="resumen" class="panel">
        <div>
            <h2>Panorama general</h2>
            <p>Controla empleados, asistencia y nómina en un solo lugar. Usa los formularios para capturar datos y observa los resúmenes en cada bloque.</p>
        </div>
        <div class="resumen-grid">
            <article>
                <h3>Total empleados</h3>
                <p class="big-number"><?= count($employees) ?></p>
                <small>Registrados en el sistema</small>
            </article>
            <article>
                <h3>Departamentos</h3>
                <p class="big-number"><?= count($departments) ?></p>
                <small>Estructura de la empresa</small>
            </article>
            <article>
                <h3>Registros de asistencia</h3>
                <p class="big-number"><?= count($data['attendance']) ?></p>
                <small>Últimos marcajes</small>
            </article>
        </div>
    </section>

    <section id="departamentos" class="panel">
        <div class="panel-header">
            <h2>Registro de departamentos</h2>
            <p>Define áreas, jefaturas y jerarquías para el organigrama.</p>
        </div>
        <form method="POST" class="grid-2">
            <input type="hidden" name="action" value="add_department">
            <label>Nombre del departamento
                <input type="text" name="name" required>
            </label>
            <label>Responsable
                <input type="text" name="leader" required>
            </label>
            <label>Depende de
                <select name="parent_id">
                    <option value="">Dirección principal</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?= $department['id'] ?>"><?= htmlspecialchars($department['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit">Crear departamento</button>
        </form>

        <div class="list">
            <?php foreach ($departments as $department): ?>
                <article>
                    <h3><?= htmlspecialchars($department['name']) ?></h3>
                    <p>Responsable: <?= htmlspecialchars($department['leader']) ?></p>
                    <p>Depende de: <?= $department['parent_id'] ? htmlspecialchars(find_department($data, $department['parent_id'])['name']) : 'Dirección General' ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="empleados" class="panel">
        <div class="panel-header">
            <h2>Registro de empleados</h2>
            <p>Asigna roles, departamentos y correos de contacto.</p>
        </div>
        <form method="POST" class="grid-2">
            <input type="hidden" name="action" value="add_employee">
            <label>Nombre completo
                <input type="text" name="name" required>
            </label>
            <label>Cargo
                <input type="text" name="role" required>
            </label>
            <label>Departamento
                <select name="department_id" required>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?= $department['id'] ?>"><?= htmlspecialchars($department['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Salario base (USD)
                <input type="number" name="salary" min="0" step="0.01" required>
            </label>
            <label>Correo corporativo
                <input type="email" name="email" required>
            </label>
            <button type="submit">Guardar empleado</button>
        </form>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>Cargo</th>
                        <th>Departamento</th>
                        <th>Salario</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $employee): ?>
                        <tr>
                            <td><?= htmlspecialchars($employee['name']) ?></td>
                            <td><?= htmlspecialchars($employee['role']) ?></td>
                            <td><?= htmlspecialchars(find_department($data, $employee['department_id'])['name']) ?></td>
                            <td>$<?= number_format($employee['salary'], 2) ?></td>
                            <td><?= htmlspecialchars($employee['email']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section id="asistencia" class="panel">
        <div class="panel-header">
            <h2>Control de asistencia</h2>
            <p>Registra la asistencia diaria con estatus personalizados.</p>
        </div>
        <form method="POST" class="grid-2">
            <input type="hidden" name="action" value="add_attendance">
            <label>Empleado
                <select name="employee_id" required>
                    <?php foreach ($employees as $employee): ?>
                        <option value="<?= $employee['id'] ?>"><?= htmlspecialchars($employee['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Fecha
                <input type="date" name="date" value="<?= date('Y-m-d') ?>" required>
            </label>
            <label>Estatus
                <select name="status" required>
                    <option value="Presente">Presente</option>
                    <option value="Ausente">Ausente</option>
                    <option value="Remoto">Remoto</option>
                    <option value="Tarde">Tarde</option>
                </select>
            </label>
            <button type="submit">Registrar asistencia</button>
        </form>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Empleado</th>
                        <th>Estatus</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_reverse($data['attendance']) as $attendance): ?>
                        <?php $employee = find_employee($data, $attendance['employee_id']); ?>
                        <tr>
                            <td><?= htmlspecialchars($attendance['date']) ?></td>
                            <td><?= htmlspecialchars($employee['name']) ?></td>
                            <td><?= htmlspecialchars($attendance['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section id="nomina" class="panel">
        <div class="panel-header">
            <h2>Nómina básica</h2>
            <p>Calcula pagos mensuales con bonificaciones y deducciones.</p>
        </div>
        <form method="POST" class="grid-2">
            <input type="hidden" name="action" value="add_payroll">
            <label>Empleado
                <select name="employee_id" required>
                    <?php foreach ($employees as $employee): ?>
                        <option value="<?= $employee['id'] ?>"><?= htmlspecialchars($employee['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Mes
                <input type="month" name="month" value="<?= date('Y-m') ?>" required>
            </label>
            <label>Salario base
                <input type="number" name="base" min="0" step="0.01" required>
            </label>
            <label>Bonos
                <input type="number" name="bonus" min="0" step="0.01" value="0">
            </label>
            <label>Deducciones
                <input type="number" name="deductions" min="0" step="0.01" value="0">
            </label>
            <button type="submit">Agregar a nómina</button>
        </form>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Mes</th>
                        <th>Empleado</th>
                        <th>Base</th>
                        <th>Bono</th>
                        <th>Deducciones</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payroll as $item): ?>
                        <?php $employee = find_employee($data, $item['employee_id']); ?>
                        <tr>
                            <td><?= htmlspecialchars($item['month']) ?></td>
                            <td><?= htmlspecialchars($employee['name']) ?></td>
                            <td>$<?= number_format($item['base'], 2) ?></td>
                            <td>$<?= number_format($item['bonus'], 2) ?></td>
                            <td>$<?= number_format($item['deductions'], 2) ?></td>
                            <td class="strong">$<?= number_format($item['total'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section id="permisos" class="panel">
        <div class="panel-header">
            <h2>Permisos y vacaciones</h2>
            <p>Registra ausencias y su estado de aprobación.</p>
        </div>
        <form method="POST" class="grid-2">
            <input type="hidden" name="action" value="add_leave">
            <label>Empleado
                <select name="employee_id" required>
                    <?php foreach ($employees as $employee): ?>
                        <option value="<?= $employee['id'] ?>"><?= htmlspecialchars($employee['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Tipo
                <input type="text" name="type" placeholder="Vacaciones, Permiso, etc." required>
            </label>
            <label>Desde
                <input type="date" name="from" value="<?= date('Y-m-d') ?>" required>
            </label>
            <label>Hasta
                <input type="date" name="to" value="<?= date('Y-m-d', strtotime('+3 days')) ?>" required>
            </label>
            <label>Estatus
                <select name="status" required>
                    <option value="En revisión">En revisión</option>
                    <option value="Aprobado">Aprobado</option>
                    <option value="Rechazado">Rechazado</option>
                </select>
            </label>
            <button type="submit">Guardar permiso</button>
        </form>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>Tipo</th>
                        <th>Desde</th>
                        <th>Hasta</th>
                        <th>Estatus</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_reverse($data['leaves']) as $leave): ?>
                        <?php $employee = find_employee($data, $leave['employee_id']); ?>
                        <tr>
                            <td><?= htmlspecialchars($employee['name']) ?></td>
                            <td><?= htmlspecialchars($leave['type']) ?></td>
                            <td><?= htmlspecialchars($leave['from']) ?></td>
                            <td><?= htmlspecialchars($leave['to']) ?></td>
                            <td><?= htmlspecialchars($leave['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section id="evaluaciones" class="panel">
        <div class="panel-header">
            <h2>Evaluaciones de desempeño</h2>
            <p>Registra resultados por periodo para cada colaborador.</p>
        </div>
        <form method="POST" class="grid-2">
            <input type="hidden" name="action" value="add_evaluation">
            <label>Empleado
                <select name="employee_id" required>
                    <?php foreach ($employees as $employee): ?>
                        <option value="<?= $employee['id'] ?>"><?= htmlspecialchars($employee['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Periodo
                <input type="text" name="period" placeholder="2025 Q1" required>
            </label>
            <label>Puntaje (0-100)
                <input type="number" name="score" min="0" max="100" required>
            </label>
            <label>Retroalimentación
                <textarea name="comments" rows="3" required></textarea>
            </label>
            <button type="submit">Registrar evaluación</button>
        </form>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>Periodo</th>
                        <th>Puntaje</th>
                        <th>Comentarios</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_reverse($data['evaluations']) as $evaluation): ?>
                        <?php $employee = find_employee($data, $evaluation['employee_id']); ?>
                        <tr>
                            <td><?= htmlspecialchars($employee['name']) ?></td>
                            <td><?= htmlspecialchars($evaluation['period']) ?></td>
                            <td><?= htmlspecialchars($evaluation['score']) ?></td>
                            <td><?= htmlspecialchars($evaluation['comments']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section id="organigrama" class="panel">
        <div class="panel-header">
            <h2>Organigrama empresarial</h2>
            <p>Visualiza la relación entre departamentos y equipos.</p>
        </div>
        <div class="org-chart">
            <?php function render_node($node) { ?>
                <li>
                    <div class="org-card">
                        <strong><?= htmlspecialchars($node['department']['name']) ?></strong>
                        <p>Lead: <?= htmlspecialchars($node['department']['leader']) ?></p>
                        <?php if (!empty($node['team'])): ?>
                            <p class="team">Equipo:
                                <?php foreach ($node['team'] as $member): ?>
                                    <span><?= htmlspecialchars($member['name']) ?></span>
                                <?php endforeach; ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($node['children'])): ?>
                        <ul>
                            <?php foreach ($node['children'] as $child): ?>
                                <?php render_node($child); ?>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php } ?>
            <ul class="org-root">
                <?php foreach ($orgChart as $node): ?>
                    <?php render_node($node); ?>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
</main>

<script src="assets/js/app.js"></script>
</body>
</html>