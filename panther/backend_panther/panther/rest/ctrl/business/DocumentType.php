<?php
require_once 'model/business/DocumentTypeModel.php';
require_once 'querys/business/DocumentTypeQuery.php';
require_once 'view/ViewJSON.php';
require_once 'cxn/Connection.php';

class Documenttype   // ← NOMBRE CORRECTO PARA PANTHER
{
    private $view;

    public function init()
    {
        $this->view = new ViewJSON();
    }

    // ====================================================
    // GET → LISTAR TODOS LOS TIPOS DE DOCUMENTO
    // ====================================================
    public function get($request)
    {
        try {
            $conn = Connection::getInstance()->getConnection();
            $stmt = $conn->query(DocumentTypeQuery::getAll());
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->view->response([
                "state" => "OK",
                "code" => 200,
                "data" => $data
            ]);
        } catch (Exception $e) {
            return $this->view->response([
                "state" => "ERROR",
                "code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    // ====================================================
    // POST → CREAR TIPO DE DOCUMENTO
    // ====================================================
    public function post($request)
    {
        try {
            $body = json_decode(file_get_contents("php://input"), true);

            if (!$body || empty($body["name_long"]) || empty($body["name_short"])) {
                throw new Exception("Faltan datos obligatorios: name_long y name_short");
            }

            $conn = Connection::getInstance()->getConnection();
            $stmt = $conn->prepare(DocumentTypeQuery::insert());
            $stmt->execute([$body["name_long"], $body["name_short"]]);

            return $this->view->response([
                "state" => "OK",
                "code" => 201,
                "message" => "Tipo de documento creado correctamente"
            ]);
        } catch (Exception $e) {
            return $this->view->response([
                "state" => "ERROR",
                "code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    // ====================================================
    // PUT → EDITAR TIPO DE DOCUMENTO
    // ====================================================
    public function put($request)
    {
        try {
            $body = json_decode(file_get_contents("php://input"), true);
            $id = $body["id"] ?? ($request[0] ?? null);

            if (!$id) {
                throw new Exception("ID requerido");
            }

            if (empty($body["name_long"]) || empty($body["name_short"])) {
                throw new Exception("Faltan campos obligatorios");
            }

            $conn = Connection::getInstance()->getConnection();

            // Verificar existencia
            $check = $conn->prepare("SELECT id FROM document_type WHERE id = ?");
            $check->execute([$id]);

            if ($check->rowCount() === 0) {
                throw new Exception("El tipo de documento no existe");
            }

            // Actualizar
            $stmt = $conn->prepare(DocumentTypeQuery::update());
            $stmt->execute([
                $body["name_long"],
                $body["name_short"],
                $id
            ]);

            return $this->view->response([
                "state" => "OK",
                "code" => 200,
                "message" => "Tipo de documento actualizado correctamente"
            ]);
        } catch (Exception $e) {
            return $this->view->response([
                "state" => "ERROR",
                "code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    // ====================================================
    // DELETE → ELIMINAR TIPO DE DOCUMENTO
    // ====================================================
    public function delete($request)
    {
        try {
            $body = json_decode(file_get_contents("php://input"), true);
            $id = $body["id"] ?? ($request[0] ?? null);

            if (!$id) {
                throw new Exception("ID requerido");
            }

            $conn = Connection::getInstance()->getConnection();

            // Verificar existencia
            $check = $conn->prepare("SELECT id FROM document_type WHERE id = ?");
            $check->execute([$id]);

            if ($check->rowCount() === 0) {
                throw new Exception("El tipo de documento no existe");
            }

            // Eliminar
            $stmt = $conn->prepare(DocumentTypeQuery::delete());
            $stmt->execute([$id]);

            return $this->view->response([
                "state" => "OK",
                "code" => 200,
                "message" => "Tipo de documento eliminado correctamente"
            ]);
        } catch (Exception $e) {
            return $this->view->response([
                "state" => "ERROR",
                "code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }
}
