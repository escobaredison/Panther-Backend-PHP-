<?php
/**
 * Constantes de claves de respuesta
 */
define("STATE", "state");
define("CODE", "code");
define("DATA", "data");

/**
 * <b>Descripcion:</b> Clase que gestiona el contenido de la respuesta
 * <b>Caso de Uso:</b> PANTHER-Seguridad <br/>
 *
 * @author Josué Nicolás Pinzón Villamil
 */
class ContentBody
{
    private $state;
    private $code;
    private $data;

    public function __construct($state, $code, $data)
    {
        $this->state = $state;
        $this->code = $code;
        $this->data = $data;
    }

    // Getters
    public function getState()
    {
        return $this->state;
    }
    public function getCode()
    {
        return $this->code;
    }
    public function getData()
    {
        return $this->data;
    }

    // Setters
    public function setState($state)
    {
        $this->state = $state;
    }
    public function setCode($code)
    {
        $this->code = $code;
    }
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Devuelve el cuerpo como array asociativo
     */
    public function getBodyArray()
    {
        return [
            STATE => $this->state,
            CODE => $this->code,
            DATA => $this->data
        ];
    }

    /**
     * Devuelve el cuerpo como JSON listo para enviar
     */
    public function toJSON()
    {
        header('Content-Type: application/json; charset=utf-8');
        return json_encode($this->getBodyArray(), JSON_UNESCAPED_UNICODE);
    }

    /**
     * Envía directamente la respuesta al cliente
     */
    public function send()
    {
        echo $this->toJSON();
        exit;
    }
}
