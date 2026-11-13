// Scripts del sistema (pantallas con el menú lateral): iconos FontAwesome y avisos SweetAlert2
import '@fortawesome/fontawesome-free/css/all.min.css';
import Swal from 'sweetalert2';

window.Swal = Swal;

const colores = { confirmButtonColor: '#27AE60', cancelButtonColor: '#123B5D' };

// 1) Confirmaciones: cualquier <form data-confirmar="mensaje"> pregunta antes de enviarse
document.addEventListener('submit', (e) => {
    const form = e.target;
    const mensaje = form.dataset ? form.dataset.confirmar : null;

    if (!mensaje || form.dataset.confirmado) {
        return;
    }

    e.preventDefault();

    Swal.fire({
        title: '¿Estás seguro?',
        text: mensaje,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar',
        ...colores,
    }).then((resultado) => {
        if (resultado.isConfirmed) {
            form.dataset.confirmado = '1';
            form.submit();
        }
    });
});

// 2) Mensajes de la sesión (éxito / error) como aviso emergente en la esquina
window.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-aviso]').forEach((el) => {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: el.dataset.aviso,
            title: el.textContent.trim(),
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
        });
        el.remove();
    });
});
