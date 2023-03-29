(function() {//son funciones que se mandan llamar inmediatamente pero no tienen un nombre video 704

    const tagsInput = document.querySelector('#tags_input')//es el input donde se escribe los tags

    if(tagsInput) {//si existe este elemento se ejecuta este codigo y si no pues lo ignora

        const tagsDiv = document.querySelector('#tags');//aqui son los tags que estan enlistados y separado por coma
        const tagsInputHidden = document.querySelector('[name="tags"]');//es el input oculto

        let tags = [];

        // Recuperar del input oculto
        //Contexto: php imprime los valores en el input oculto (cuando se esta Actualizando los tags del ponente) y js transforma el string a un array
        if(tagsInputHidden.value !== '') {//si tiene algo..
            tags = tagsInputHidden.value.split(',');//pasa de string a arreglo, se separan con una coma ","
            mostrarTags();
        }
 
        // Escuchar los cambios en el input
        //keypress (cada vez que se presiona una tecla)
        tagsInput.addEventListener('keypress', guardarTag)

        function guardarTag(e) {//se ejecuta cada vez que se escribe una coma (,)
            if(e.keyCode === 44) {//el keCode es el codigo o numero asci (ej. 64 = @)
                if(e.target.value.trim() === '' || e.target.value < 1) { //si es igual a un string vacio o la extencion es menor a 1, no se ejecuta el siguiente codigo
                    return
                }
                e.preventDefault();
                tags = [...tags, e.target.value.trim()];//del array de tags, toma una copia ...tags
                tagsInput.value = '';//se limpia el input
                mostrarTags();
            }
        }

        function mostrarTags() {
            tagsDiv.textContent = '';//son las palabras separadas por coma (tambien sirve para limpiar los tags anteriores y poner los nuevos)
            tags.forEach(tag => {//itera sobre los tags que ya estan el array y se separaron por comas
                const etiqueta = document.createElement('LI');
                etiqueta.classList.add('formulario__tag')
                etiqueta.textContent = tag;//el contenido es igual al tag que se almaceno en el array
                etiqueta.ondblclick = eliminarTag
                tagsDiv.appendChild(etiqueta)//se agrega al DOM o al HTML
            })
            actualizarInputHidden();
        }   

        function eliminarTag(e) {
            e.target.remove();//se remueve del html
            tags = tags.filter(tag => tag !== e.target.textContent)//trae todos los tags que no sean a los que yo le di click (remueve al tag que le dieron doble click)
            actualizarInputHidden();
        }

        function actualizarInputHidden() {
           tagsInputHidden.value = tags.toString();//en el input oculto en el name, se actualiza, (se transforma el array a un string) video 705 
           //el input hidden se actualiza con los tags que se escribieron o se borraron
        }
    }

})();