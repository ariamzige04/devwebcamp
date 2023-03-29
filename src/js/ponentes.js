(function() {//son funciones que se mandan llamar inmediatamente pero no tienen un nombre video 704
    const ponentesInput = document.querySelector('#ponentes');

    if(ponentesInput) {
        let ponentes = [];
        let ponentesFiltrados = [];

        const listadoPonentes = document.querySelector('#listado-ponentes')
        const ponenteHidden = document.querySelector('[name="ponente_id"]')

        obtenerPonentes();
        ponentesInput.addEventListener('input', buscarPonentes)//siempre se va estar lamando cada vez que se escribe dentro del input

        if(ponenteHidden.value) {
           (async() => {
                const ponente = await obtenerPonente(ponenteHidden.value)//"await" detiene las siguientes lineas de codigo hasta que tenga una respuesta
                const { nombre, apellido} = ponente//destructuring

                // Insertar en el HTML
                const ponenteDOM = document.createElement('LI');
                ponenteDOM.classList.add('listado-ponentes__ponente', 'listado-ponentes__ponente--seleccionado');
                ponenteDOM.textContent = `${nombre} ${apellido}`//se agrega el texto

                listadoPonentes.appendChild(ponenteDOM)//se agrega al HTML
           })()
        }

        async function obtenerPonentes() {//aqui se obtienen todos los ponentes 
            const url = `/api/ponentes`;
            const respuesta = await fetch(url);
            const resultado = await respuesta.json();
            formatearPonentes(resultado)//se tiene que formatear los ponentes para que solo traega lo necesario
        }

        async function obtenerPonente(id) {
            const url = `/api/ponente?id=${id}`;
            const respuesta = await fetch(url)
            const resultado = await respuesta.json()
            return resultado;
        }

        function formatearPonentes(arrayPonentes = []) {//toma un array vacio por default
            ponentes = arrayPonentes.map( ponente => {//mapea el array de ponentes
                return {//quiero que retornes solo el nombre y apellido; y su id
                    nombre: `${ponente.nombre.trim()} ${ponente.apellido.trim()}`,
                    id: ponente.id
                } 
            })
        }

        function buscarPonentes(e) {
            const busqueda = e.target.value;//el "e" es el evento, es donde se esta escribiendo en el input (va a obtener el valor del input y se va a actualizar cada vez que escribes)

            if(busqueda.length > 3) {//si es mayor a 3 caracteres empieza a buscar
                const expresion = new RegExp(busqueda, "i");//"RegExp" expresion regular. La i significa que no pasa nada si le pasas minusculas o mayusculas (da igual) (busca un valor en un patron no pasa nada si son mayusculas o minusculas)
                ponentesFiltrados = ponentes.filter(ponente => {//aqui se llena el array de ponentes que estan filtrados y filtra, accede a cada ponente
                    if(ponente.nombre.toLowerCase().search(expresion) != -1) {//lo convierte en minusculas para que no importe las Mayusculas o minusculas, se pone el search y se pasa la expresion regular
                        //el -1 es porque no encontro NADA, asi que si encontro algo... retorna el ponente
                        return ponente
                    }
                })
            } else {
                ponentesFiltrados = []
            }

            mostrarPonentes();
        }

        function mostrarPonentes() {

            while(listadoPonentes.firstChild) {//mientras halla elementos en listados ponentes lo va eliminando
                listadoPonentes.removeChild(listadoPonentes.firstChild)
            }

            if(ponentesFiltrados.length > 0) {//si hay algo en ponentes filtrados lo muestra en pantalla
                ponentesFiltrados.forEach(ponente => {
                    const ponenteHTML = document.createElement('LI');
                    ponenteHTML.classList.add('listado-ponentes__ponente')
                    ponenteHTML.textContent = ponente.nombre;
                    ponenteHTML.dataset.ponenteId = ponente.id
                    ponenteHTML.onclick = seleccionarPonente

                    // Añadir al dom
                    listadoPonentes.appendChild(ponenteHTML)
                })
            } else {
                const noResultados = document.createElement('P')
                noResultados.classList.add('listado-ponentes__no-resultado')
                noResultados.textContent = 'No hay resultados para tu búsqueda'
                listadoPonentes.appendChild(noResultados)              
            }
        }

        function seleccionarPonente(e) {
            const ponente = e.target;

            // Remover la clase previa
            const ponentePrevio = document.querySelector('.listado-ponentes__ponente--seleccionado')
            if(ponentePrevio) {
                ponentePrevio.classList.remove('listado-ponentes__ponente--seleccionado')
            }
            ponente.classList.add('listado-ponentes__ponente--seleccionado')

            ponenteHidden.value = ponente.dataset.ponenteId;//el el input hidden de ponente su valor va a ser igual al dataset (aqui es donde contiene el id del ponente y se envia a la BD)
        }
    }
})();