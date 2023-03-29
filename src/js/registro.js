import Swal from 'sweetalert2';

(function(){
    let eventos = [];//objeto

    const resumen = document.querySelector('#registro-resumen')//aqui es donde se va a poner en el html
    if(resumen) {
            
        const eventosBoton = document.querySelectorAll('.evento__agregar');//selecciona todos los botones de los eventos
        eventosBoton.forEach(boton => boton.addEventListener('click', seleccionarEvento))//itera en cada uno de ellos

        const formularioRegistro = document.querySelector('#registro');
        formularioRegistro.addEventListener('submit', submitFormulario)

        mostrarEventos();

        function seleccionarEvento({target}) {//este target es para evitar el e.target

            if(eventos.length < 5) {//si tiene menos de 5 eventos (0 a 5)
                // Deshabilitar el evento
                target.disabled = true//desabilita el btn para que no se pueda agregar multiples veces el evento
                eventos = [...eventos, {//toma una copia de lo que hay en eventos
                    //se agrega el id y titulo al objeto de eventos
                    id: target.dataset.id,//selecciona el id del dataset
                    titulo: target.parentElement.querySelector('.evento__nombre').textContent.trim()//te vas al elemento padre del elemento y seleccionas el h4 (el contenido)
                }]

                mostrarEventos();
            } else {//si quiere poner mas de 5 eventos...
                Swal.fire({
                    title: 'Error',
                    text: 'Máximo 5 eventos por registro',
                    icon: 'error',
                    confirmButtonText: 'OK'
                })
            }
        }

        function mostrarEventos() {
            // LIMPIAR EL HTML
            limpiarEventos();

            if(eventos.length > 0 ) {//asegura que hay eventos
                eventos.forEach( evento => {
                    const eventoDOM = document.createElement('DIV')//se crea un div
                    eventoDOM.classList.add('registro__evento')//se añade la clase

                    const titulo = document.createElement('H3')//se crea un h3
                    titulo.classList.add('registro__nombre')//se añade la clase
                    titulo.textContent = evento.titulo//aqui ya se esta pasando el titulo donde "seleccionas el evento"

                    const botonEliminar = document.createElement('BUTTON')//se crea un boton
                    botonEliminar.classList.add('registro__eliminar')//se añade la clase
                    botonEliminar.innerHTML = `<i class="fa-solid fa-trash"></i>`;//se agrega el icono
                    botonEliminar.onclick = function() {//esto es para eliminar los eventos que ya seleccionastes
                        eliminarEvento(evento.id)//aqui ya se esta pasando el id del evento
                    }


                    // renderizar en el html
                    eventoDOM.appendChild(titulo)
                    eventoDOM.appendChild(botonEliminar)
                    resumen.appendChild(eventoDOM)
                })
            } else {
                const noRegistro = document.createElement('P')
                noRegistro.textContent = 'No hay eventos, añade hasta 5 del lado izquierdo'
                noRegistro.classList.add('registro__texto')
                resumen.appendChild(noRegistro)
            }
        }

        function eliminarEvento(id) {
            eventos = eventos.filter( evento => evento.id !== id)//se va a traer todos los eventos cuyo id sea diferente al id que le estamos dando click (se trae todos los eventos otra vez pero al que le diste click NO)
            const botonAgregar = document.querySelector(`[data-id="${id}"]`)//si elimina el evento que no queria, se vuelve habilitar otra vez el boton para agregarlo otra vez
            botonAgregar.disabled = false//video 787
            mostrarEventos();
        }

        function limpiarEventos() {
            while(resumen.firstChild) {//se resumen tiene elementos hijos 
                resumen.removeChild(resumen.firstChild);//remueve todo el HTML
            }
        }

        async function submitFormulario(e) {
            e.preventDefault();

            // Obtener el regalo
            const regaloId = document.querySelector('#regalo').value;//esto es el valor del input select, donde tinene sus options
            const eventosId = eventos.map(evento => evento.id)//map permite iterar sobre el array y retornar los valores que consideramos importantes. Accede a cada evento y retorna el evento id

            if(eventosId.length === 0 || regaloId === '') {//si el array de eventosId esta vacio (0) O el regalo id esta vacio 
                 Swal.fire({
                    title: 'Error',
                    text: 'Elige al menos un Evento y un Regalo',
                    icon: 'error',
                    confirmButtonText: 'OK'
                })
                return;
            }

            // Objeto de formdata
            const datos = new FormData();//es para enviar datos
            datos.append('eventos', eventosId)//eventos es un string con todos los eventos
            datos.append('regalo_id', regaloId)//este es solo un id

            const url = '/finalizar-registro/conferencias';//es la misma url donde se ejecuta este mismo codigo
            const respuesta = await fetch(url, {
                method: 'POST',
                body: datos
            })
            const resultado = await respuesta.json();

            console.log(resultado)

            if(resultado.resultado) {//resultado es la variable JS y el .resultado (aqui esta accediendo al json que tubo como resultado "true o false")
                Swal.fire(
                    'Registro Exitoso',
                    'Tus conferencias se han almacenado y tu registro fue exitoso, te esperamos en DevWebCamp',
                    'success'
                ).then( () => location.href = `/boleto?id=${resultado.token}`) //una vez que presiona el boton de aceptar hace una redireccion 
            } else {
                Swal.fire({
                    title: 'Error',
                    text: 'Hubo un error',
                    icon: 'error',
                    confirmButtonText: 'OK'
                }).then( () => location.reload() )
            }

        }

    }
})();