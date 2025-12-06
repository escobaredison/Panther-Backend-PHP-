import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink, RouterModule } from '@angular/router';
import { CommonModule } from '@angular/common';
import { HttpClientModule } from '@angular/common/http';
import { AuthService } from '../../shared/services/auth.service';

@Component({
    selector: 'app-login',
    standalone: true,
    imports: [CommonModule, FormsModule, RouterLink, RouterModule, HttpClientModule],
    templateUrl: './login.component.html',
    styleUrls: ['./login.component.scss']
})
export class LoginComponent {

    credentials = {
        user: '',
        password: ''
    };
    error: string | null = null;
    success: string | null = null;
    loading = false;

    constructor(
        private authService: AuthService,
        private router: Router
    ) { }

    login() {
        this.loading = true;
        this.error = null;
        this.success = null;

        this.authService.login(this.credentials).subscribe({
            next: (resp: any) => {
                console.log('Respuesta backend:', resp);
                this.loading = false;

                if (resp && (resp.state === 'SUCCESS' || resp.state === 200) && resp.code === 200) {
                    // Usa el campo correcto que devuelve el backend
                    localStorage.setItem('token', resp.data.token);
                    localStorage.setItem('roles', resp.data.roles ?? '');

                    this.success = `✅ Bienvenido ${this.credentials.user}, ingreso correcto al dashboard.`;

                    this.credentials.user = '';
                    this.credentials.password = '';

                    setTimeout(() => this.router.navigate(['/dashboard/home']), 1000);
                } else {
                    this.error = resp?.data?.message || '❌ Usuario o contraseña incorrectos';
                }
            },
            error: (err) => {
                this.loading = false;
                this.error = err?.error?.data?.message || '❌ Usuario o contraseña incorrectos';
            }
        });

    }
}
