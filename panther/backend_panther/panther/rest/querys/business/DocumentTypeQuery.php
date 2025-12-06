<?php
class DocumentTypeQuery
{
    public static function getAll()
    {
        // ✅ Ajustado: solo selecciona las columnas que existen
        return "SELECT id, name_long, name_short FROM document_type";
    }

    public static function insert()
    {
        return "INSERT INTO document_type (name_long, name_short) VALUES (?, ?)";
    }

    public static function update()
    {
        return "UPDATE document_type SET name_long=?, name_short=? WHERE id=?";
    }

    public static function delete()
    {
        return "DELETE FROM document_type WHERE id=?";
    }
}
