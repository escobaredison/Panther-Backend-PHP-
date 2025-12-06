import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface AuthResponse {
    state: number;
    code: number;
    data: any;
}

export interface UserCredentials {
    user: string;
    password: string;
}

export interface RegisterUser extends UserCredentials {
    roles?: number;
}

@Injectable({
    providedIn: 'root'
})
export class AuthService {

    private apiUrl = '/api'; // Proxy configurado para evitar CORS

    private httpOptions = {
        headers: new HttpHeaders({
            'Content-Type': 'application/json'
        })
    };

    constructor(private http: HttpClient) { }

    /**
     * Login de usuario
     */
    login(credentials: UserCredentials): Observable<AuthResponse> {
        return this.http.post<AuthResponse>(
            `${environment.authApi}?PATH_INFO=users/login`,
            credentials,
            this.httpOptions
        );
    }
    /**
     * Registro de usuario
     */
    register(user: RegisterUser): Observable<AuthResponse> {
        return this.http.post<AuthResponse>(
            `${environment.authApi}?PATH_INFO=users/register`,
            user,
            this.httpOptions
        );
    }
    /**
     * Logout: eliminar token local
     */
    logout(): void {
        localStorage.removeItem('token');
    }

    /**
     * Verifica si hay token en localStorage
     */
    isAuthenticated(): boolean {
        return !!localStorage.getItem('token');
    }

    /**
     * Guardar token en localStorage
     */
    saveToken(token: string): void {
        localStorage.setItem('token', token);
    }

    /**
     * Obtener token de localStorage
     */
    getToken(): string | null {
        return localStorage.getItem('token');
    }
}
