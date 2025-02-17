document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('userModal');
    const deleteModal = document.getElementById('deleteModal');
    const addUserBtn = document.querySelector('.add-user-btn');
    const closeBtn = document.querySelector('.close-btn');
    const cancelBtn = document.querySelector('.cancel-btn');
    const form = document.getElementById('userForm');
    let userIdToDelete = null;
    let allUsers = [];

    // Agregar estas variables al inicio junto con las otras declaraciones
    const excelBtn = document.getElementById('excelBtn');
    const excelModal = document.getElementById('excelModal');
    const closeExcelBtn = document.getElementById('closeExcelModal');

    console.log('Excel button:', excelBtn); // Debug
    console.log('Excel modal:', excelModal); // Debug
    console.log('Close button:', closeExcelBtn); // Debug

    // Inicializar datos
    function initializeData() {
        const rows = document.querySelectorAll('.users-table tbody tr');
        allUsers = Array.from(rows).map(row => ({
            ANEXO: row.cells[0].textContent.trim(),
            APELLIDO: row.cells[1].textContent.trim(),
            NOMBRE: row.cells[2].textContent.trim(),
            UBICACION: row.cells[3].textContent.trim(),
            CORREO: row.cells[4].textContent.trim()
        }));

        // Ordenar usuarios por número de anexo
        allUsers.sort((a, b) => {
            const anexoA = parseInt(a.ANEXO) || 0;
            const anexoB = parseInt(b.ANEXO) || 0;
            return anexoA - anexoB;
        });
    }

    // Event listeners para los modales y botones
    addUserBtn.addEventListener('click', () => openModal('create'));
    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });

    function openModal(type = 'edit') {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        form.reset();
        
        if (type === 'create') {
            document.querySelector('.modal-header h2').textContent = 'Agregar Usuario';
            document.getElementById('action').value = 'create';
            document.getElementById('userId').value = '';
            enableAllFields(true);
            // Restaurar required en los campos para crear
            const inputs = form.querySelectorAll('input');
            inputs.forEach(input => {
                if (input.id !== 'anexo') {
                    input.setAttribute('required', 'required');
                }
                if (input.id === 'correo') {
                    input.setAttribute('type', 'email');
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

    // Función para filtrar y mostrar usuarios
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

        // Limpiar tabla
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

    // Event listeners para la búsqueda
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

    // Inicializar event listeners de botones
    function initializeButtonListeners() {
        // Botones de editar
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const userId = this.getAttribute('data-id');
                const row = this.closest('tr');
                
                // Limpiar el formulario
                form.reset();
                
                // Remover required y cambiar tipo de email
                const inputs = form.querySelectorAll('input');
                inputs.forEach(input => {
                    input.removeAttribute('required');
                    if (input.id === 'correo') {
                        input.setAttribute('type', 'text');
                    }
                });
                
                // Establecer el modo de edición
                document.getElementById('action').value = 'update';
                
                // Llenar los campos con los valores actuales
                const anexoInput = document.getElementById('anexo');
                anexoInput.value = row.cells[0].textContent.trim();
                anexoInput.dataset.original = row.cells[0].textContent.trim();
                
                document.getElementById('apellido').value = row.cells[1].textContent.trim();
                document.getElementById('nombre').value = row.cells[2].textContent.trim();
                document.getElementById('ubicacion').value = row.cells[3].textContent.trim();
                document.getElementById('correo').value = row.cells[4].textContent.trim();
                
                // Configurar detectores de cambios
                const formInputs = form.querySelectorAll('input:not([type="hidden"])');
                formInputs.forEach(input => {
                    input.dataset.original = input.value;
                    input.addEventListener('input', function() {
                        const hasChanged = this.value !== this.dataset.original;
                        this.classList.toggle('modified', hasChanged);
                    });
                });

                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            });
        });

        // Botones de eliminar
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                userIdToDelete = this.getAttribute('data-id');
                openDeleteModal();
            });
        });
    }

    // Modal de eliminación
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
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    });

    // Manejar el formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const action = document.getElementById('action').value;
        
        const newFormData = new FormData();
        newFormData.append('action', action);
        
        const anexoOriginal = document.getElementById('anexo').dataset.original;
        
        if (action === 'update') {
            if (!anexoOriginal) {
                return;
            }
            newFormData.append('id', anexoOriginal);

            const fields = ['apellido', 'nombre', 'ubicacion', 'correo', 'anexo'];
            let hasChanges = false;

            fields.forEach(field => {
                const input = document.getElementById(field);
                if (input && input.classList.contains('modified')) {
                    if (field === 'correo' && input.value && !isValidEmail(input.value)) {
                        return;
                    }
                    newFormData.append(field, input.value);
                    hasChanges = true;
                }
            });

            if (!hasChanges) {
                return;
            }
        } else {
            const fields = ['anexo', 'apellido', 'nombre', 'ubicacion', 'correo'];
            const requiredFields = ['anexo', 'apellido', 'nombre'];
            
            let missingFields = false;
            fields.forEach(field => {
                const input = document.getElementById(field);
                if (input) {
                    if (requiredFields.includes(field) && !input.value) {
                        missingFields = true;
                    }
                    newFormData.append(field, input.value);
                }
            });

            if (missingFields) {
                return;
            }
        }

        const url = action === 'create' ? 'views/procesar_crear.php' : 'views/editar.php';

        fetch(url, {
            method: 'POST',
            body: newFormData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (action === 'update') {
                    const row = document.querySelector(`button[data-id="${anexoOriginal}"]`).closest('tr');
                    if (!row) {
                        return;
                    }
                    const updatedUser = data.user;
                    
                    const newRow = document.createElement('tr');
                    newRow.innerHTML = `
                        <td>${updatedUser.ANEXO}</td>
                        <td>${updatedUser.APELLIDO}</td>
                        <td>${updatedUser.NOMBRE}</td>
                        <td>${updatedUser.UBICACION}</td>
                        <td>${updatedUser.CORREO}</td>
                        <td class="actions">
                            <button class="edit-btn" data-id="${updatedUser.ANEXO}">✏️</button>
                            <button class="delete-btn" data-id="${updatedUser.ANEXO}">🗑️</button>
                        </td>
                    `;

                    row.parentNode.replaceChild(newRow, row);

                    const index = allUsers.findIndex(u => u.ANEXO === anexoOriginal);
                    if (index !== -1) {
                        allUsers[index] = updatedUser;
                    }

                    if (updatedUser.ANEXO !== anexoOriginal) {
                        allUsers.sort((a, b) => {
                            const anexoA = parseInt(a.ANEXO) || 0;
                            const anexoB = parseInt(b.ANEXO) || 0;
                            return anexoA - anexoB;
                        });
                        filterAndDisplayUsers(document.getElementById('searchInput').value.trim());
                    }

                    initializeButtonListeners();
                } else {
                    allUsers.push(data.user);
                    allUsers.sort((a, b) => {
                        const anexoA = parseInt(a.ANEXO) || 0;
                        const anexoB = parseInt(b.ANEXO) || 0;
                        return anexoA - anexoB;
                    });
                    filterAndDisplayUsers(document.getElementById('searchInput').value.trim());
                    initializeButtonListeners();
                }
                
                closeModal();
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });

    // Agregar función para habilitar/deshabilitar campos
    function enableAllFields(enable) {
        const fields = ['anexo', 'apellido', 'nombre', 'ubicacion', 'correo'];
        fields.forEach(field => {
            const input = document.getElementById(field);
            input.disabled = !enable;
        });
    }

    // Función auxiliar para validar email
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    // Inicializar la aplicación
    initializeData();
    initializeButtonListeners();
    filterAndDisplayUsers();

    // Agregar los event listeners para el modal de Excel
    if (excelBtn && excelModal) {
        console.log('Excel button and modal found'); // Para debugging

        excelBtn.addEventListener('click', function(e) {
            console.log('Excel button clicked'); // Debug
            e.preventDefault();
            excelModal.classList.add('show');
            document.body.style.overflow = 'hidden';
        });

        if (closeExcelBtn) {
            closeExcelBtn.addEventListener('click', function() {
                console.log('Close button clicked'); // Debug
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

        // Prevenir que los clics dentro del contenido cierren el modal
        const modalContent = excelModal.querySelector('.modal-content');
        if (modalContent) {
            modalContent.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    } else {
        console.log('Excel button or modal not found'); // Debug
    }
});

