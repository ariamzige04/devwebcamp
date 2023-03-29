<?php

namespace Controllers;

use Classes\Paginacion;
use MVC\Router;
use Model\Ponente;
use Intervention\Image\ImageManagerStatic as Image;

class PonentesController {

    public static function index(Router $router) {
        if(!is_admin()) {
            header('Location: /login');
        }
        
        $pagina_actual = $_GET['page'];//toma el numero de la Url 
        $pagina_actual = filter_var($pagina_actual, FILTER_VALIDATE_INT);//lo filtra y solo acepta numero (validacion)

        //si la pagina actual no era un numero o era menor a 1 (numero negativo)
        if(!$pagina_actual || $pagina_actual < 1) {
            header('Location: /admin/ponentes?page=1');
        }
        $registros_por_pagina = 5;
        $total = Ponente::total();//llama este metodo para saber el total de los ponentes
        $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total);

        if($paginacion->total_paginas() < $pagina_actual) {//esto es para que no quieras ir a paginas que no existen y te redirecciona
            header('Location: /admin/ponentes?page=1');
        }

        $ponentes = Ponente::paginar($registros_por_pagina, $paginacion->offset());//estos son la cantidad que va a traer de la BD (ej. del 1 al 9, 10 al 19 etc.)



        $router->render('admin/ponentes/index', [
            'titulo' => 'Ponentes / Conferencistas',
            'ponentes' => $ponentes,
            'paginacion' => $paginacion->paginacion()
        ]);
    }

    public static function crear(Router $router) {
        if(!is_admin()) {
            header('Location: /login');
        }

        $alertas = [];
        $ponente = new Ponente;

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!is_admin()) {
                header('Location: /login');
            }

            // Leer imagen
            //lee el name imagen y el nombre temporal
            if(!empty($_FILES['imagen']['tmp_name'])) {
                
                $carpeta_imagenes = '../public/img/speakers';

                // Crear la carpeta si no existe
                //(si no es un directorio o no existe)
                if(!is_dir($carpeta_imagenes)) {
                    mkdir($carpeta_imagenes, 0755, true);//mkdir se crea el directorio
                }//0755 o 0777 (aveces tienes que subir los permisos para que fucione )

                $imagen_png = Image::make($_FILES['imagen']['tmp_name'])->fit(800,800)->encode('png', 80);
                $imagen_webp = Image::make($_FILES['imagen']['tmp_name'])->fit(800,800)->encode('webp', 80);

                $nombre_imagen = md5( uniqid( rand(), true) );

                $_POST['imagen'] = $nombre_imagen;//se guarda en el post el nombre de laimagen
            } 
            $_POST['redes'] = json_encode( $_POST['redes'], JSON_UNESCAPED_SLASHES );//el json_encode toma un array y lo convierte a un string        
            // JSON_UNESCAPED_SLASHES formate correctamente para que no haiga caracteres extraños
            $ponente->sincronizar($_POST);

            // validar
            $alertas = $ponente->validar();


            // Guardar el registro
            if(empty($alertas)) {

                // Guardar las imagenes
                $imagen_png->save($carpeta_imagenes . '/' . $nombre_imagen . ".png" );
                $imagen_webp->save($carpeta_imagenes . '/' . $nombre_imagen . ".webp" );

                // Guardar en la BD
                $resultado = $ponente->guardar();

                if($resultado) {
                    header('Location: /admin/ponentes');
                }
            }
        }

        $router->render('admin/ponentes/crear', [
            'titulo' => 'Registrar Ponente',
            'alertas' => $alertas,
            'ponente' => $ponente,
            'redes' => json_decode($ponente->redes)
        ]);
    }


    public static function editar(Router $router) {
        if(!is_admin()) {
            header('Location: /login');
        }
        $alertas = [];
        // Validar el ID
        $id = $_GET['id'];
        $id = filter_var($id, FILTER_VALIDATE_INT);

        if(!$id) {
            header('Location: /admin/ponentes');
        }

        // Obtener ponente a Editar
        $ponente = Ponente::find($id);

        if(!$ponente) {
            header('Location: /admin/ponentes');
        }

        $ponente->imagen_actual = $ponente->imagen;//imagen_actual es  la imagen que tiene ese ponente (es la imagen que ya esta guardada)

        if($_SERVER['REQUEST_METHOD'] === 'POST') {

            if(!is_admin()) {
                header('Location: /login');
            }

            if(!empty($_FILES['imagen']['tmp_name'])) {
                
                $carpeta_imagenes = '../public/img/speakers';

                // Crear la carpeta si no existe
                if(!is_dir($carpeta_imagenes)) {
                    mkdir($carpeta_imagenes, 0755, true);
                }

                $imagen_png = Image::make($_FILES['imagen']['tmp_name'])->fit(800,800)->encode('png', 80);
                $imagen_webp = Image::make($_FILES['imagen']['tmp_name'])->fit(800,800)->encode('webp', 80);

                $nombre_imagen = md5( uniqid( rand(), true) );

                $_POST['imagen'] = $nombre_imagen;//si hay una imagen se almecena el nombre de la imagen nuevo
            } else {
                $_POST['imagen'] = $ponente->imagen_actual;//si no hay imagen, se recupera el valor que tenia previamente y se vuelve asignar al Post
            }

            $_POST['redes'] = json_encode( $_POST['redes'], JSON_UNESCAPED_SLASHES );//json decode pasa un string a un objeto , y json encode pasa de array a string
            $ponente->sincronizar($_POST);//a estas alturas ya va tener la imagen nueva o la anterior

            $alertas = $ponente->validar();//trae las validaciones que esta en el Modelo

            if(empty($alertas)) {//esto se ejecuta si hay una imagen Nueva
                if(isset($nombre_imagen)) {
                    $imagen_png->save($carpeta_imagenes . '/' . $nombre_imagen . ".png" );
                    $imagen_webp->save($carpeta_imagenes . '/' . $nombre_imagen . ".webp" );
                }
                $resultado = $ponente->guardar();
                
                if($resultado) {
                    header('Location: /admin/ponentes');
                }
            }

        }

        $router->render('admin/ponentes/editar', [
            'titulo' => 'Actualizar Ponente',
            'alertas' => $alertas,
            'ponente' => $ponente,
            'redes' => json_decode($ponente->redes)
        ]);

    }

    public static function eliminar() {
 
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!is_admin()) {
                header('Location: /login');
            }
            
            $id = $_POST['id'];//lee el id que se esta enviando por Post
            $ponente = Ponente::find($id);//busca el id en la BD
            if(!isset($ponente) ) {//si no existe el ponente se redirecciona    
                header('Location: /admin/ponentes');
            }
            $resultado = $ponente->eliminar();
            if($resultado) {
                header('Location: /admin/ponentes');
            }
        }

    }
}