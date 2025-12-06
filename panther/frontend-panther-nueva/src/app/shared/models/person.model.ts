export interface Person {
    id?: number;
    name: string;              // coincide con la columna 'name'
    lastName: string;
    phone: string;
    document_type_id?: number; // coincide con la columna 'document_type_id'
    country_id?: number;
    state_id?: number;         // coincide con la columna 'state_id'
    city_id?: number;
}
