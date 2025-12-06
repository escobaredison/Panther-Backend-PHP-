import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { NgIf, NgFor } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { DocumentService, DocumentType } from '../../shared/services/document.service';
import { finalize } from 'rxjs/operators';

@Component({
    selector: 'app-document-crud',
    standalone: true,
    imports: [NgIf, NgFor, FormsModule],
    templateUrl: './document-crud.component.html'
})
export class DocumentCrudComponent implements OnInit {
    documents: DocumentType[] = [];
    document: DocumentType = { name_long: '', name_short: '' };
    saving = false;

    constructor(
        private documentService: DocumentService,
        private cd: ChangeDetectorRef   // 👈 inyectamos ChangeDetectorRef
    ) { }

    ngOnInit(): void {
        this.loadDocuments();
    }

    loadDocuments() {
        this.documentService.getDocuments().subscribe({
            next: data => {
                this.documents = [...(data || [])]; // 👈 nueva referencia
                this.cd.detectChanges();            // 👈 fuerza actualización inmediata
            },
            error: err => {
                console.error(err);
                alert('Error cargando documentos');
            }
        });
    }
    saveDocument() {
        if (!this.document.name_long.trim() || !this.document.name_short.trim()) {
            alert('Completa Nombre Largo y Nombre Corto.');
            return;
        }

        const isUpdate = !!this.document.id;
        this.saving = true;

        const obs = isUpdate
            ? this.documentService.updateDocument(this.document)
            : this.documentService.createDocument(this.document);

        obs.pipe(finalize(() => this.saving = false)).subscribe({
            next: () => {
                this.loadDocuments();   // 👈 recarga la tabla
                this.document = { name_long: '', name_short: '' }; // reset
                alert(isUpdate ? 'Documento actualizado' : 'Documento creado');
            },
            error: err => {
                console.error(err);
                alert('Error al guardar documento');
            }
        });
    }

    editDocument(doc: DocumentType) {
        this.document = { ...doc };
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    deleteDocument(id?: number) {
        if (id === undefined) return;
        if (!confirm('¿Eliminar este tipo de documento?')) return;

        this.saving = true;
        this.documentService.deleteDocument(id)
            .pipe(finalize(() => this.saving = false))
            .subscribe({
                next: () => {
                    this.loadDocuments();
                    alert('Documento eliminado');
                },
                error: err => {
                    console.error(err);
                    alert('Error al eliminar');
                }
            });
    }

    cancelEdit() {
        this.document = { name_long: '', name_short: '' };
    }
}
