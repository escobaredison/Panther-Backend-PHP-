import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class LocationService {
    constructor(private http: HttpClient) { }

    getCountries(): Observable<any[]> {
        return this.http.get<any[]>('assets/data/countries.json');
    }

    getDepartments(countryId: number): Observable<any[]> {
        return this.http.get<any[]>('assets/data/states.json');
    }

    getCities(departmentId: number): Observable<any[]> {
        return this.http.get<any[]>('assets/data/cities.json');
    }
}
