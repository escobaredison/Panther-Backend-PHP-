import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { NgIf, NgFor } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { PersonService } from '../../shared/services/person.service';
import { Person } from '../../shared/models/person.model';

@Component({
    selector: 'app-person-crud',
    standalone: true,
    imports: [NgIf, NgFor, FormsModule],
    templateUrl: './person-crud.component.html'
})
export class PersonCrudComponent implements OnInit {

    persons: Person[] = [];
    person: Person = {
        id: undefined,
        name: '',
        lastName: '',
        phone: '',
        document_type_id: undefined,
        country_id: undefined,
        state_id: undefined,
        city_id: undefined
    };

    documents: any[] = [];
    countries: any[] = [];
    departments: any[] = [];
    cities: any[] = [];

    allDepartments: any[] = [];
    allCities: any[] = [];

    documentMap: Record<number, string> = {};
    countryMap: Record<number, string> = {};
    departmentMap: Record<number, string> = {};
    cityMap: Record<number, string> = {};

    constructor(
        private personService: PersonService,
        private cd: ChangeDetectorRef
    ) { }

    ngOnInit(): void {
        this.loadPersons();

        // Documentos
        this.documents = [
            { id: 1, name_long: 'Cédula de Ciudadanía' },
            { id: 2, name_long: 'Pasaporte' },
            { id: 3, name_long: 'Tarjeta de Identidad' },
            { id: 4, name_long: 'Licencia de Conducción' },
            { id: 5, name_long: 'Carné Universitario' },
            { id: 6, name_long: 'Cédula de Extranjería' },
            { id: 7, name_long: 'NIT' },
            { id: 8, name_long: 'Registro Civil' },
            { id: 9, name_long: 'Visa' },
            { id: 10, name_long: 'Permiso Especial' }
        ];
        this.documents.forEach(d => this.documentMap[d.id] = d.name_long);

        // Países
        this.countries = [
            { id: 1, name: 'Colombia' },
            { id: 2, name: 'Ecuador' },
            { id: 3, name: 'Perú' },
            { id: 4, name: 'Chile' },
            { id: 5, name: 'Argentina' },
            { id: 6, name: 'Brasil' },
            { id: 7, name: 'México' },
            { id: 8, name: 'EE.UU.' },
            { id: 9, name: 'España' },
            { id: 10, name: 'Francia' }
        ];
        this.countries.forEach(c => this.countryMap[c.id] = c.name);

        // Departamentos (usa country_id)
        this.allDepartments = [
            { id: 1, name: 'Boyacá', country_id: 1 },
            { id: 2, name: 'Cundinamarca', country_id: 1 },
            { id: 3, name: 'Pichincha', country_id: 2 },
            { id: 4, name: 'Guayas', country_id: 2 },
            { id: 5, name: 'Lima', country_id: 3 },
            { id: 6, name: 'Cusco', country_id: 3 },
            { id: 7, name: 'Santiago', country_id: 4 },
            { id: 8, name: 'Valparaíso', country_id: 4 },
            { id: 9, name: 'Buenos Aires', country_id: 5 },
            { id: 10, name: 'Córdoba', country_id: 5 }
        ];
        this.departments = [...this.allDepartments];
        this.allDepartments.forEach(d => this.departmentMap[d.id] = d.name);

        // Ciudades (usa state_id)
        this.allCities = [
            { id: 1, name: 'Tunja', state_id: 1 },
            { id: 2, name: 'Bogotá', state_id: 2 },
            { id: 3, name: 'Quito', state_id: 3 },
            { id: 4, name: 'Guayaquil', state_id: 4 },
            { id: 5, name: 'Lima', state_id: 5 },
            { id: 6, name: 'Cusco', state_id: 6 },
            { id: 7, name: 'Santiago de Chile', state_id: 7 },
            { id: 8, name: 'Valparaíso', state_id: 8 },
            { id: 9, name: 'Buenos Aires', state_id: 9 },
            { id: 10, name: 'Córdoba', state_id: 10 }
        ];
        this.cities = [...this.allCities];
        this.allCities.forEach(ci => this.cityMap[ci.id] = ci.name);
    }

    loadPersons() {
        this.personService.getPersons().subscribe({
            next: data => {
                this.persons = [...(data || [])];
                this.cd.detectChanges();
            },
            error: err => console.error('Error cargando personas', err)
        });
    }

    savePerson() {
        if (!this.person.name.trim() || !this.person.lastName.trim()) {
            alert('Completa Nombre y Apellido.');
            return;
        }

        if (this.person.id) {
            this.personService.updatePerson(this.person).subscribe({
                next: () => {
                    alert('Persona actualizada');
                    this.loadPersons();
                    this.cancelEdit();
                },
                error: err => console.error('Error actualizando persona', err)
            });
        } else {
            this.personService.createPerson(this.person).subscribe({
                next: () => {
                    alert('Persona creada');
                    this.loadPersons();
                    this.cancelEdit();
                },
                error: err => console.error('Error creando persona', err)
            });
        }
    }

    editPerson(p: Person) {
        this.person = { ...p };
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    deletePerson(id: number) {
        if (!confirm('¿Eliminar esta persona?')) return;
        this.personService.deletePerson(id).subscribe({
            next: () => {
                alert('Persona eliminada');
                this.loadPersons();
            },
            error: err => console.error('Error eliminando persona', err)
        });
    }

    cancelEdit() {
        this.person = {
            id: undefined,
            name: '',
            lastName: '',
            phone: '',
            document_type_id: undefined,
            country_id: undefined,
            state_id: undefined,
            city_id: undefined
        };
    }

    // Selects dependientes
    onCountryChange(countryId: number | undefined) {
        this.departments = countryId
            ? this.allDepartments.filter(d => d.country_id === countryId)
            : [...this.allDepartments];

        this.cities = [...this.allCities]; // siempre habilitadas
    }

    onDepartmentChange(stateId: number | undefined) {
        this.cities = stateId
            ? this.allCities.filter(c => c.state_id === stateId)
            : [...this.allCities];
    }

    // Métodos auxiliares
    getDocumentName(id: number | undefined): string {
        return id ? this.documentMap[id] || '' : '';
    }
    getCountryName(id: number | undefined): string {
        return id ? this.countryMap[id] || '' : '';
    }
    getDepartmentName(id: number | undefined): string {
        return id ? this.departmentMap[id] || '' : '';
    }
    getCityName(id: number | undefined): string {
        return id ? this.cityMap[id] || '' : '';
    }
}
