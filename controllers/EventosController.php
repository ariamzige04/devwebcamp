<?php

namespace Controllers;

use Classes\Paginacion;
use Model\Categoria;
use Model\Dia;
use Model\Evento;
use Model\Hora;
use Model\Ponente;
use MVC\Router;

class EventosController {

    public static function index(Router $router) {
        if(!is_admin()) {//valida para que sea el admin y no una persona extraña
            header('Location: /login');
        }
        
        $pagina_actual = $_GET['page'];//esto es lo que lee de la url ?=15
        $pagina_actual = filter_var($pagina_actual, FILTER_VALIDATE_INT);//valida que se un entero (numero)

        if(!$pagina_actual || $pagina_actual < 1) {//si en la url "no" le pasaron un entero o numero, o es "menor" a 1, hace una redireccion
            header('Location: /admin/eventos?page=1');
        }

        $por_pagina = 10;//esto es lo que va a paginar por cada registro 
        $total = Evento::total();//llama este metodo para saber el total de los eventos
        $paginacion = new Paginacion($pagina_actual, $por_pagina, $total);//

        $eventos = Evento::paginar($por_pagina, $paginacion->offset());//estos son la cantidad que va a traer de la BD (ej. del 1 al 9, 10 al 19 etc.)

        foreach($eventos as $evento) {
            //del modelo de Evento, crea una llave "categoria", y trae la informacion del modelo de Categoria y find busca un registro dependiendo de su id
            $evento->categoria = Categoria::find($evento->categoria_id);//views/eventos/index
            // <?php echo $evento->categoria->nombre;  en el evento accede a la llave nueva que se creo "categoria" y alli accede al "nombre" de la "categoria" 
            $evento->dia = Dia::find($evento->dia_id);
            $evento->hora = Hora::find($evento->hora_id);
            $evento->ponente = Ponente::find($evento->ponente_id);
        }

        $router->render('admin/eventos/index', [
            'titulo' => 'Conferencias y Workshops',
            'eventos' => $eventos,
            'paginacion' => $paginacion->paginacion()//esto contiene todo el html de la paginacion
        ]);
    }

    public static function crear(Router $router) {
        if(!is_admin()) {
            header('Location: /login');
            return;
        }

        $alertas = [];  

        $categorias = Categoria::all('ASC');//asc es de ascendente
        $dias = Dia::all('ASC');
        $horas = Hora::all('ASC');
        $evento = new Evento;

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!is_admin()) {
                header('Location: /login');
                return;
            }
            
            $evento->sincronizar($_POST);

            $alertas = $evento->validar();

            if(empty($alertas)) {
                $resultado = $evento->guardar();
                if($resultado) {
                    header('Location: /admin/eventos');
                    return;
                }
            }
        }

        $router->render('admin/eventos/crear', [
            'titulo' => 'Registrar Evento',
            'alertas' => $alertas,
            'categorias' => $categorias,
            'dias' => $dias,
            'horas' => $horas,
            'evento' => $evento
        ]);
    }

    public static function editar(Router $router) {

        if(!is_admin()) {//valida para que sea el admin y no una persona extraña
            header('Location: /login');
            return;
        }

        $alertas = [];

        $id = $_GET['id'];//lo toma de la url 
        $id = filter_var($id, FILTER_VALIDATE_INT);//solo acepta un entero o numero

        if(!$id) {//si no es un numero hace una redireccion
            header('Location: /admin/eventos');
            return;
        }

        $categorias = Categoria::all('ASC');//toma "todo" de categorias y los muestra de forma "ascendente"
        $dias = Dia::all('ASC');
        $horas = Hora::all('ASC');

        $evento = Evento::find($id);//busca el evento por medio del id
        if(!$evento) {//si no hay ningun evento redirecciona
            header('Location: /admin/eventos');
            return;
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!is_admin()) {
                header('Location: /login');
                return;
            }
            
            $evento->sincronizar($_POST);

            $alertas = $evento->validar();

            if(empty($alertas)) {
                $resultado = $evento->guardar();
                if($resultado) {
                    header('Location: /admin/eventos');
                    return;
                }
            }
        }

        $router->render('admin/eventos/editar', [
            'titulo' => 'Editar Evento',
            'alertas' => $alertas,
            'categorias' => $categorias,
            'dias' => $dias,
            'horas' => $horas,
            'evento' => $evento
        ]);
    }


    public static function eliminar() {

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!is_admin()) {
                header('Location: /login');
                return;
            }

            $id = $_POST['id'];
            $evento = Evento::find($id);
            if(!isset($evento) ) {
                header('Location: /admin/eventos');
                return;
            }
            $resultado = $evento->eliminar();
            if($resultado) {
                header('Location: /admin/eventos');
                return;
            }
        }

    }
}