document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('userModal');
    const deleteModal = document.getElementById('deleteModal');
    const addUserBtn = document.querySelector('.add-user-btn');
    const closeBtn = document.querySelector('.close-btn');
    const cancelBtn = document.querySelector('.cancel-btn');
    const form = document.getElementById('userForm');
    let userIdToDelete = null;
    let allUsers = [];

    const excelBtn = document.getElementById('excelBtn');
    const excelModal = document.getElementById('excelModal');
    const closeExcelBtn = document.getElementById('closeExcelModal');

    function handleAlert(alerta) {
        if (!alerta.dataset.timeoutSet) {
            alerta.dataset.timeoutSet = 'true';
            setTimeout(() => {
                alerta.classList.add('hiding');
                setTimeout(() => {
                    if (alerta && alerta.parentNode) {
                        alerta.remove();
                    }
                }, 300);
            }, 3000);
        }
    }

    document.querySelectorAll('.alerta').forEach(handleAlert);

    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node.classList && node.classList.contains('alerta')) {
                    handleAlert(node);
                }
            });
        });
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    function initializeData() {
        if (Array.isArray(window.allUsers)) {
            allUsers = window.allUsers;
        } else {
            const rows = document.querySelectorAll('.users-table tbody tr');
            allUsers = Array.from(rows).map(row => ({
                ANEXO: row.cells[0].textContent.trim(),
                APELLIDO: row.cells[1].textContent.trim(),
                NOMBRE: row.cells[2].textContent.trim(),
                UBICACION: row.cells[3].textContent.trim(),
                CORREO: row.cells[4].textContent.trim()
            }));
        }
    
        allUsers.sort((a, b) => {
            return a.UBICACION.localeCompare(b.UBICACION);
        });
    }
    addUserBtn.addEventListener('click', () => {
        openModal('create');
    });
    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });

    function openModal(type = 'edit') {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        form.reset();
        
        // Configurar el campo de correo
        const emailInput = document.getElementById('correo');
        emailInput.setAttribute('type', 'text');
        emailInput.removeAttribute('pattern');
        emailInput.removeAttribute('required');
        
        if (type === 'create') {
            document.querySelector('.modal-header h2').textContent = 'Agregar Usuario';
            document.getElementById('action').value = 'create';
            document.getElementById('userId').value = '';
            enableAllFields(true);
            
            // Configurar los campos requeridos
            const inputs = form.querySelectorAll('input');
            inputs.forEach(input => {
                if (input.id === 'anexo' || input.id === 'apellido' || 
                    input.id === 'nombre' || input.id === 'ubicacion') {
                    input.setAttribute('required', 'required');
                }
            });
        } else {
            document.querySelector('.modal-header h2').textContent = 'Editar Usuario';
            document.getElementById('action').value = 'update';
            enableAllFields(true);
        }
    }

    function closeModal() {
        modal.classList.remove('show');
        document.body.style.overflow = '';
        form.reset();
    }

    function mostrarError(mensaje) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: mensaje
        });
    }
    
    function mostrarExito(mensaje) {
        Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: mensaje
        });
    }

    function filterAndDisplayUsers(searchTerm = '') {
        const tbody = document.querySelector('.users-table tbody');
        const filteredUsers = allUsers.filter(user => {
            const searchFields = [
                user.ANEXO,
                user.APELLIDO,
                user.NOMBRE,
                user.UBICACION,
                user.CORREO
            ];
            return searchFields.some(field => 
                field.toLowerCase().includes(searchTerm.toLowerCase())
            );
        });

        tbody.innerHTML = '';

        if (filteredUsers.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4">
                        No se encontraron resultados para "${searchTerm}"
                    </td>
                </tr>
            `;
        } else {
            filteredUsers.forEach(user => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${user.ANEXO}</td>
                    <td>${user.APELLIDO}</td>
                    <td>${user.NOMBRE}</td>
                    <td>${user.UBICACION}</td>
                    <td>${user.CORREO}</td>
                    <td class="actions">
                        <button class="edit-btn" data-id="${user.ANEXO}">✏️</button>
                        <button class="delete-btn" data-id="${user.ANEXO}">🗑️</button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        initializeButtonListeners();
    }

    const searchInput = document.getElementById('searchInput');
    const clearSearchBtn = document.getElementById('clearSearch');

    searchInput.addEventListener('input', function(e) {
        const searchTerm = e.target.value.trim();
        filterAndDisplayUsers(searchTerm);
    });

    clearSearchBtn.addEventListener('click', function() {
        searchInput.value = '';
        filterAndDisplayUsers('');
        searchInput.focus();
    });

    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            this.value = '';
            filterAndDisplayUsers('');
        }
    });

    function initializeButtonListeners() {
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const userId = this.getAttribute('data-id');
                const row = this.closest('tr');
                
                form.reset();
                
                // Remover todos los required y cambiar tipo de correo
                const inputs = form.querySelectorAll('input');
                inputs.forEach(input => {
                    input.removeAttribute('required');
                    if (input.id === 'correo') {
                        input.setAttribute('type', 'text');
                        input.removeAttribute('pattern');
                    }
                });
                
                document.getElementById('action').value = 'update';
                
                // Guardar los valores originales
                const anexoInput = document.getElementById('anexo');
                anexoInput.value = row.cells[0].textContent.trim();
                anexoInput.dataset.original = row.cells[0].textContent.trim();
                
                document.getElementById('apellido').value = row.cells[1].textContent.trim();
                document.getElementById('apellido').dataset.original = row.cells[1].textContent.trim();
                
                document.getElementById('nombre').value = row.cells[2].textContent.trim();
                document.getElementById('nombre').dataset.original = row.cells[2].textContent.trim();
                
                document.getElementById('ubicacion').value = row.cells[3].textContent.trim();
                document.getElementById('ubicacion').dataset.original = row.cells[3].textContent.trim();
                
                document.getElementById('correo').value = row.cells[4].textContent.trim();
                document.getElementById('correo').dataset.original = row.cells[4].textContent.trim();

                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            });
        });

        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                userIdToDelete = this.getAttribute('data-id');
                openDeleteModal();
            });
        });
    }

    function openDeleteModal() {
        deleteModal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeDeleteModal() {
        deleteModal.classList.remove('show');
        document.body.style.overflow = '';
        userIdToDelete = null;
    }

    document.getElementById('cancelDelete').addEventListener('click', closeDeleteModal);
    document.getElementById('closeDeleteModal').addEventListener('click', closeDeleteModal);
    deleteModal.addEventListener('click', (e) => {
        if (e.target === deleteModal) closeDeleteModal();
    });

    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (userIdToDelete) {
            const formData = new FormData();
            formData.append('id', userIdToDelete);

            fetch('views/eliminar.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const row = document.querySelector(`button[data-id="${userIdToDelete}"]`).closest('tr');
                    row.remove();
                    allUsers = allUsers.filter(user => user.ANEXO !== userIdToDelete);
                    closeDeleteModal();
                    showNotification('Usuario eliminado correctamente');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error al eliminar el usuario', true);
            });
        }
    });

    function showNotification(message, isError = false) {
        const existingNotification = document.querySelector('.notification');
        if (existingNotification) {
            existingNotification.remove();
        }

        const notification = document.createElement('div');
        notification.className = `notification${isError ? ' error' : ''}`;
        notification.textContent = message;

        document.body.appendChild(notification);

        setTimeout(() => notification.classList.add('show'), 10);

        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData();
        
        // Solo enviar los campos que han sido modificados
        const inputs = form.querySelectorAll('input:not([type="hidden"])');
        let hasChanges = false;
        
        inputs.forEach(input => {
            // Para creación nueva, enviar todos los campos
            if (document.getElementById('action').value === 'create') {
                formData.append(input.id, input.value);
                hasChanges = true;
            } else if (input.value !== input.dataset.original) {
                // Para edición, solo enviar campos modificados
                formData.append(input.id, input.value);
                hasChanges = true;
            }
        });
        
        // Si no hay cambios en modo edición, mostrar mensaje y cerrar modal
        if (!hasChanges) {
            showNotification('No se han realizado cambios');
            closeModal();
            return;
        }
        
        // Agregar campos necesarios
        const action = document.getElementById('action').value;
        formData.append('action', action);
        
        if (action === 'update') {
            formData.append('id', document.getElementById('anexo').dataset.original);
        }
        
        try {
            const url = action === 'create' ? 'views/procesar_crear.php' : 'views/editar.php';
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });

            let result;
            try {
                result = await response.json();
            } catch (e) {
                // Si no podemos parsear el JSON, simplemente cerramos el modal y actualizamos la tabla
                if (action === 'create') {
                    closeModal();
                    window.location.reload(); // Recargar la página para ver el nuevo usuario
                    return;
                }
                throw new Error('Error al procesar la respuesta del servidor');
            }
            
            if (!response.ok) {
                throw new Error(result.message || 'Error al procesar la solicitud');
            }
            
            if (result.success) {
                const nuevoUsuario = {
                    ANEXO: result.data.ANEXO,
                    APELLIDO: result.data.APELLIDO,
                    NOMBRE: result.data.NOMBRE,
                    UBICACION: result.data.UBICACION,
                    CORREO: result.data.CORREO
                };
                
                if (action === 'create') {
                    allUsers.push(nuevoUsuario);
                    allUsers.sort((a, b) => a.UBICACION.localeCompare(b.UBICACION));
                } else {
                    const index = allUsers.findIndex(u => u.ANEXO === document.getElementById('anexo').dataset.original);
                    if (index !== -1) {
                        allUsers[index] = nuevoUsuario;
                        allUsers.sort((a, b) => a.UBICACION.localeCompare(b.UBICACION));
                    }
                }
                filterAndDisplayUsers(searchInput.value.trim());
                showNotification(action === 'create' ? 'Usuario creado correctamente' : 'Usuario actualizado correctamente');
                closeModal();
            } else {
                throw new Error(result.message || 'Error al procesar la solicitud');
            }
        } catch (error) {
            console.error('Error:', error);
            // Si es una creación, simplemente cerramos y recargamos
            if (action === 'create') {
                closeModal();
                window.location.reload();
                return;
            }
            showNotification(error.message || 'Error al procesar la solicitud', true);
        }
    });
    
    function enableAllFields(enable) {
        const fields = ['anexo', 'apellido', 'nombre', 'ubicacion', 'correo'];
        fields.forEach(field => {
            const input = document.getElementById(field);
            input.disabled = !enable;
        });
    }

    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    initializeData();
    initializeButtonListeners();
    filterAndDisplayUsers();

    if (excelBtn && excelModal) {
        excelBtn.addEventListener('click', function(e) {
            e.preventDefault();
            excelModal.classList.add('show');
            document.body.style.overflow = 'hidden';
        });

        if (closeExcelBtn) {
            closeExcelBtn.addEventListener('click', function() {
                excelModal.classList.remove('show');
                document.body.style.overflow = '';
            });
        }

        excelModal.addEventListener('click', function(e) {
            if (e.target === excelModal) {
                excelModal.classList.remove('show');
                document.body.style.overflow = '';
            }
        });

        const modalContent = excelModal.querySelector('.modal-content');
        if (modalContent) {
            modalContent.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    }
});