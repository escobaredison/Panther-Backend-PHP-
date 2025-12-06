import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { HttpClientModule } from '@angular/common/http';
import { AuthService } from '../../shared/services/auth.service';

@Component({
    selector: 'app-signup',
    standalone: true,
    imports: [CommonModule, FormsModule, RouterLink, HttpClientModule],
    templateUrl: './signup.component.html',
    styleUrls: ['./signup.component.scss']
})
export class SignupComponent {

    user = {
        user: '',
        emailUser: '',
        password: ''
    };

    error: string | null = null;
    success: string | null = null;
    loading = false;

    constructor(
        private authService: AuthService,
        private router: Router
    ) { }

    register() {
        this.loading = true;
        this.error = null;
        this.success = null;

        const data = {
            user: this.user.user.trim(),
            password: this.user.password.trim(),
            roles: 2
        };

        console.log("🔵 Datos ENVIADOS a Panther:", data);

        this.authService.register(data).subscribe({
            next: (res: any) => {
                this.loading = false;
                console.log("🔥 RESPUESTA REAL:", res);

                if (res && (res.state === 201 || res.code === 201)) {
                    // Limpiar campos
                    this.user.user = '';
                    this.user.password = '';
                    this.user.emailUser = '';

                    // Mensaje amigable
                    this.success = '✅ Usuario registrado correctamente. Ahora puedes iniciar sesión.';

                    // Redirigir al login después de 1 segundo
                    setTimeout(() => this.router.navigate(['/login']), 1000);
                } else {
                    this.error = res?.data?.message || '❌ No se pudo registrar el usuario';
                }
            },
            error: (err: any) => {
                this.loading = false;
                console.error("❌ Error Panther:", err);
                this.error = err.error?.data?.message || '❌ No se pudo registrar el usuario';
            }
        });
    }
}
