<?php

/**
 * <b>Descripcion:</b> Clase que <br/>Gestiona las solicitudes tipo CRUD de un elemento
 * <b>Caso de Uso:</b> PANTHER-Seguridad <br/>
 *
 * @author Josué Nicolás Pinzón Villamil <a href = "mailto:jpinzon@j4sysol.com">jpinzon@j4sysol.com</a>
 */
interface IRequest
{
    /**
     * Método para inicializar variables requeridas
     */
    public function init();
    
    /**
     * Método para gestionar solicitudes tipo get
     */
    public function get();
    
    /**
     * Método para gestionar solicitudes tipo post
     */
    public function post($request);
    
    /**
     * Método para gestionar solicitudes tipo put
     */
    public function put($request);
    
    /**
     * Método para gestionar solicitudes tipo delete
     */
    public function delete();
    
}

