<?php
// Incluir ViewAPI primero
require_once __DIR__ . '/ViewAPI.php';

class ViewJSON extends ViewAPI
{
    /**
     * Imprime el cuerpo en formato JSON.
     * Acepta tanto ContentBody como arrays simples.
     */
    public function viewPrint($body)
    {
        // Limpiar cualquier salida previa
        if (ob_get_length()) {
            ob_clean();
        }

        // Si es ContentBody, convertirlo a array
        if ($body instanceof ContentBody) {
            http_response_code($body->getCode() ?? 200);
            $body = [
                'state' => $body->getState() ?? 'OK',
                'code' => $body->getCode() ?? 200,
                'data' => $body->getData() ?? []
            ];
        } elseif (is_array($body)) {
            http_response_code($body['code'] ?? 200);
        } else {
            http_response_code(200);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($body, JSON_UNESCAPED_UNICODE);
        exit();
    }

    /**
     * Método de compatibilidad para controladores.
     * Permite usar $this->view->response([...]) en vez de viewPrint.
     */
    public function response($arr)
    {
        $this->viewPrint($arr);
    }
}
