// src/app/shared/services/invoice.service.ts
import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from '../../environments/environment';
import { Observable } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class InvoiceService {
    constructor(private http: HttpClient) { }

    crearFactura(body: any): Observable<any> {
        const token = localStorage.getItem('token') || '';
        const headers = new HttpHeaders({
            'Content-Type': 'application/json',
            'Authorization': token // viene con "Bearer ...", no dupliques
        });
        return this.http.post(environment.clarisaInvoiceUrl, body, { headers });
    }
}
