(function(){
    const horas = document.querySelector('#horas')

    if(horas) {
        const categoria = document.querySelector('[name="categoria_id"]')
        const dias = document.querySelectorAll('[name="dia"]');
        const inputHiddenDia = document.querySelector('[name="dia_id"]');
        const inputHiddenHora = document.querySelector('[name="hora_id"]');

        categoria.addEventListener('change', terminoBusqueda)
        dias.forEach( dia => dia.addEventListener('change', terminoBusqueda))


        let busqueda = {
            categoria_id: +categoria.value || '',//recupera un valor de categoria (es un input "value") y si no esta presente imprime un string vacio
            dia: +inputHiddenDia.value || ''//el signo de mas "+" lo hace un numero entero y NO un string
        }

        //esto se ejecuta cuando se va a actualizar        
        if(!Object.values(busqueda).includes('')) {//si el objeto de busqueda tiene algo "video 752"
            (async () => {
                await buscarEventos();//"await" va a bloquear las siguientes lineas hasta que finalice

                const id = inputHiddenHora.value;

                // Resaltar la hora actual 
                const horaSeleccionada = document.querySelector(`[data-hora-id="${id}"]`)
                horaSeleccionada.classList.remove('horas__hora--deshabilitada')
                horaSeleccionada.classList.add('horas__hora--seleccionada')

                horaSeleccionada.onclick = seleccionarHora;
            })()
        }

        function terminoBusqueda(e) {//esta funcion se va a llamar cada vez que se cambia el dia y la categoria
            busqueda[e.target.name] = e.target.value;

            // Reiniciar los campos ocultos y el selector de horas
            inputHiddenHora.value = '';//cada vez que cambie el termino de busqueda se reinicia o empieza vacio
            inputHiddenDia.value = '';
            
            const horaPrevia = document.querySelector('.horas__hora--seleccionada')
            if(horaPrevia) {
                horaPrevia.classList.remove('horas__hora--seleccionada')
            }

            if(Object.values(busqueda).includes('')) {//si el objeto de busqueda esta vacio uno de sus campos, retorna todo para que no se ejecute el siguiente codigo
                return
            }

            buscarEventos();
        }

        async function buscarEventos() {
            const { dia, categoria_id } = busqueda
            const url = `/api/eventos-horario?dia_id=${dia}&categoria_id=${categoria_id}`;

            const resultado = await fetch(url);
            const eventos = await resultado.json();
            obtenerHorasDisponibles(eventos);
        }

        function obtenerHorasDisponibles(eventos) {//va a obtener los eventos
            // Reiniciar las horas
            const listadoHoras = document.querySelectorAll('#horas li');
            listadoHoras.forEach(li => li.classList.add('horas__hora--deshabilitada'))

            // Comprobar eventos ya tomados, y quitar la variable de deshabilitado
            const horasTomadas = eventos.map( evento => evento.hora_id);//va a mapear los "eventos" y crea una variable temporal "evento" y retornara el "evento" de la "hora id" (esta variable ya tiene las horas tomadas que se seleccionaron)
            const listadoHorasArray = Array.from(listadoHoras);//convierte el nodelist en "array"

            const resultado = listadoHorasArray.filter( li =>  !horasTomadas.includes(li.dataset.horaId) );//filtra en el listado de horas y retorna loas horas que no estan tomadas 
            resultado.forEach( li => li.classList.remove('horas__hora--deshabilitada'))//por default todos las horas van a estar deshabilitadas pero si estan habilitadas remueven esa clase

            const horasDisponibles = document.querySelectorAll('#horas li:not(.horas__hora--deshabilitada)');//video 742 esto es para que no puedan seleccionar o dar click a una hora que esta deshabilitada
            horasDisponibles.forEach( hora => hora.addEventListener('click', seleccionarHora));
        }

        function seleccionarHora(e) {
            // Deshabilitar la hora previa, si hay un nuevo click
            const horaPrevia = document.querySelector('.horas__hora--seleccionada')
            if(horaPrevia) {//si existe esta clase la remueve, para que solo un elemento se resalte
                horaPrevia.classList.remove('horas__hora--seleccionada')
            }

            // Agregar clase de seleccionado
            e.target.classList.add('horas__hora--seleccionada')

            inputHiddenHora.value = e.target.dataset.horaId;//en el input oculto de hora su valor va a ser el id del elemento que se esta targeteando

            // Llenar el campo oculto de dia
            inputHiddenDia.value = document.querySelector('[name="dia"]:checked').value;//el input hidden se va a llenar su valor dependiendo del input que esta checked y agarra su "valor"
        }
    }
    
})();