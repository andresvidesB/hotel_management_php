<?php
// 1. Cargar Autoload y Controladores
require __DIR__ . '/vendor/autoload.php';
use Src\Rooms\Infrastructure\Services\RoomsController;
use Src\Shared\Infrastructure\Services\UuidIdentifierCreator;

$message = null;
$error = null;

// Datos auxiliares
$roomTypes = ['Individual', 'Doble', 'Suite', 'Twin'];

// 2. MANEJO DE ACCIONES (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        // --- AGREGAR HABITACIÓN ---
        if ($_POST['action'] === 'add_room') {
            $name = trim($_POST['room_name'] ?? '');
            $type = trim($_POST['room_type'] ?? '');
            $price = floatval($_POST['room_price'] ?? 0);
            $capacity = intval($_POST['room_capacity'] ?? 0);

            if (empty($name) || empty($type)) throw new Exception("Nombre y Tipo son obligatorios.");

            $idCreator = new UuidIdentifierCreator();
            $roomId = $idCreator->createIdentifier()->getValue();
            
            $roomData = [
                'room_id' => $roomId,
                'room_name' => $name,
                'room_type' => strtolower($type),
                'room_price' => $price,
                'room_capacity' => $capacity
            ];

            RoomsController::addRoom($roomData);
            $message = "✅ Habitación agregada con éxito.";
        }

        // --- EDITAR HABITACIÓN ---
        elseif ($_POST['action'] === 'edit_room') {
            $id = $_POST['room_id'] ?? '';
            $name = trim($_POST['room_name'] ?? '');
            $type = trim($_POST['room_type'] ?? '');
            $price = floatval($_POST['room_price'] ?? 0);
            $capacity = intval($_POST['room_capacity'] ?? 0);

            if (empty($id) || empty($name)) throw new Exception("Datos incompletos para editar.");

            $roomData = [
                'room_id' => $id,
                'room_name' => $name,
                'room_type' => strtolower($type),
                'room_price' => $price,
                'room_capacity' => $capacity
            ];

            RoomsController::updateRoom($roomData);
            $message = "✏️ Habitación actualizada correctamente.";
        }

        // --- ELIMINAR HABITACIÓN ---
        elseif ($_POST['action'] === 'delete_room') {
            $id = $_POST['room_id'] ?? '';
            if (empty($id)) throw new Exception("ID de habitación inválido.");

            RoomsController::deleteRoom($id);
            $message = "🗑️ Habitación eliminada correctamente.";
        }

    } catch (Exception $e) {
        $error = "❌ Error: " . $e->getMessage();
    }
}

// 3. Obtener Habitaciones
try {
    $habitaciones = RoomsController::getRooms();
} catch (Exception $e) {
    $error = ($error ? $error . " | " : "") . "Error al listar: " . $e->getMessage();
    $habitaciones = [];
}

// 4. Renderizar Vista
require_once __DIR__ . '/views/layouts/header.php';
?>

<script>
    document.getElementById('page-title').innerText = 'Gestionar habitaciones del hotel';

    // Llenar modal de edición
    function prepareEditRoom(id, name, type, price, capacity) {
        document.getElementById('edit_room_id').value = id;
        document.getElementById('edit_room_name').value = name;
        document.getElementById('edit_room_type').value = type; // Debe coincidir con las opciones (capitalizado en HTML)
        document.getElementById('edit_room_price').value = price;
        document.getElementById('edit_room_capacity').value = capacity;
    }

    // Confirmar eliminación
    function confirmDelete(id, name) {
        if(confirm('¿Eliminar la habitación "' + name + '"?')) {
            document.getElementById('delete_room_id').value = id;
            document.getElementById('deleteForm').submit();
        }
    }
</script>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="dashboard.php">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Habitaciones</li>
                </ol>
            </nav>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddRoom">
            <i class="fa-solid fa-plus"></i> Agregar habitación
        </button>
    </div>
    
    <div class="card-body">
        <?php if ($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

        <div class="row mb-3">
            <div class="col-md-4">
                <input type="text" class="form-control" placeholder="Buscar...">
            </div>
            <div class="col-md-8 d-flex justify-content-end align-items-center">
                <span class="badge bg-success-subtle text-success me-2">Disponible</span>
                <span class="badge bg-danger-subtle text-danger me-2">Ocupada</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nombre/Nº</th>
                        <th>Tipo</th>
                        <th>Precio</th>
                        <th>Cap.</th>
                        <th>Estado (Sim.)</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($habitaciones)): ?>
                        <tr><td colspan="6" class="text-center">No hay registros.</td></tr>
                    <?php else: ?>
                        <?php foreach ($habitaciones as $room): 
                            $idHash = intval(substr(str_replace('-', '', $room['room_id']), -5));
                            $estado = ($idHash % 2 == 0) ? 'Ocupada' : 'Disponible';
                            $bgState = ($estado == 'Disponible') ? 'success' : 'danger';
                            // Normalizar tipo para el JS (Capitalizar primera letra)
                            $tipoDisplay = ucfirst($room['room_type']);
                        ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($room['room_name']) ?></td>
                            <td><?= htmlspecialchars($tipoDisplay) ?></td>
                            <td>$<?= number_format($room['room_price'], 2) ?></td>
                            <td><?= $room['room_capacity'] ?></td>
                            <td><span class="badge bg-<?= $bgState ?>"><?= $estado ?></span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalEditRoom"
                                        onclick="prepareEditRoom(
                                            '<?= $room['room_id'] ?>',
                                            '<?= htmlspecialchars($room['room_name']) ?>',
                                            '<?= htmlspecialchars($tipoDisplay) ?>',
                                            '<?= $room['room_price'] ?>',
                                            '<?= $room['room_capacity'] ?>'
                                        )">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger"
                                        onclick="confirmDelete('<?= $room['room_id'] ?>', '<?= htmlspecialchars($room['room_name']) ?>')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAddRoom" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add_room">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Habitación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre/Número</label>
                        <input type="text" class="form-control" name="room_name" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Tipo</label>
                            <select class="form-select" name="room_type" required>
                                <?php foreach ($roomTypes as $t): ?><option value="<?= $t ?>"><?= $t ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Capacidad</label>
                            <input type="number" class="form-control" name="room_capacity" min="1" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Precio</label>
                        <input type="number" class="form-control" name="room_price" step="0.01" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditRoom" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="edit_room">
                <input type="hidden" name="room_id" id="edit_room_id">
                
                <div class="modal-header">
                    <h5 class="modal-title">Editar Habitación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre/Número</label>
                        <input type="text" class="form-control" name="room_name" id="edit_room_name" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Tipo</label>
                            <select class="form-select" name="room_type" id="edit_room_type" required>
                                <?php foreach ($roomTypes as $t): ?><option value="<?= $t ?>"><?= $t ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Capacidad</label>
                            <input type="number" class="form-control" name="room_capacity" id="edit_room_capacity" min="1" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Precio</label>
                        <input type="number" class="form-control" name="room_price" id="edit_room_price" step="0.01" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-warning">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display:none;">
    <input type="hidden" name="action" value="delete_room">
    <input type="hidden" name="room_id" id="delete_room_id">
</form>

<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>