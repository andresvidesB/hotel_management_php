</div> </div> </div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php
// Verificar si hay sesión de cliente para mostrar el botón
if (session_status() === PHP_SESSION_NONE) session_start();
$role = $_SESSION['role_id'] ?? '';

// MOSTRAR SOLO PARA CLIENTES (Rol 3)
if ($role == '3'): 
?>
    <a href="https://wa.me/573137163216?text=Hola,%20necesito%20ayuda%20con%20mi%20reserva." 
       target="_blank" 
       class="btn-whatsapp shadow-lg"
       title="Contactar por WhatsApp">
        <i class="fa-brands fa-whatsapp"></i>
    </a>

    <style>
        .btn-whatsapp {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: #25D366;
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 35px;
            text-decoration: none;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        .btn-whatsapp:hover {
            background-color: #128C7E;
            color: white;
            transform: scale(1.1);
        }
    </style>
<?php endif; ?>

</body>
</html>