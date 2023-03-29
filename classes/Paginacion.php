<?php 

namespace Classes;

class Paginacion {
    public $pagina_actual;
    public $registros_por_pagina;
    public $total_registros;
    //                          toma por default estos datos, por si no se pasa despues toma esto (se puede modificar video 720)
    public function __construct($pagina_actual = 1, $registros_por_pagina = 10, $total_registros = 0 )
    {
        $this->pagina_actual = (int) $pagina_actual;//(int) es Castear (si se pasa un string Ej. "1" se convierte en un entero o numero 1)
        $this->registros_por_pagina = (int) $registros_por_pagina;
        $this->total_registros = (int) $total_registros;
    }

    public function offset() {
        return $this->registros_por_pagina * ($this->pagina_actual - 1);//10 * 1 = 10 - 1 = "9"
        //Ejemplo: 10 * 2 = 20 - 1 = "19"
    }

    public function total_paginas() {//ceil retorna hacia arriba Ej. 14.3 = 15
        $total = ceil($this->total_registros / $this->registros_por_pagina);
        $total == 0 ? $total = 1 : $total = $total;
        return $total;
    }

    public function pagina_anterior() {
        $anterior = $this->pagina_actual - 1;//ejemplo estoy en la 5 y me resta 1, osea pagina 4
        return ($anterior > 0) ? $anterior : false;//si la pagina anterior es mayor a 0 retorna "anterior" y si no retorna un false (pequeña validacion)
    }

    public function pagina_siguiente() {
        $siguiente = $this->pagina_actual + 1;
        return ($siguiente <= $this->total_paginas()) ? $siguiente : false;//si la pagina siguiente es menor o igual al total de paginas (esta bien, es un numero menor) retorna siguiente, y si no retorna false
    }

    public function enlace_anterior() {
        $html = '';
        if($this->pagina_anterior()) {//si es posible ir a la pagina anterior
            $html .= "<a class=\"paginacion__enlace paginacion__enlace--texto\" href=\"?page={$this->pagina_anterior()}\">&laquo; Anterior </a>";
        }
        return $html;
    }

    public function enlace_siguiente() {//si es posible ir a la pagina siguiente
        $html = '';
        if($this->pagina_siguiente()) {
            $html .= "<a class=\"paginacion__enlace paginacion__enlace--texto\" href=\"?page={$this->pagina_siguiente()}\">Siguiente &raquo;</a>";
        }
        return $html;
    }

    public function numeros_paginas() {
        $html = '';
        for($i = 1; $i <= $this->total_paginas(); $i++) {//si es igual a uno y menor al total de paginas, lo incrementa
            if($i === $this->pagina_actual ) {//imprime todos los numeritos, si el numero es igual a la pagina actual imprime un Span, (esto es para no darle click al enlace de la misma pagina que estas)
                $html .= "<span class=\"paginacion__enlace paginacion__enlace--actual \">{$i}</span>";
            } else {
                $html .= "<a class=\"paginacion__enlace paginacion__enlace--numero \" href=\"?page={$i}\">{$i}</a>";
            }
        }

        return $html;
    }

    public function paginacion() {
        $html = '';
        if($this->total_registros > 1) {
            $html .= '<div class="paginacion">';
            $html .= $this->enlace_anterior();
            $html .= $this->numeros_paginas();
            $html .= $this->enlace_siguiente();
            $html .= '</div>';
        }

        return $html;
    }

}