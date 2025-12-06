import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../environments/environment';
import { Observable, map } from 'rxjs';
import { Person } from '../models/person.model';

@Injectable({ providedIn: 'root' })
export class PersonService {
    // 👈 Asegúrate que en environment.pantherApi NO tenga slash al final
    // ej: 'http://localhost/backend_panther/panther/rest'
    private apiUrl = environment.pantherApi;

    constructor(private http: HttpClient) { }

    getPersons(): Observable<Person[]> {
        return this.http.get<any>(`${this.apiUrl}/persons`).pipe(
            map(response => response.data as Person[])
        );
    }

    createPerson(person: Person): Observable<any> {
        return this.http.post(`${this.apiUrl}/persons`, person);
    }

    updatePerson(person: Person): Observable<any> {
        return this.http.put(`${this.apiUrl}/persons/${person.id}`, person);
    }

    deletePerson(id: number): Observable<any> {
        return this.http.delete(`${this.apiUrl}/persons/${id}`);
    }
}
