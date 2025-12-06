import { Routes } from '@angular/router';
import { LoginComponent } from './auth/login/login.component';
import { SignupComponent } from './auth/signup/signup.component';
import { DashboardComponent } from './dashboard/dashboard.component';

export const routes: Routes = [
    { path: 'login', component: LoginComponent },
    { path: 'signup', component: SignupComponent },

    { path: '', redirectTo: 'login', pathMatch: 'full' },

    {
        path: 'dashboard',
        component: DashboardComponent,
        children: [
            { path: '', redirectTo: 'home', pathMatch: 'full' },

            {
                path: 'home',
                loadComponent: () =>
                    import('./dashboard/home/home.component')
                        .then(m => m.HomeComponent)
            },
            {
                path: 'documents',
                loadComponent: () =>
                    import('./documents/document-crud/document-crud.component')
                        .then(m => m.DocumentCrudComponent)
            },
            {
                path: 'people',
                loadComponent: () =>
                    import('./people/person-crud/person-crud.component')
                        .then(m => m.PersonCrudComponent)
            },
            {
                path: 'invoice',
                loadComponent: () =>
                    import('./invoice/invoice.component')
                        .then(m => m.InvoiceComponent)
            }
        ]
    },

    { path: '**', redirectTo: 'login' }
];
