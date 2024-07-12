document.addEventListener('DOMContentLoaded', function() {
    const itemsTableBody = document.getElementById('itemsTableBody');

    function toggleForm() {
        var form = document.getElementById('itemForm');
        var buttonText = document.querySelector('.toggle-form-btn');

        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
            buttonText.textContent = 'Ocultar Formulario';
        } else {
            form.style.display = 'none';
            buttonText.textContent = 'Mostrar Formulario';
        }
    }


    function loadItems() {
        fetch('http://localhost/Final-CAC/api/api.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la solicitud al servidor.');
                }
                return response.json();
            })
            .then(data => {
                itemsTableBody.innerHTML = '';
                if (data.peliculas && Array.isArray(data.peliculas)) {
                    data.peliculas.forEach(pelicula => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${pelicula.id}</td>
                            <td>${pelicula.titulo}</td>
                            <td>${pelicula.fecha_lanzamiento}</td>
                            <td>${pelicula.genero}</td>
                            <td>${pelicula.duracion}</td>
                            <td>${pelicula.director}</td>
                            <td>${pelicula.reparto}</td>
                            <td>${pelicula.sinopsis}</td>
                            <td>
                                <button class="btn btn-danger" onclick="deleteItem(${pelicula.id})">Eliminar</button>
                            </td>
                            <td>
                                <button class="btn btn-success" onclick="editItem(
                                    ${pelicula.id}, 
                                    '${pelicula.titulo.replace(/'/g, "\\'")}',
                                    '${pelicula.genero.replace(/'/g, "\\'")}',
                                    '${pelicula.fecha_lanzamiento.replace(/'/g, "\\'")}',
                                    '${pelicula.duracion.replace(/'/g, "\\'")}',
                                    '${pelicula.director.replace(/'/g, "\\'")}',
                                    '${pelicula.reparto.replace(/'/g, "\\'")}',
                                    '${pelicula.sinopsis.replace(/'/g, "\\'")}')">Editar</button>
                            </td>
                        `;
                        itemsTableBody.appendChild(row);
                    });
                } else {
                    console.error('No se encontraron películas');
                }
            })
            .catch(error => {
                console.error('Error al cargar películas:', error.message);
            });
    }

    function deleteItem(id) {
        fetch(`http://localhost/Final-CAC/api/api.php?id=${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error al eliminar la película.');
            }
            return response.json();
        })
        .then(data => {
            loadItems(); // Actualiza la tabla después de eliminar un elemento
        })
        .catch(error => {
            console.error('Error al eliminar película:', error.message);
        });
    }

    

    window.editItem = function(id, titulo, genero, fecha_lanzamiento, duracion, director, reparto, sinopsis) {
        console.log('editItem called with:', { id, titulo, genero, fecha_lanzamiento, duracion, director, reparto, sinopsis });
        document.getElementById('id').value = id;
        document.getElementById('titulo').value = titulo;
        document.getElementById('genero').value = genero;
        document.getElementById('fecha_lanzamiento').value = fecha_lanzamiento;
        document.getElementById('duracion').value = duracion;
        document.getElementById('director').value = director;
        document.getElementById('reparto').value = reparto;
        document.getElementById('sinopsis').value = sinopsis;
        // Cambia el texto del botón de envío
        document.querySelector('#itemForm button[type="submit"]').textContent = 'Actualizar';
    
        // Añade un atributo data-action al formulario
        document.getElementById('itemForm').setAttribute('data-action', 'update');
    };

    document.getElementById('itemForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            id: document.getElementById('id').value,
            titulo: document.getElementById('titulo').value,
            genero: document.getElementById('genero').value,
            fecha_lanzamiento: document.getElementById('fecha_lanzamiento').value,
            duracion: document.getElementById('duracion').value,
            director: document.getElementById('director').value,
            reparto: document.getElementById('reparto').value,
            sinopsis: document.getElementById('sinopsis').value
        };

        const isUpdate = this.getAttribute('data-action') === 'update';
        const method = isUpdate ? 'PUT' : 'POST';
        const url = isUpdate ? `http://localhost/Final-CAC/api/api.php?id=${formData.id}` : 'http://localhost/Final-CAC/api/api.php';

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la solicitud.');
            }
            return response.json();
        })
        .then(data => {
            console.log(data.message);
            loadItems(); // Actualiza la tabla
            this.reset(); // Limpia el formulario
            document.getElementById('id').value = ''; // Limpia el campo id oculto
            document.querySelector('#itemForm button[type="submit"]').textContent = 'Guardar';
            this.removeAttribute('data-action'); // Resetea la acción del formulario
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });

    // Asigna las funciones al objeto window para hacerlas globales
    window.deleteItem = deleteItem;

    // Carga inicial de los items
    loadItems();
});
