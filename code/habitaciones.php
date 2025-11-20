<?php
require __DIR__ . '/vendor/autoload.php';
use Src\Rooms\Infrastructure\Services\RoomsController;
use Src\Shared\Infrastructure\Services\UuidIdentifierCreator;

$message = null;
$error = null;
$roomTypes = ['Individual', 'Doble', 'Suite', 'Twin'];
$roomStates = ['Disponible', 'Ocupada', 'Limpieza', 'Mantenimiento', 'Bloqueada']; // Nuevos estados

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'add_room') {
            $name = trim($_POST['room_name']);
            $type = trim($_POST['room_type']);
            $price = floatval($_POST['room_price']);
            $capacity = intval($_POST['room_capacity']);
            $state = $_POST['room_state'] ?? 'Disponible';

            $idCreator = new UuidIdentifierCreator();
            RoomsController::addRoom([
                'room_id' => $idCreator->createIdentifier()->getValue(),
                'room_name' => $name,
                'room_type' => strtolower($type),
                'room_price' => $price,
                'room_capacity' => $capacity,
                'room_state' => $state
            ]);
            $message = "✅ Habitación creada.";
        }
        elseif ($_POST['action'] === 'edit_room') {
            RoomsController::updateRoom([
                'room_id' => $_POST['room_id'],
                'room_name' => $_POST['room_name'],
                'room_type' => strtolower($_POST['room_type']),
                'room_price' => floatval($_POST['room_price']),
                'room_capacity' => intval($_POST['room_capacity']),
                'room_state' => $_POST['room_state']
            ]);
            $message = "✏️ Habitación actualizada.";
        }
        elseif ($_POST['action'] === 'delete_room') {
            RoomsController::deleteRoom($_POST['room_id']);
            $message = "🗑️ Habitación eliminada.";
        }
    } catch (Exception $e) {
        $error = "❌ Error: " . $e->getMessage();
    }
}

try { $habitaciones = RoomsController::getRooms(); } catch (Exception $e) { $habitaciones = []; }
require_once __DIR__ . '/views/layouts/header.php';
?>

<script>
    document.getElementById('page-title').innerText = 'Gestionar Habitaciones';
    function prepareEditRoom(id, name, type, price, capacity, state) {
        document.getElementById('edit_room_id').value = id;
        document.getElementById('edit_room_name').value = name;
        document.getElementById('edit_room_type').value = type;
        document.getElementById('edit_room_price').value = price;
        document.getElementById('edit_room_capacity').value = capacity;
        document.getElementById('edit_room_state').value = state;
    }
    function confirmDelete(id) {
        if(confirm('¿Eliminar habitación?')) {
            document.getElementById('delete_room_id').value = id;
            document.getElementById('deleteForm').submit();
        }
    }
</script>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Habitaciones</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddRoom"><i class="fa-solid fa-plus"></i> Agregar</button>
    </div>
    <div class="card-body">
        <?php if ($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th><th>Tipo</th><th>Precio</th><th>Cap.</th><th>Estado</th><th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($habitaciones as $room): 
                        $st = $room['room_state'];
                        // Colores según estado
                        $bg = 'secondary';
                        if($st === 'Disponible') $bg = 'success';
                        if($st === 'Ocupada') $bg = 'primary';
                        if($st === 'Mantenimiento' || $st === 'Bloqueada') $bg = 'danger';
                        if($st === 'Limpieza') $bg = 'warning text-dark';
                        
                        $tipoDisplay = ucfirst($room['room_type']);
                    ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($room['room_name']) ?></td>
                        <td><?= htmlspecialchars($tipoDisplay) ?></td>
                        <td>$<?= number_format($room['room_price'], 2) ?></td>
                        <td><?= $room['room_capacity'] ?></td>
                        <td><span class="badge bg-<?= $bg ?>"><?= $st ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#modalEditRoom"
                                    onclick="prepareEditRoom('<?= $room['room_id'] ?>', '<?= htmlspecialchars($room['room_name']) ?>', '<?= $tipoDisplay ?>', '<?= $room['room_price'] ?>', '<?= $room['room_capacity'] ?>', '<?= $st ?>')">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= $room['room_id'] ?>')"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAddRoom" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add_room">
                <div class="modal-header"><h5 class="modal-title">Nueva Habitación</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label>Nombre/Número</label><input type="text" class="form-control" name="room_name" required></div>
                    <div class="row">
                        <div class="col-6 mb-3"><label>Tipo</label><select class="form-select" name="room_type" required><?php foreach ($roomTypes as $t): ?><option value="<?= $t ?>"><?= $t ?></option><?php endforeach; ?></select></div>
                        <div class="col-6 mb-3"><label>Capacidad</label><input type="number" class="form-control" name="room_capacity" min="1" required></div>
                    </div>
                    <div class="mb-3"><label>Precio</label><input type="number" class="form-control" name="room_price" step="0.01" required></div>
                    <div class="mb-3"><label>Estado Inicial</label>
                        <select class="form-select" name="room_state">
                            <?php foreach ($roomStates as $s): ?><option value="<?= $s ?>"><?= $s ?></option><?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditRoom" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="edit_room">
                <input type="hidden" name="room_id" id="edit_room_id">
                <div class="modal-header"><h5 class="modal-title">Editar Habitación</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label>Nombre/Número</label><input type="text" class="form-control" name="room_name" id="edit_room_name" required></div>
                    <div class="row">
                        <div class="col-6 mb-3"><label>Tipo</label><select class="form-select" name="room_type" id="edit_room_type" required><?php foreach ($roomTypes as $t): ?><option value="<?= $t ?>"><?= $t ?></option><?php endforeach; ?></select></div>
                        <div class="col-6 mb-3"><label>Capacidad</label><input type="number" class="form-control" name="room_capacity" id="edit_room_capacity" min="1" required></div>
                    </div>
                    <div class="mb-3"><label>Precio</label><input type="number" class="form-control" name="room_price" id="edit_room_price" step="0.01" required></div>
                    
                    <div class="mb-3 bg-light p-2 rounded">
                        <label class="fw-bold text-primary">Estado Actual</label>
                        <select class="form-select" name="room_state" id="edit_room_state">
                            <?php foreach ($roomStates as $s): ?>
                                <option value="<?= $s ?>"><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text small">Si selecciona 'Mantenimiento' o 'Bloqueada', no se podrá reservar.</div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button><button type="submit" class="btn btn-warning">Actualizar</button></div>
            </form>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display:none;"><input type="hidden" name="action" value="delete_room"><input type="hidden" name="room_id" id="delete_room_id"></form>
<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>