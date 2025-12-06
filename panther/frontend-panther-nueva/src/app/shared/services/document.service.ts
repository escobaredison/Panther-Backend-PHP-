import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, map } from 'rxjs';
import { environment } from '../../environments/environment';

export interface DocumentType {
    id?: number;
    name_long: string;
    name_short: string;
}

@Injectable({ providedIn: 'root' })
export class DocumentService {
    private baseUrl = environment.pantherApi;

    constructor(private http: HttpClient) { }

    // GET → devuelve el array dentro de "data"
    getDocuments(): Observable<DocumentType[]> {
        return this.http
            .get<{ state: string; code: number; data: DocumentType[] }>(
                `${this.baseUrl}?PATH_INFO=documenttype`
            )
            .pipe(map(res => res.data));
    }

    // GET por id (si tu backend lo soporta)
    getDocument(id: number): Observable<DocumentType> {
        return this.http
            .get<{ state: string; code: number; data: DocumentType }>(
                `${this.baseUrl}?PATH_INFO=documenttype&id=${id}`
            )
            .pipe(map(res => res.data));
    }

    // POST → crear
    createDocument(doc: DocumentType): Observable<any> {
        return this.http.post(`${this.baseUrl}?PATH_INFO=documenttype`, doc);
    }

    // PUT → actualizar (id en body, no en URL)
    updateDocument(doc: DocumentType): Observable<any> {
        return this.http.put(`${this.baseUrl}?PATH_INFO=documenttype`, doc);
    }

    // DELETE → eliminar (id en body, no en URL)
    deleteDocument(id: number): Observable<any> {
        return this.http.request('delete', `${this.baseUrl}?PATH_INFO=documenttype`, {
            body: { id }
        });
    }
}
