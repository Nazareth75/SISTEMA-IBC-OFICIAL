import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();


import Swal from 'sweetalert2';

window.Swal = Swal;

document.addEventListener('DOMContentLoaded', () => {
    const successMessage = document.querySelector('meta[name="flash-success"]')?.content;
    const errorMessage = document.querySelector('meta[name="flash-error"]')?.content;

    if (successMessage) {
        Swal.fire({
            icon: 'success',
            title: 'Operación exitosa',
            text: successMessage,
            confirmButtonText: 'Aceptar',
        });
    }

    if (errorMessage) {
        Swal.fire({
            icon: 'error',
            title: 'Ocurrió un problema',
            text: errorMessage,
            confirmButtonText: 'Aceptar',
        });
    }

    document.querySelectorAll('.form-eliminar-persona').forEach(form => {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            Swal.fire({
                title: '¿Está seguro?',
                text: 'La persona será dada de baja lógicamente.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
            }).then(result => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    document.querySelectorAll('.form-eliminar-tutor').forEach(form => {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            Swal.fire({
                title: '¿Está seguro?',
                text: 'El tutor/contacto será dado de baja lógicamente.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
            }).then(result => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});